<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ActivityBookingCancelledMail;
use App\Mail\ActivityBookingCompletedMail;
use App\Mail\ActivityBookingConfirmedMail;
use App\Models\ActivityBooking;
use App\Support\DriverReferralAttribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $oldStatus = $activityBooking->status;
        $activityBooking->update($validated);

        if ($validated['status'] === ActivityBooking::STATUS_CANCELLED) {
            DriverReferralAttribution::void($activityBooking);
        }

        // Send email notification based on status change
        $this->sendStatusEmail($activityBooking, $validated['status']);

        return back()->with('success', 'Booking status updated.');
    }

    private function sendStatusEmail(ActivityBooking $booking, string $newStatus): void
    {
        if (! $booking->customer_email) {
            return;
        }

        try {
            $booking->load('activity');

            $mail = match ($newStatus) {
                ActivityBooking::STATUS_CONFIRMED => new ActivityBookingConfirmedMail($booking),
                ActivityBooking::STATUS_CANCELLED => new ActivityBookingCancelledMail($booking),
                ActivityBooking::STATUS_COMPLETED => new ActivityBookingCompletedMail($booking),
                default => null,
            };

            if ($mail) {
                Mail::to($booking->customer_email)->send($mail);
                Log::info("Activity booking {$newStatus} email sent to {$booking->customer_email} for booking {$booking->booking_code}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send activity booking {$newStatus} email for booking {$booking->booking_code}: {$e->getMessage()}");
        }
    }
}
