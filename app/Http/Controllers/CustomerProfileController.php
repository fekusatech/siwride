<?php

namespace App\Http\Controllers;

use App\Models\ActivityBooking;
use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerProfileController extends Controller
{
    /**
     * Show the customer profile page with order history.
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $orders = Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $cancellationService = new OrderCancellationService;
        $orders->each(fn ($order) => $cancellationService->autoCancelIfEligible($order));

        // Fetch activity bookings
        $activityBookings = ActivityBooking::where('customer_id', $customer->id)
            ->with('activity')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'date' => $booking->booking_date->format('Y-m-d'),
                    'time' => null,
                    'pickup_address' => $booking->activity->meeting_point ?? 'Activity Location',
                    'dropoff_address' => null,
                    'passengers' => $booking->pax,
                    'price' => $booking->total_price,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'created_at' => $booking->created_at,
                    'is_activity' => true,
                    'activity_title' => $booking->activity->title ?? 'Activity',
                ];
            });

        // Merge orders and activity bookings, sort by created_at
        $allBookings = $orders->concat($activityBookings)
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('customer/profile', [
            'orders' => $allBookings,
        ]);
    }

    /**
     * Update customer profile details.
     */
    public function update(Request $request)
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $customer->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
