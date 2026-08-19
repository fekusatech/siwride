<?php

namespace App\Services;

use App\Models\DriverWallet;
use App\Models\DriverWalletTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DriverWalletService
{
    public function getOrCreateWallet(User $user): DriverWallet
    {
        return DB::transaction(function () use ($user): DriverWallet {
            $wallet = DriverWallet::query()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if ($wallet !== null) {
                return $wallet;
            }

            return DriverWallet::query()->create([
                'user_id' => $user->getKey(),
                'balance' => 0,
                'is_negative' => false,
            ]);
        }, attempts: 5);
    }

    public function credit(
        DriverWallet $wallet,
        int|float|string $amount,
        string $type,
        ?string $bookingCode,
        string $description,
        ?int $createdBy = null,
    ): ?DriverWalletTransaction {
        return $this->record(
            $wallet,
            $this->positiveAmount($amount),
            $type,
            $bookingCode,
            $description,
            $createdBy,
        );
    }

    public function debit(
        DriverWallet $wallet,
        int|float|string $amount,
        string $type,
        ?string $bookingCode,
        string $description,
        ?int $createdBy = null,
        ?string $minimumBalance = null,
    ): ?DriverWalletTransaction {
        return $this->record(
            $wallet,
            bcsub('0', $this->positiveAmount($amount), 2),
            $type,
            $bookingCode,
            $description,
            $createdBy,
            $minimumBalance,
        );
    }

    private function record(
        DriverWallet $wallet,
        string $amount,
        string $type,
        ?string $bookingCode,
        string $description,
        ?int $createdBy,
        ?string $minimumBalance = null,
    ): ?DriverWalletTransaction {
        return DB::transaction(function () use ($wallet, $amount, $type, $bookingCode, $description, $createdBy, $minimumBalance): ?DriverWalletTransaction {
            $lockedWallet = DriverWallet::query()
                ->whereKey($wallet->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedWallet === null) {
                throw (new ModelNotFoundException)->setModel(DriverWallet::class, [$wallet->getKey()]);
            }

            if ($bookingCode !== null && DriverWalletTransaction::query()
                ->where('wallet_id', $lockedWallet->getKey())
                ->where('booking_code', $bookingCode)
                ->where('type', $type)
                ->exists()) {
                return null;
            }

            $lockedWallet->balance = bcadd((string) $lockedWallet->balance, $amount, 2);
            if ($minimumBalance !== null && bccomp((string) $lockedWallet->balance, $minimumBalance, 2) < 0) {
                throw new InvalidArgumentException('Wallet balance cannot fall below the allowed negative limit.');
            }

            $lockedWallet->is_negative = bccomp((string) $lockedWallet->balance, '0', 2) < 0;
            $lockedWallet->save();

            return $lockedWallet->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $lockedWallet->balance,
                'booking_code' => $bookingCode,
                'description' => $description,
                'created_by' => $createdBy,
            ]);
        }, attempts: 5);
    }

    private function positiveAmount(int|float|string $amount): string
    {
        if (! is_numeric($amount) || bccomp((string) $amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('Wallet amount must be greater than zero.');
        }

        return bcadd((string) $amount, '0', 2);
    }
}
