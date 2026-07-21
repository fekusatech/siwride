<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityBookingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $bookings = ActivityBooking::with('activity')
            ->when($search, function ($query, $search) {
                $query->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            })
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/ActivityBookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(ActivityBooking $activityBooking)
    {
        $activityBooking->load('activity', 'customer');

        return Inertia::render('Admin/ActivityBookings/Show', [
            'booking' => $activityBooking,
        ]);
    }

    public function updateStatus(Request $request, ActivityBooking $activityBooking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $activityBooking->update($validated);

        return back()->with('success', 'Booking status updated.');
    }
}
