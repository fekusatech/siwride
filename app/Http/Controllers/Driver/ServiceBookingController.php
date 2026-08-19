<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Mail\DriverServiceBookingCompletedMail;
use App\Mail\DriverServiceBookingCustomerCompletedMail;
use App\Models\DriverServiceBooking;
use App\Services\ServiceBookingSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ServiceBookingController extends Controller
{
    public function accept(string $bookingCode): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $booking = DriverServiceBooking::query()
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        abort_unless($booking->assigned_driver_id === $driver->getKey(), 403);

        DB::transaction(function () use ($booking): void {
            $lockedBooking = DriverServiceBooking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedBooking->assigned_driver_id === Auth::guard('driver')->id(), 403);

            if ($lockedBooking->assignment_status === 'accepted') {
                return;
            }

            abort_unless(
                $lockedBooking->payment_status === DriverServiceBooking::PAYMENT_PAID
                    && $lockedBooking->status === DriverServiceBooking::STATUS_CONFIRMED
                    && $lockedBooking->assignment_status === 'assigned',
                422,
                'This booking is not ready to be accepted.',
            );

            $lockedBooking->update([
                'assignment_status' => 'accepted',
                'accepted_at' => now(),
            ]);
        });

        return back()->with('success', 'Booking accepted.');
    }

    public function cashReceived(
        string $bookingCode,
        ServiceBookingSettlement $settlement,
    ): RedirectResponse {
        $driver = Auth::guard('driver')->user();

        $booking = DriverServiceBooking::query()
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        abort_unless($booking->assigned_driver_id === $driver->getKey(), 403);

        $wasCompleted = false;
        $completedBooking = DB::transaction(function () use ($booking, $settlement, &$wasCompleted): DriverServiceBooking {
            $lockedBooking = DriverServiceBooking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedBooking->assigned_driver_id === Auth::guard('driver')->id(), 403);

            if ($lockedBooking->status === DriverServiceBooking::STATUS_COMPLETED) {
                return $lockedBooking;
            }

            $wasCompleted = true;

            abort_unless(
                $lockedBooking->payment_status === DriverServiceBooking::PAYMENT_PAID
                    && $lockedBooking->status === DriverServiceBooking::STATUS_CONFIRMED,
                422,
                'This booking is not ready to be completed.',
            );

            $lockedBooking->update([
                'cash_confirmed_at' => now(),
                'status' => DriverServiceBooking::STATUS_COMPLETED,
            ]);

            $settlement->complete($lockedBooking);

            return $lockedBooking->fresh(['driverService', 'assignedDriver']);
        }, attempts: 5);

        try {
            if ($wasCompleted) {
                Mail::to($driver)->send(new DriverServiceBookingCompletedMail($completedBooking));
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to send driver service completion email', [
                'booking_code' => $bookingCode,
                'recipient' => $driver->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            if ($wasCompleted) {
                Mail::to($completedBooking->customer_email)->send(new DriverServiceBookingCustomerCompletedMail($completedBooking));
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to send customer service booking completion email', [
                'booking_code' => $bookingCode,
                'recipient' => $completedBooking->customer_email,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Booking completed and cash received recorded.');
    }
}
