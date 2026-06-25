<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\RideSharingCity;
use App\Models\RideSharingRoute;
use App\Models\RideSharingSchedule;
use App\Models\Setting;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class RideSharingController extends Controller
{
    /**
     * Show the Ride Sharing booking page, and fetch available routes if from/to are specified.
     */
    public function index(Request $request): Response
    {
        $locations = RideSharingCity::orderBy('name')->get(['id', 'name', 'address']);

        $pickup_id = $request->query('pickup_location_id');
        $dropoff_id = $request->query('dropoff_location_id');

        $availableRoutes = [];

        if ($pickup_id && $dropoff_id && $pickup_id != $dropoff_id) {
            $availableRoutes = RideSharingRoute::where('is_active', true)
                ->whereHas('paths', function ($q) use ($pickup_id) {
                    $q->where('city_id', $pickup_id);
                })
                ->whereHas('paths', function ($q) use ($dropoff_id) {
                    $q->where('city_id', $dropoff_id);
                })
                ->with(['paths', 'schedules' => function ($q) use ($request, $pickup_id, $dropoff_id) {
                    $q->where('is_active', true)->with(['vehicleCategory', 'prices' => function ($p) use ($pickup_id, $dropoff_id) {
                        $p->where('from_city_id', $pickup_id)
                            ->where('to_city_id', $dropoff_id)
                            ->where('is_active', true);
                    }]);
                    if ($date = $request->query('date')) {
                        $dayOfWeek = Carbon::parse($date)->englishDayOfWeek; // e.g. "Sunday"
                        $q->whereJsonContains('days', $dayOfWeek);
                    }
                }])
                ->get()
                ->filter(function ($route) use ($pickup_id, $dropoff_id) {
                    $pickupSeq = $route->paths->firstWhere('city_id', $pickup_id)->sequence ?? 9999;
                    $dropoffSeq = $route->paths->firstWhere('city_id', $dropoff_id)->sequence ?? 0;

                    // Keep route if it's correct direction and at least one schedule has a valid price
                    return $pickupSeq < $dropoffSeq && $route->schedules->filter(function ($s) {
                        return $s->prices->isNotEmpty();
                    })->isNotEmpty();
                })
                ->map(function ($route) use ($pickup_id, $dropoff_id) {

                    // Sort schedules in PHP since it's a JSON column mapping
                    $sortedSchedules = $route->schedules->filter(function ($schedule) {
                        // Only return schedules that have a price for this specific city pair
                        return $schedule->prices->isNotEmpty();
                    })->sortBy(function ($schedule) use ($pickup_id) {
                        return $schedule->departure_times[$pickup_id] ?? '23:59';
                    })->values()->map(function ($schedule) use ($pickup_id, $dropoff_id) {
                        $pickupTime = $schedule->departure_times[$pickup_id] ?? null;
                        $dropoffTime = $schedule->departure_times[$dropoff_id] ?? null;

                        $estimatedMinutes = null;
                        if ($pickupTime && $dropoffTime) {
                            $pickupCarbon = Carbon::parse($pickupTime);
                            $dropoffCarbon = Carbon::parse($dropoffTime);
                            if ($dropoffCarbon->lessThan($pickupCarbon)) {
                                $dropoffCarbon->addDay();
                            }
                            $estimatedMinutes = $pickupCarbon->diffInMinutes($dropoffCarbon);
                        }

                        // Get the exact price object for this schedule
                        $priceObj = $schedule->prices->first();

                        // Include specific departure time for this pickup point
                        $schedule->specific_departure_time = $pickupTime;
                        $schedule->specific_arrival_time = $dropoffTime;
                        $schedule->estimated_minutes = $estimatedMinutes;
                        $schedule->price = $priceObj->price; // Set price directly on schedule

                        return $schedule;
                    });

                    return [
                        'id' => $route->id,
                        'name' => $route->name,
                        'schedules' => $sortedSchedules,
                    ];
                })
                ->values();
        }

        return Inertia::render('RideSharing/Index', [
            'locations' => $locations,
            'availableRoutes' => $availableRoutes,
            'search' => [
                'date' => $request->query('date', ''),
                'pickup_location_id' => $pickup_id,
                'dropoff_location_id' => $dropoff_id,
                'passengers' => (int) $request->query('passengers', 1),
            ],
        ]);
    }

    /**
     * Show the Ride Sharing checkout page.
     */
    public function checkout(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'rs_route_id' => 'required|exists:rs_routes,id',
            'rs_schedule_id' => 'required|exists:rs_schedules,id',
            'pickup_location_id' => 'required|exists:rs_cities,id',
            'dropoff_location_id' => 'required|exists:rs_cities,id',
            'date' => 'required|date',
            'passengers' => 'required|integer|min:1',
        ]);

        $route = RideSharingRoute::findOrFail($validated['rs_route_id']);
        $schedule = RideSharingSchedule::with(['prices', 'vehicleCategory'])->findOrFail($validated['rs_schedule_id']);
        $pickup = RideSharingCity::findOrFail($validated['pickup_location_id']);
        $dropoff = RideSharingCity::findOrFail($validated['dropoff_location_id']);

        $priceObj = $schedule->prices->where('from_city_id', $pickup->id)->where('to_city_id', $dropoff->id)->first();
        if (! $priceObj) {
            return redirect()->back()->with('error', 'Price not found for selected route.');
        }

        $basePrice = $priceObj->price;
        $totalPrice = $basePrice * $validated['passengers'];

        $checkoutData = [
            'route' => $route,
            'schedule' => $schedule,
            'pickup' => $pickup,
            'dropoff' => $dropoff,
            'date' => $validated['date'],
            'passengers' => $validated['passengers'],
            'basePrice' => $basePrice,
            'totalPrice' => $totalPrice,
            'departure_time' => $schedule->departure_times[$pickup->id] ?? null,
            'arrival_time' => $schedule->departure_times[$dropoff->id] ?? null,
        ];

        return Inertia::render('RideSharing/Checkout', [
            'checkoutData' => $checkoutData,
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    /**
     * Store the Ride Sharing order and redirect to payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rs_route_id' => 'required|exists:rs_routes,id',
            'rs_schedule_id' => 'required|exists:rs_schedules,id',
            'pickup_location_id' => 'required|exists:rs_cities,id',
            'dropoff_location_id' => 'required|exists:rs_cities,id',
            'date' => 'required|date',
            'passengers' => 'required|integer|min:1',

            // Customer Details
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'create_account' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed|required_if:create_account,true',

            'pickup_detail' => 'nullable|string',
            'dropoff_detail' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $route = RideSharingRoute::findOrFail($validated['rs_route_id']);
        $schedule = RideSharingSchedule::with('prices')->findOrFail($validated['rs_schedule_id']);
        $pickup = RideSharingCity::findOrFail($validated['pickup_location_id']);
        $dropoff = RideSharingCity::findOrFail($validated['dropoff_location_id']);

        $priceObj = $schedule->prices->where('from_city_id', $pickup->id)->where('to_city_id', $dropoff->id)->first();
        if (! $priceObj) {
            throw ValidationException::withMessages(['rs_schedule_id' => 'Price configuration is missing for this schedule.']);
        }

        $basePrice = $priceObj->price;
        $totalPrice = $basePrice * $validated['passengers'];

        $customer = Auth::guard('customer')->user();
        if (! $customer) {
            $customerData = [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
            ];

            if ($request->boolean('create_account') && ! empty($validated['password'])) {
                $customerData['password'] = Hash::make($validated['password']);
            } else {
                $customerData['password'] = Hash::make(Str::random(12));
            }

            if (Customer::where('email', $validated['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email is already registered. Please log in first.',
                ]);
            }
            $customerData['email'] = $validated['email'];
            $customer = Customer::create($customerData);
        }

        do {
            $bookingCode = 'SR'.strtoupper(Str::random(6));
        } while (Order::where('booking_code', $bookingCode)->exists());

        $orderNumber = 'ORD'.date('Ymd').strtoupper(Str::random(4));

        $pickupAddress = $pickup->name.' - '.($validated['pickup_detail'] ?? $pickup->address);
        $dropoffAddress = $dropoff->name.' - '.($validated['dropoff_detail'] ?? $dropoff->address);
        $time = $schedule->departure_times[$pickup->id] ?? '00:00';

        $order = Order::create([
            'booking_code' => $bookingCode,
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['email'],
            'date' => $validated['date'],
            'time' => $time,
            'pickup_address' => $pickupAddress,
            'pickup_latitude' => null,
            'pickup_longitude' => null,
            'dropoff_address' => $dropoffAddress,
            'dropoff_latitude' => null,
            'dropoff_longitude' => null,
            'passengers' => $validated['passengers'],
            'notes' => $validated['notes'] ?? null,
            'vehicle_category_id' => $schedule->vehicle_category_id,
            'price' => $totalPrice,
            'status' => 'pending',
            'is_shared' => false,
            'trip_type' => 'sharing_ride',
            'is_return_trip' => false,
        ]);

        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        try {
            $redirectUrl = $this->generateXenditPayment($order);

            if (str_starts_with($redirectUrl, 'http') && ! str_starts_with($redirectUrl, url('/'))) {
                return inertia()->location($redirectUrl);
            }

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            return redirect()->route('booking.show', $order->booking_code)
                ->with('error', 'Failed to create payment invoice: '.$e->getMessage());
        }
    }

    private function generateXenditPayment(Order $order): string
    {
        $xenditKey = Setting::getValue('xendit_secret_key') ?: config('services.xendit.secret_key');
        Configuration::setXenditKey($xenditKey);

        $expiry = now()->addHours(24);

        $guzzleClient = new Client([
            'verify' => app()->environment('local') ? false : true,
        ]);

        $apiInstance = new InvoiceApi($guzzleClient);
        $invoiceAmount = (float) $order->price;

        $successUrl = route('booking.show', $order->booking_code).'?payment=success';
        $failureUrl = route('booking.show', $order->booking_code).'?payment=failed';

        $req = new CreateInvoiceRequest([
            'external_id' => $order->booking_code.'_'.time(),
            'amount' => $invoiceAmount,
            'payer_email' => $order->customer_email,
            'description' => 'Payment for Ride Sharing Booking '.$order->booking_code,
            'success_redirect_url' => $successUrl,
            'failure_redirect_url' => $failureUrl,
        ]);

        $result = $apiInstance->createInvoice($req);
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
}
