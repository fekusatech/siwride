<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
     * Store a new customer order.
     */
    public function store(StoreCustomerOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Generate unique booking code (SW + 6 digits)
        $bookingCode = $this->generateUniqueBookingCode();

        // Generate order number (ORD + timestamp + random)
        $orderNumber = 'ORD' . date('Ymd') . strtoupper(Str::random(4));

        // Create order
        $order = Order::create([
            'booking_code' => $bookingCode,
            'order_number' => $orderNumber,
            'date' => $validated['date'],
            'time' => $validated['time'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'email' => $validated['email'],
            'pickup_address' => $validated['pickup_address'],
            'dropoff_address' => $validated['dropoff_address'],
            'passengers' => $validated['passengers'],
            'notes' => $validated['notes'] ?? null,
            'price' => 0, // Price will be set by admin later
            'parking_gas_fee' => 0,
            'status' => 'pending',
        ]);

        return redirect()->route('booking.success', ['code' => $bookingCode, 'vehicle' => $validated['vehicle_type']])
            ->with('success', 'Order berhasil dibuat! Booking code Anda: ' . $bookingCode);
    }

    /**
     * Display success page after booking.
     */
    public function success(Request $request): Response
    {
        $bookingCode = $request->query('code', '');

        $order = Order::where('booking_code', $bookingCode)->first();

        return Inertia::render('customer/booking-success', [
            'booking_code' => $bookingCode,
            'order' => $order ? [
                'customer_name' => $order->customer_name,
                'email' => $order->email,
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
     * Generate unique booking code.
     */
    private function generateUniqueBookingCode(): string
    {
        do {
            $code = 'SW' . strtoupper(Str::random(6));
        } while (Order::where('booking_code', $code)->exists());

        return $code;
    }
}
