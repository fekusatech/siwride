<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverServiceBooking;
use App\Support\DriverReferralAttribution;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverServiceBookingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $bookings = DriverServiceBooking::with('driverService.driver')
            ->when($search, function ($query, $search) {
                $query->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            })
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/DriverServiceBookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(DriverServiceBooking $driverServiceBooking)
    {
        $driverServiceBooking->load('driverService.driver', 'customer');

        return Inertia::render('Admin/DriverServiceBookings/Show', [
            'booking' => $driverServiceBooking,
        ]);
    }

    public function updateStatus(Request $request, DriverServiceBooking $driverServiceBooking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $driverServiceBooking->update($validated);

        if ($validated['status'] === DriverServiceBooking::STATUS_CANCELLED) {
            DriverReferralAttribution::void($driverServiceBooking);
        }

        return back()->with('success', 'Booking status updated.');
    }
}
