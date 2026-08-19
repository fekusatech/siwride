<?php

namespace App\Services;

use App\Models\DriverWallet;
use App\Models\DriverWalletTransaction;
use App\Models\DriverWithdrawal;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DriverWithdrawalService
{
    public function __construct(
        private DriverWalletService $driverWalletService,
    ) {}

    public function availableBalance(User $driver): string
    {
        $wallet = $this->driverWalletService->getOrCreateWallet($driver);

        $locked = DriverWithdrawal::query()
            ->where('user_id', $driver->getKey())
            ->whereIn('status', [
                DriverWithdrawal::STATUS_PENDING,
                DriverWithdrawal::STATUS_APPROVED,
            ])
            ->sum('amount');

        return bcsub((string) $wallet->balance, (string) $locked, 2);
    }

    public function request(User $driver, int|float|string $amount, string $bankName, string $bankAccountNumber, string $bankAccountName): DriverWithdrawal
    {
        return DB::transaction(function () use ($driver, $amount, $bankName, $bankAccountNumber, $bankAccountName): DriverWithdrawal {
            $wallet = DriverWallet::query()
                ->where('user_id', $driver->getKey())
                ->lockForUpdate()
                ->first();

            if ($wallet === null) {
                throw (new ModelNotFoundException)->setModel(DriverWallet::class, [$driver->getKey()]);
            }

            $amount = bcadd((string) $amount, '0', 2);
            $minWithdrawal = (string) Setting::getValue('min_withdrawal_amount', 100000);

            if (bccomp($amount, $minWithdrawal, 2) < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Minimum withdrawal is Rp '.number_format((float) $minWithdrawal, 0, ',', '.').'.',
                ]);
            }

            $available = $this->availableBalance($driver);

            if (bccomp($amount, $available, 2) > 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Withdrawal cannot exceed available balance of Rp '.number_format((float) $available, 0, ',', '.').'.',
                ]);
            }

            return DriverWithdrawal::create([
                'user_id' => $driver->getKey(),
                'amount' => $amount,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'bank_account_name' => $bankAccountName,
                'status' => DriverWithdrawal::STATUS_PENDING,
            ]);
        }, attempts: 5);
    }

    public function approve(DriverWithdrawal $withdrawal, User $admin): DriverWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin): DriverWithdrawal {
            $locked = $this->lockWithdrawal($withdrawal);

            $this->ensurePending($locked);

            $locked->update([
                'status' => DriverWithdrawal::STATUS_APPROVED,
                'processed_by' => $admin->getKey(),
            ]);

            return $locked;
        }, attempts: 5);
    }

    public function reject(DriverWithdrawal $withdrawal, User $admin, string $reason): DriverWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin, $reason): DriverWithdrawal {
            $locked = $this->lockWithdrawal($withdrawal);

            $this->ensurePending($locked);

            $locked->update([
                'status' => DriverWithdrawal::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'processed_by' => $admin->getKey(),
            ]);

            return $locked;
        }, attempts: 5);
    }

    public function markPaid(DriverWithdrawal $withdrawal, User $admin): DriverWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin): DriverWithdrawal {
            $locked = $this->lockWithdrawal($withdrawal);

            if ($locked->status === DriverWithdrawal::STATUS_PAID) {
                return $locked;
            }

            if ($locked->status !== DriverWithdrawal::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'status' => 'Only approved withdrawals can be marked as paid.',
                ]);
            }

            $wallet = $this->driverWalletService->getOrCreateWallet($locked->driver);

            $this->driverWalletService->debit(
                $wallet,
                $locked->amount,
                DriverWalletTransaction::TYPE_WITHDRAWAL,
                null,
                "Withdrawal #{$locked->id} paid to {$locked->bank_name} {$locked->bankAccountLastFour()}",
                $admin->getKey(),
            );

            $locked->update([
                'status' => DriverWithdrawal::STATUS_PAID,
                'processed_by' => $admin->getKey(),
                'paid_at' => now(),
            ]);

            return $locked;
        }, attempts: 5);
    }

    private function lockWithdrawal(DriverWithdrawal $withdrawal): DriverWithdrawal
    {
        return DriverWithdrawal::query()
            ->whereKey($withdrawal->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensurePending(DriverWithdrawal $withdrawal): void
    {
        if ($withdrawal->status !== DriverWithdrawal::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'This withdrawal is no longer pending.',
            ]);
        }
    }
}
