<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DriverServiceReassignApprovedMail;
use App\Mail\DriverServiceReassignRejectedMail;
use App\Models\DriverServiceReassignment;
use App\Models\DriverWalletTransaction;
use App\Services\DriverWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ServiceReassignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $statuses = ['pending', 'approved', 'rejected'];

        $reassignments = DriverServiceReassignment::query()
            ->with([
                'booking:id,booking_code,driver_service_id,customer_name,booking_date',
                'booking.driverService:id,title',
                'fromDriver:id,firstname,lastname,email',
                'toDriver:id,firstname,lastname,email',
                'decider:id,firstname,lastname',
            ])
            ->when($request->search, function ($query, string $search) {
                $query->whereHas('booking', fn ($q) => $q->where('booking_code', 'like', "%{$search}%"))
                    ->orWhereHas('fromDriver', fn ($q) => $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('toDriver', fn ($q) => $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                    ->orWhere('reason', 'like', "%{$search}%");
            })
            ->when(in_array($request->status, $statuses, true), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Reassignments/Index', [
            'reassignments' => $reassignments,
            'filters' => $request->only(['search', 'status']),
            'statuses' => $statuses,
        ]);
    }

    public function show(DriverServiceReassignment $reassignment): Response
    {
        $reassignment->load([
            'booking.driverService',
            'booking.assignedDriver',
            'fromDriver',
            'toDriver',
            'decider',
        ]);

        return Inertia::render('Admin/Reassignments/Show', [
            'reassignment' => $reassignment,
        ]);
    }

    public function approve(
        Request $request,
        DriverServiceReassignment $reassignment,
        DriverWalletService $walletService,
    ): RedirectResponse {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        abort_unless($reassignment->status === 'pending', 422, 'This reassignment is not pending.');

        DB::transaction(function () use ($reassignment, $walletService) {
            $lockedReassignment = DriverServiceReassignment::query()
                ->with('booking.driverService.driver')
                ->whereKey($reassignment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedReassignment->status === 'pending', 422, 'This reassignment is not pending.');

            $booking = $lockedReassignment->booking;
            $fromDriver = $lockedReassignment->fromDriver;
            $toDriver = $lockedReassignment->toDriver;

            // Reverse DP from old driver
            if ($fromDriver && (float) $booking->dp_amount > 0) {
                $fromWallet = $walletService->getOrCreateWallet($fromDriver);
                $walletService->debit(
                    $fromWallet,
                    $booking->dp_amount,
                    DriverWalletTransaction::TYPE_DP_REVERSAL,
                    $booking->booking_code,
                    "DP reversal for reassignment {$lockedReassignment->id} from driver {$fromDriver->id} to {$lockedReassignment->to_driver_id}",
                );
            }

            // Credit DP to new driver
            if ($toDriver && (float) $booking->dp_amount > 0) {
                $toWallet = $walletService->getOrCreateWallet($toDriver);
                $walletService->credit(
                    $toWallet,
                    $booking->dp_amount,
                    DriverWalletTransaction::TYPE_DP_PAYMENT,
                    $booking->booking_code,
                    "DP payment for reassignment {$lockedReassignment->id} from driver {$lockedReassignment->from_driver_id} to {$toDriver->id}",
                );
            }

            // Update booking assignment
            $booking->update([
                'assigned_driver_id' => $toDriver->getKey(),
                'assignment_status' => 'reassigned',
            ]);

            $lockedReassignment->update([
                'status' => 'approved',
                'decided_by' => auth()->id(),
                'decided_at' => now(),
            ]);

            // Send emails
            $fromDriverFresh = $fromDriver?->fresh();
            $toDriverFresh = $toDriver?->fresh();
            $reassignmentFresh = $lockedReassignment->fresh(['booking.driverService', 'fromDriver', 'toDriver']);

            try {
                if ($fromDriverFresh) {
                    Mail::to($fromDriverFresh)->send(new DriverServiceReassignRejectedMail($reassignmentFresh));
                }
                if ($toDriverFresh) {
                    Mail::to($toDriverFresh)->send(new DriverServiceReassignApprovedMail($reassignmentFresh));
                }
            } catch (\Throwable $exception) {
                Log::error('Failed to send reassignment approval emails', [
                    'reassignment_id' => $lockedReassignment->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }, attempts: 5);

        return back()->with('success', 'Reassignment approved and DP transferred.');
    }

    public function reject(
        Request $request,
        DriverServiceReassignment $reassignment,
    ): RedirectResponse {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        abort_unless($reassignment->status === 'pending', 422, 'This reassignment is not pending.');

        DB::transaction(function () use ($reassignment, $validated) {
            $lockedReassignment = DriverServiceReassignment::query()
                ->with('booking')
                ->whereKey($reassignment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedReassignment->status === 'pending', 422, 'This reassignment is not pending.');

            $lockedReassignment->update([
                'status' => 'rejected',
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            $lockedReassignment->booking->update([
                'assignment_status' => 'assigned',
            ]);

            // Send emails
            $reassignmentFresh = $lockedReassignment->fresh(['booking.driverService', 'fromDriver', 'toDriver']);

            try {
                if ($reassignmentFresh->fromDriver) {
                    Mail::to($reassignmentFresh->fromDriver)->send(new DriverServiceReassignRejectedMail($reassignmentFresh));
                }
            } catch (\Throwable $exception) {
                Log::error('Failed to send reassignment rejection email', [
                    'reassignment_id' => $lockedReassignment->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }, attempts: 5);

        return back()->with('success', 'Reassignment rejected.');
    }
}
