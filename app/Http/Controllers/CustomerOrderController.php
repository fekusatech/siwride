<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    /**
     * Display booking page with optional pre-filled data.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('customer/booking', [
            'prefill' => [
                'pickup' => $request->input('pickup', ''),
                'dropoff' => $request->input('dropoff', ''),
                'date' => $request->input('date', ''),
                'passengers' => $request->input('passengers', '1'),
                'vehicle' => $request->input('vehicle', ''),
            ],
        ]);
    }

    /**
     * Validate email availability for booking step 1.
     */
    public function validateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'create_account' => ['nullable', 'boolean'],
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        // If trying to create account but email already registered with password
        if ($request->boolean('create_account') && $customer && $customer->password) {
            return response()->json([
                'valid' => false,
                'message' => 'Email ini sudah terdaftar. Silakan login terlebih dahulu untuk melanjutkan booking.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'exists' => (bool) $customer,
            'has_password' => $customer ? (bool) $customer->password : false,
        ]);
    }

    /**
     * Search bookings by booking code for guest tracking.
     */
    public function searchBookings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $query = $validated['query'];

        $orders = Order::with(['customer', 'driver'])
            ->where('booking_code', 'like', "%{$query}%")
            ->orWhere('order_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return response()->json([
            'orders' => $orders->map(fn ($order) => [
                'booking_code' => $order->booking_code,
                'order_number' => $order->order_number,
                'pickup_address' => $order->pickup_address,
                'dropoff_address' => $order->dropoff_address,
                'date' => $order->date->format('Y-m-d'),
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'driver_name' => $order->driver?->name,
            ]),
        ]);
    }

    /**
     * Store a new customer order.
     */
    public function store(StoreCustomerOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Find existing customer by email
        $customer = Customer::where('email', $validated['email'])->first();

        // If trying to create account but email already registered with password
        if ($request->boolean('create_account') && $customer && $customer->password) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah terdaftar. Silakan login terlebih dahulu untuk melanjutkan booking.',
            ]);
        }

        $customerData = [
            'name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'] ?? null,
        ];

        // Hash password if user wants to create an account
        if ($request->boolean('create_account') && isset($validated['password'])) {
            $customerData['password'] = Hash::make($validated['password']);
        }

        if ($customer) {
            // Update customer data to latest from booking form
            $updateData = [
                'name' => $customerData['name'],
            ];

            if (isset($customerData['phone'])) {
                $updateData['phone'] = $customerData['phone'];
            }

            // Only set password if not exists and user wants to create account
            if (! $customer->password && isset($customerData['password'])) {
                $updateData['password'] = $customerData['password'];
            }

            $customer->update($updateData);
        } else {
            // Create new customer
            $customerData['email'] = $validated['email'];
            $customer = Customer::create($customerData);
        }

        // Generate unique booking code (SW + 6 chars)
        $bookingCode = $this->generateUniqueBookingCode();

        // Generate order number (ORD + timestamp + random)
        $orderNumber = 'ORD'.date('Ymd').strtoupper(Str::random(4));

        // Create order linked to customer
        Order::create([
            'booking_code' => $bookingCode,
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['email'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'pickup_address' => $validated['pickup_address'],
            'dropoff_address' => $validated['dropoff_address'],
            'passengers' => $validated['passengers'],
            'notes' => $validated['notes'] ?? null,
            'price' => 0, // Price will be set by admin later
            'parking_gas_fee' => 0,
            'status' => 'pending',
            'is_shared' => true,
        ]);

        // Auto-login customer if they just created an account
        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        return redirect()->route('booking.success', ['code' => $bookingCode, 'vehicle' => $validated['vehicle_type']])
            ->with('success', 'Order berhasil dibuat! Booking code Anda: '.$bookingCode);
    }

    /**
     * Display success page after booking.
     */
    public function success(Request $request): Response
    {
        $bookingCode = $request->query('code', '');

        $order = Order::with('customer')->where('booking_code', $bookingCode)->first();

        return Inertia::render('customer/booking-success', [
            'booking_code' => $bookingCode,
            'order' => $order ? [
                'customer_name' => $order->customer_name,
                'email' => $order->customer_email,
                'pickup_address' => $order->pickup_address,
                'dropoff_address' => $order->dropoff_address,
                'date' => $order->date->format('Y-m-d'),
                'time' => substr($order->time, 0, 5),
                'vehicle_type' => $request->query('vehicle', ''),
                'passengers' => $order->passengers,
            ] : null,
        ]);
    }

    /**
     * Display customer booking details.
     */
    public function show($bookingCode): Response
    {
        $order = Order::with(['customer', 'driver'])->where('booking_code', $bookingCode)->firstOrFail();

        return Inertia::render('customer/booking-detail', [
            'order' => $order,
        ]);
    }

    /**
     * Generate unique booking code.
     */
    private function generateUniqueBookingCode(): string
    {
        do {
            $code = 'SW'.strtoupper(Str::random(6));
        } while (Order::where('booking_code', $code)->exists());

        return $code;
    }
}
