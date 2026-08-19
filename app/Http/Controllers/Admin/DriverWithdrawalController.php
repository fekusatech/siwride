<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DriverWithdrawalStatusMail;
use App\Models\DriverWithdrawal;
use App\Services\DriverWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DriverWithdrawalController extends Controller
{
    public function index(Request $request): Response
    {
        $statuses = [
            DriverWithdrawal::STATUS_PENDING,
            DriverWithdrawal::STATUS_APPROVED,
            DriverWithdrawal::STATUS_REJECTED,
            DriverWithdrawal::STATUS_PAID,
        ];

        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => DriverWithdrawal::query()
                ->with('driver:id,firstname,lastname,email')
                ->when($request->search, function ($query, string $search) {
                    $query->where('bank_name', 'like', "%{$search}%")
                        ->orWhere('bank_account_number', 'like', "%{$search}%")
                        ->orWhere('bank_account_name', 'like', "%{$search}%")
                        ->orWhereHas('driver', fn ($q) => $q->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                })
                ->when(in_array($request->status, $statuses, true), fn ($query) => $query->where('status', $request->status))
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (DriverWithdrawal $withdrawal) => [
                    ...$withdrawal->toArray(),
                    'bank_account_last_four' => $withdrawal->bankAccountLastFour(),
                ]),
            'filters' => $request->only(['search', 'status']),
            'statuses' => $statuses,
        ]);
    }

    public function approve(DriverWithdrawal $withdrawal, DriverWithdrawalService $withdrawalService): RedirectResponse
    {
        try {
            $withdrawal = $withdrawalService->approve($withdrawal, auth()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())
                ->with('error', 'Withdrawal tidak dapat di-approve.');
        }

        $this->notify($withdrawal);

        return back()->with('success', 'Withdrawal approved.');
    }

    public function reject(Request $request, DriverWithdrawal $withdrawal, DriverWithdrawalService $withdrawalService): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $withdrawal = $withdrawalService->reject($withdrawal, auth()->user(), $validated['rejection_reason']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())
                ->with('error', 'Withdrawal tidak dapat di-reject.');
        }

        $this->notify($withdrawal);

        return back()->with('success', 'Withdrawal rejected.');
    }

    public function markPaid(DriverWithdrawal $withdrawal, DriverWithdrawalService $withdrawalService): RedirectResponse
    {
        try {
            $withdrawal = $withdrawalService->markPaid($withdrawal, auth()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())
                ->with('error', 'Withdrawal tidak dapat ditandai dibayar.');
        }

        $this->notify($withdrawal);

        return back()->with('success', 'Withdrawal marked as paid.');
    }

    private function notify(DriverWithdrawal $withdrawal): void
    {
        try {
            Mail::to($withdrawal->driver)->send(new DriverWithdrawalStatusMail($withdrawal->fresh('driver')));
        } catch (\Throwable $exception) {
            Log::error('Failed to send withdrawal status email', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
