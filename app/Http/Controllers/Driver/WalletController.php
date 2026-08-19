<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverWalletTransaction;
use App\Models\DriverWithdrawal;
use App\Models\Setting;
use App\Services\DriverWalletService;
use App\Services\DriverWithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function index(
        Request $request,
        DriverWalletService $walletService,
        DriverWithdrawalService $withdrawalService,
    ): Response {
        $driver = Auth::guard('driver')->user();
        $wallet = $walletService->getOrCreateWallet($driver);
        $type = $request->string('type')->toString();
        $allowedTypes = [
            DriverWalletTransaction::TYPE_DP_PAYMENT,
            DriverWalletTransaction::TYPE_DP_REVERSAL,
            DriverWalletTransaction::TYPE_PLATFORM_COMMISSION,
            DriverWalletTransaction::TYPE_WITHDRAWAL,
            DriverWalletTransaction::TYPE_ADMIN_ADJUSTMENT,
        ];

        $transactions = $wallet->transactions()
            ->when(in_array($type, $allowedTypes, true), fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingWithdrawals = DriverWithdrawal::query()
            ->where('user_id', $driver->getKey())
            ->whereIn('status', [
                DriverWithdrawal::STATUS_PENDING,
                DriverWithdrawal::STATUS_APPROVED,
            ])
            ->latest()
            ->get();

        return Inertia::render('Driver/Wallet', [
            'wallet' => [
                'balance' => $wallet->balance,
                'is_negative' => $wallet->is_negative,
                'available_balance' => $withdrawalService->availableBalance($driver),
                'min_withdrawal_amount' => Setting::getValue('min_withdrawal_amount', 100000),
            ],
            'transactions' => $transactions,
            'filters' => [
                'type' => in_array($type, $allowedTypes, true) ? $type : '',
            ],
            'transactionTypes' => $allowedTypes,
            'withdrawals' => $pendingWithdrawals,
        ]);
    }
}
