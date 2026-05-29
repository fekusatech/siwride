<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\VehicleCategory;
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
     * Display booking page — shows route form + route info + vehicle categories.
     */
    public function index(Request $request): Response
    {
        $passengers = (int) $request->input('passengers', 1);

        // Filter vehicle categories by passenger capacity
        $vehicleCategories = VehicleCategory::query()
            ->when($passengers > 1, function ($query) use ($passengers) {
                $query->where(function ($q) use ($passengers) {
                    $q->whereNull('passenger_capacity')
                        ->orWhere('passenger_capacity', '>=', $passengers);
                });
            })
            ->orderBy('base_price')
            ->get();

        return Inertia::render('customer/booking', [
            'prefill' => [
                'pickup' => $request->input('pickup', ''),
                'dropoff' => $request->input('dropoff', ''),
                'date' => $request->input('date', ''),
                'time' => $request->input('time', ''),
                'passengers' => $request->input('passengers', '1'),
            ],
            'vehicleCategories' => $vehicleCategories,
            'allVehicleCategories' => VehicleCategory::orderBy('base_price')->get(),
        ]);
    }

    /**
     * Display checkout page — multi-step form after vehicle selection.
     */
    public function checkout(Request $request): Response
    {
        $vehicleCategoryId = $request->input('vehicle_category_id');
        $vehicleCategory = null;

        if ($vehicleCategoryId) {
            $vehicleCategory = VehicleCategory::find($vehicleCategoryId);
        }

        return Inertia::render('customer/checkout', [
            'transfer' => [
                'pickup' => $request->input('pickup', ''),
                'dropoff' => $request->input('dropoff', ''),
                'date' => $request->input('date', ''),
                'time' => $request->input('time', ''),
                'passengers' => $request->input('passengers', '1'),
            ],
            'vehicleCategory' => $vehicleCategory,
        ]);
    }

    /**
     * Display payment page — dummy payment UI.
     */
    public function payment(Request $request): Response
    {
        $bookingCode = $request->input('code', '');
        $order = null;

        if ($bookingCode) {
            $order = Order::with(['vehicleCategory'])->where('booking_code', $bookingCode)->first();
        }

        return Inertia::render('customer/payment', [
            'booking_code' => $bookingCode,
            'order' => $order ? [
                'booking_code' => $order->booking_code,
                'customer_name' => $order->customer_name,
                'pickup_address' => $order->pickup_address,
                'dropoff_address' => $order->dropoff_address,
                'date' => $order->date->format('Y-m-d'),
                'time' => substr($order->time, 0, 5),
                'passengers' => $order->passengers,
                'extras' => $order->extras ?? [],
                'price' => $order->price,
                'vehicle_category' => $order->vehicleCategory ? [
                    'title' => $order->vehicleCategory->title,
                    'image_url' => $order->vehicleCategory->image_url,
                    'base_price' => $order->vehicleCategory->base_price,
                ] : null,
            ] : null,
        ]);
    }

    /**
     * Display payment success page.
     */
    public function paymentSuccess(Request $request): Response
    {
        $bookingCode = $request->input('code', '');
        $order = Order::with(['vehicleCategory'])->where('booking_code', $bookingCode)->first();

        return Inertia::render('customer/payment-success', [
            'booking_code' => $bookingCode,
            'order' => $order ? [
                'booking_code' => $order->booking_code,
                'customer_name' => $order->customer_name,
                'email' => $order->customer_email,
                'pickup_address' => $order->pickup_address,
                'dropoff_address' => $order->dropoff_address,
                'date' => $order->date->format('Y-m-d'),
                'time' => substr($order->time, 0, 5),
                'passengers' => $order->passengers,
                'extras' => $order->extras ?? [],
                'price' => $order->price,
                'vehicle_category' => $order->vehicleCategory ? [
                    'title' => $order->vehicleCategory->title,
                    'image_url' => $order->vehicleCategory->image_url,
                    'base_price' => $order->vehicleCategory->base_price,
                ] : null,
            ] : null,
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
     * Store a new customer order (from checkout page).
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
            $updateData = ['name' => $customerData['name']];

            if (isset($customerData['phone'])) {
                $updateData['phone'] = $customerData['phone'];
            }

            if (! $customer->password && isset($customerData['password'])) {
                $updateData['password'] = $customerData['password'];
            }

            $customer->update($updateData);
        } else {
            $customerData['email'] = $validated['email'];
            $customer = Customer::create($customerData);
        }

        // Generate unique booking code (SW + 6 chars)
        $bookingCode = $this->generateUniqueBookingCode();

        // Generate order number (ORD + timestamp + random)
        $orderNumber = 'ORD'.date('Ymd').strtoupper(Str::random(4));

        // Resolve vehicle category
        $vehicleCategory = null;
        if (! empty($validated['vehicle_category_id'])) {
            $vehicleCategory = VehicleCategory::find($validated['vehicle_category_id']);
        }

        // Calculate total price (base price + extras)
        $basePrice = $vehicleCategory ? (float) $vehicleCategory->base_price : 0;
        $extrasTotal = 0;
        $extras = $validated['extras'] ?? [];
        foreach ($extras as $extra) {
            $extrasTotal += (float) ($extra['price'] ?? 0);
        }
        $totalPrice = $basePrice + $extrasTotal;

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
            'extras' => $extras ?: null,
            'vehicle_category_id' => $vehicleCategory?->id,
            'price' => $totalPrice,
            'parking_gas_fee' => 0,
            'status' => 'pending',
            'is_shared' => true,
        ]);

        // Auto-login customer if they just created an account
        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        return redirect()->route('booking.payment', ['code' => $bookingCode]);
    }

    /**
     * Process dummy payment and redirect to success.
     */
    public function processPayment(Request $request): RedirectResponse
    {
        $bookingCode = $request->input('booking_code', '');

        // Dummy payment processing — just redirect to success
        return redirect()->route('booking.payment-success', ['code' => $bookingCode]);
    }

    /**
     * Display success page after booking (legacy route kept for compatibility).
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
