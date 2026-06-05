<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\VehicleCategory;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use App\Services\OrderCancellationService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

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
            'allVehicleCategories' => VehicleCategory::orderBy('price_per_km')->get(),
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
                'payment_method' => $order->payment_method,
                'payment_reference' => $order->payment_reference,
                'payment_status' => $order->payment_status,
                'payment_expiry' => $order->payment_expiry ? Carbon::parse($order->payment_expiry)->format('Y-m-d H:i:s') : null,
                'vehicle_category' => $order->vehicleCategory ? [
                    'title' => $order->vehicleCategory->title,
                    'image_url' => $order->vehicleCategory->image_url,
                    'base_price' => $order->vehicleCategory->base_price,
                ] : null,
            ] : null,
        ]);
    }

    /**
     * Estimate the booking price for a given route (pickup/dropoff lat-lng).
     *
     * Returns the computed price per vehicle category so the booking page
     * can display live prices before the customer submits the form.
     */
    public function estimatePrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pickup_latitude' => ['required', 'numeric'],
            'pickup_longitude' => ['required', 'numeric'],
            'dropoff_latitude' => ['required', 'numeric'],
            'dropoff_longitude' => ['required', 'numeric'],
            'exact_distance_km' => ['nullable', 'numeric'],
        ]);

        $pickupZone = Zone::findContainingPoint((float) $validated['pickup_latitude'], (float) $validated['pickup_longitude']);
        $dropoffZone = Zone::findContainingPoint((float) $validated['dropoff_latitude'], (float) $validated['dropoff_longitude']);

        if (! $pickupZone) {
            return response()->json(['error' => 'The pickup address is outside our service area.'], 422);
        }
        if (! $dropoffZone) {
            return response()->json(['error' => 'The dropoff address is outside our service area.'], 422);
        }

        $pricingRule = ZonePricingRule::active()
            ->where('pickup_zone_id', $pickupZone->id)
            ->where('dropoff_zone_id', $dropoffZone->id)
            ->first();

        if (! $pricingRule) {
            return response()->json(['error' => "The route from {$pickupZone->name} to {$dropoffZone->name} is currently not available."], 422);
        }

        $categories = VehicleCategory::all();

        $exactDistance = isset($validated['exact_distance_km']) ? (float) $validated['exact_distance_km'] : null;

        $prices = $categories->map(function (VehicleCategory $category) use ($pricingRule, $exactDistance): array {
            $estPrice = (int) round($pricingRule->calculate($category, $exactDistance));

            return [
                'id' => $category->id,
                'estimated_price' => $estPrice,
                'price_per_km' => (float) $category->price_per_km,
            ];
        });

        return response()->json([
            'pickup_zone' => $pickupZone?->name,
            'dropoff_zone' => $dropoffZone?->name,
            'distance_km' => $exactDistance ?? $pricingRule?->distance_km,
            'prices' => $prices,
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
                'message' => 'This email is already registered. Please log in to continue booking.',
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
     * Track a specific booking by its exact booking code.
     */
    public function trackBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $order = Order::with(['driver'])
            ->where('booking_code', strtoupper($validated['code']))
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json([
            'order' => [
                'booking_code' => $order->booking_code,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'pickup_address' => $order->pickup_address,
                'dropoff_address' => $order->dropoff_address,
                'date' => $order->date->format('Y-m-d'),
                'time' => $order->time,
                'driver_name' => $order->driver?->name,
            ],
        ]);
    }

    /**
     * Store a new customer order (from checkout page).
     */
    public function store(StoreCustomerOrderRequest $request): SymfonyResponse
    {
        $validated = $request->validated();

        // Find existing customer by email
        $customer = Customer::where('email', $validated['email'])->first();

        // If trying to create account but email already registered with password
        if ($request->boolean('create_account') && $customer && $customer->password) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered. Please log in to continue booking.',
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

        // ── Calculate total price ─────────────────────────────────────────────
        //
        // Primary path: look up the zone pair from the order's lat/lng, then
        // use ZonePricingRule::calculate($vehicle) which applies the formula:
        //   price = max(minimum_price, base_price + distance_km × vehicle.price_per_km)
        //
        // Fallback: if no zone rule exists for this route, use the vehicle's
        // base_price directly so the booking still goes through.
        $basePrice = 0.0;

        if ($vehicleCategory) {
            $pickupLat = isset($validated['pickup_latitude']) ? (float) $validated['pickup_latitude'] : null;
            $pickupLng = isset($validated['pickup_longitude']) ? (float) $validated['pickup_longitude'] : null;
            $dropoffLat = isset($validated['dropoff_latitude']) ? (float) $validated['dropoff_latitude'] : null;
            $dropoffLng = isset($validated['dropoff_longitude']) ? (float) $validated['dropoff_longitude'] : null;

            if ($pickupLat && $pickupLng && $dropoffLat && $dropoffLng) {
                $pickupZone = Zone::findContainingPoint($pickupLat, $pickupLng);
                $dropoffZone = Zone::findContainingPoint($dropoffLat, $dropoffLng);

                if (! $pickupZone) {
                    throw ValidationException::withMessages(['pickup_address' => 'The pickup address is outside our service area.']);
                }
                if (! $dropoffZone) {
                    throw ValidationException::withMessages(['dropoff_address' => 'The dropoff address is outside our service area.']);
                }

                $pricingRule = ZonePricingRule::active()
                    ->where('pickup_zone_id', $pickupZone->id)
                    ->where('dropoff_zone_id', $dropoffZone->id)
                    ->first();

                if (! $pricingRule) {
                    throw ValidationException::withMessages(['pickup_address' => "The route from {$pickupZone->name} to {$dropoffZone->name} is not available."]);
                }

                $exactDistance = isset($validated['exact_distance_km']) ? (float) $validated['exact_distance_km'] : null;
                $basePrice = $pricingRule->calculate($vehicleCategory, $exactDistance);
            }
        }

        $extrasTotal = 0.0;
        $extras = $validated['extras'] ?? [];
        foreach ($extras as $extra) {
            $extrasTotal += (float) ($extra['price'] ?? 0);
        }
        $totalPrice = $basePrice + $extrasTotal;

        $order = Order::create([
            'booking_code' => $bookingCode,
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['email'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'pickup_address' => $validated['pickup_address'],
            'pickup_latitude' => $validated['pickup_latitude'] ?? null,
            'pickup_longitude' => $validated['pickup_longitude'] ?? null,
            'dropoff_address' => $validated['dropoff_address'],
            'dropoff_latitude' => $validated['dropoff_latitude'] ?? null,
            'dropoff_longitude' => $validated['dropoff_longitude'] ?? null,
            'distance_km' => $validated['exact_distance_km'] ?? null,
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

        try {
            $redirectUrl = $this->generateXenditPayment($order);

            // If it returns an Inertia redirect (Inertia::location) for external URLs
            if (str_starts_with($redirectUrl, 'http') && ! str_starts_with($redirectUrl, url('/'))) {
                return inertia()->location($redirectUrl);
            }

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            // If payment generation fails, redirect back to checkout with error
            return redirect()->back()->withErrors(['payment_method' => 'Failed to create payment invoice: '.$e->getMessage()]);
        }
    }

    /**
     * Generate Xendit Payment and return redirect URL
     */
    private function generateXenditPayment(Order $order): string
    {
        Configuration::setXenditKey(
            \App\Models\Setting::getValue('xendit_secret_key') ?? config('services.xendit.secret_key')
        );

        $paymentReference = null;
        $expiry = now()->addHours(24);

        // Khusus untuk local development di Windows/XAMPP yang sering bermasalah dengan SSL
        $guzzleClient = new Client([
            'verify' => app()->environment('local') ? false : true,
        ]);

        $apiInstance = new InvoiceApi($guzzleClient);

        $req = new CreateInvoiceRequest([
            'external_id' => $order->booking_code.'_'.time(),
            'amount' => (float) $order->price,
            'payer_email' => $order->customer_email,
            'description' => 'Payment for Booking '.$order->booking_code,
        ]);

        $result = $apiInstance->createInvoice($req);

        // Xendit Invoice URL
        $paymentReference = $result->getInvoiceUrl();

        $order->update([
            'payment_method' => 'Xendit Invoice',
            'payment_reference' => $paymentReference,
            'payment_status' => 'pending',
            'payment_expiry' => $expiry,
        ]);

        if (str_starts_with($paymentReference, 'http')) {
            return $paymentReference;
        }

        return route('booking.payment-success', ['code' => $order->booking_code]);
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
     * Cancel an order (customer initiated).
     */
    public function cancelOrder(Request $request, $bookingCode): JsonResponse
    {
        $order = Order::where('booking_code', $bookingCode)->firstOrFail();

        // Verify the user is the order owner (if authenticated) or it's a guest order
        if ($request->user('customer') && $order->customer_id !== $request->user('customer')->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cancellationService = new OrderCancellationService;

        // Check if order can be cancelled
        if (! $cancellationService->canBeCancelled($order)) {
            return response()->json([
                'message' => 'This order cannot be cancelled. It may have already been paid or cancelled.',
            ], 422);
        }

        // Cancel the order
        if ($cancellationService->manuallyCancel($order)) {
            return response()->json([
                'message' => 'Order has been cancelled successfully.',
                'order' => [
                    'booking_code' => $order->booking_code,
                    'status' => 'cancelled',
                    'payment_status' => $order->payment_status,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Failed to cancel order.',
        ], 500);
    }

    /**
     * Display customer booking details.
     */
    public function show($bookingCode): Response
    {
        $order = Order::with(['customer', 'driver', 'vehicleCategory'])->where('booking_code', $bookingCode)->firstOrFail();

        // Automatically cancel order if it's eligible
        $cancellationService = new OrderCancellationService;
        $cancellationService->autoCancelIfEligible($order);

        // Refresh order from database to get updated status
        $order = $order->fresh();

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
