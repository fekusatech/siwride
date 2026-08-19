<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverWalletTransaction;
use App\Models\User;
use App\Services\DriverWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverWalletController extends Controller
{
    public function show(User $user, DriverWalletService $walletService): Response
    {
        $wallet = $walletService->getOrCreateWallet($user);

        return Inertia::render('Admin/DriverWallets/Show', [
            'driver' => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'wallet' => [
                'balance' => (string) $wallet->balance,
                'is_negative' => (bool) $wallet->is_negative,
            ],
            'transactions' => $wallet->transactions()
                ->with('creator:id,firstname,lastname')
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn (DriverWalletTransaction $transaction) => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (string) $transaction->amount,
                    'balance_after' => (string) $transaction->balance_after,
                    'booking_code' => $transaction->booking_code,
                    'description' => $transaction->description,
                    'created_by_name' => $transaction->creator?->name,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                ]),
        ]);
    }

    public function storeAdjustment(
        Request $request,
        User $user,
        DriverWalletService $walletService,
    ): RedirectResponse {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $wallet = $walletService->getOrCreateWallet($user);

        $amount = bcadd((string) $validated['amount'], '0', 2);
        $isCredit = bccomp($amount, '0', 2) > 0;

        if ($isCredit) {
            $walletService->credit(
                $wallet,
                $amount,
                DriverWalletTransaction::TYPE_ADMIN_ADJUSTMENT,
                null,
                "Manual adjustment: {$validated['reason']}",
                auth()->id(),
            );
        } else {
            $walletService->debit(
                $wallet,
                bcsub('0', $amount, 2),
                DriverWalletTransaction::TYPE_ADMIN_ADJUSTMENT,
                null,
                "Manual adjustment: {$validated['reason']}",
                auth()->id(),
            );
        }

        return back()->with('success', 'Wallet adjustment applied.');
    }
}
