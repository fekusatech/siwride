<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Mail\DriverServiceReassignRequestMail;
use App\Models\DriverServiceBooking;
use App\Models\DriverServiceReassignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ServiceBookingReassignController extends Controller
{
    public function request(Request $request, string $bookingCode): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $booking = DriverServiceBooking::query()
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        abort_unless($booking->assigned_driver_id === $driver->getKey(), 403);

        $validated = $request->validate([
            'to_driver_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $targetDriver = User::where('id', $validated['to_driver_id'])
            ->where('role', 'driver')
            ->where('status', 'active')
            ->first();

        abort_if(! $targetDriver, 422, 'Target driver not found or inactive.');

        if ($targetDriver->getKey() === $driver->getKey()) {
            throw ValidationException::withMessages([
                'to_driver_id' => 'You cannot reassign to yourself.',
            ]);
        }

        DB::transaction(function () use ($booking, $validated, $driver, $targetDriver) {
            $lockedBooking = DriverServiceBooking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedBooking->assigned_driver_id === $driver->getKey(), 403);

            abort_if(
                $lockedBooking->assignment_status === 'reassign_requested',
                422,
                'A reassignment is already pending for this booking.',
            );

            $lockedBooking->update([
                'assignment_status' => 'reassign_requested',
            ]);

            $reassignment = DriverServiceReassignment::create([
                'booking_id' => $lockedBooking->id,
                'from_driver_id' => $driver->getKey(),
                'to_driver_id' => $targetDriver->getKey(),
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);
        }, attempts: 5);

        try {
            Mail::to($targetDriver)->send(new DriverServiceReassignRequestMail(
                DriverServiceReassignment::with('booking.driverService', 'fromDriver', 'toDriver')
                    ->where('booking_id', $booking->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->firstOrFail()
            ));
        } catch (\Throwable $exception) {
            Log::error('Failed to send reassignment request email', [
                'booking_code' => $booking->booking_code,
                'target_driver_id' => $targetDriver->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Reassignment request submitted for admin approval.');
    }
}
