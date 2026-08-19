<?php

namespace App\Services;

use App\Models\DriverServiceBooking;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    /**
     * @return array{voucher: Voucher, discount_amount: float}
     */
    public function validate(string $code, float $subtotal, ?string $email = null): array
    {
        $voucher = Voucher::query()->where('code', $code)->first();

        if ($voucher === null) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        if (! $voucher->is_active) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher tidak aktif.',
            ]);
        }

        $now = now();

        if ($voucher->valid_from !== null && $now->lt($voucher->valid_from)) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher belum berlaku.',
            ]);
        }

        if ($voucher->valid_until !== null && $now->gt($voucher->valid_until)) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher sudah kedaluwarsa.',
            ]);
        }

        if ($voucher->min_spend > 0 && bccomp((string) $subtotal, (string) $voucher->min_spend, 2) < 0) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Minimum pembelanjaan Rp '.number_format((float) $voucher->min_spend, 0, ',', '.').' belum terpenuhi.',
            ]);
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher sudah mencapai batas pemakaian.',
            ]);
        }

        if ($voucher->usage_limit_per_user !== null && $email !== null && $voucher->redemptions()
            ->where('email', $email)
            ->count() >= $voucher->usage_limit_per_user) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher sudah digunakan untuk email ini.',
            ]);
        }

        return [
            'voucher' => $voucher,
            'discount_amount' => $this->discountAmount($voucher, $subtotal),
        ];
    }

    public function discountAmount(Voucher $voucher, float $subtotal): float
    {
        $discount = match ($voucher->type) {
            Voucher::TYPE_PERCENT => round($subtotal * ((float) $voucher->value / 100), 2),
            Voucher::TYPE_FIXED => (float) $voucher->value,
            default => 0.0,
        };

        if ($voucher->max_discount !== null && $discount > (float) $voucher->max_discount) {
            $discount = (float) $voucher->max_discount;
        }

        return min($discount, $subtotal);
    }

    /**
     * Atomically increments used_count and records a redemption. Throws if
     * the usage limit is already reached (race-safe).
     */
    public function redeem(Voucher $voucher, DriverServiceBooking $booking, string $email, float $subtotal): VoucherRedemption
    {
        $discountAmount = $this->discountAmount($voucher, $subtotal);

        return DB::transaction(function () use ($voucher, $booking, $email, $discountAmount): VoucherRedemption {
            $affected = DB::table('vouchers')
                ->where('id', $voucher->id)
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                        ->orWhereColumn('used_count', '<', 'usage_limit');
                })
                ->increment('used_count');

            if ($affected !== 1) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Voucher sudah mencapai batas pemakaian.',
                ]);
            }

            return VoucherRedemption::create([
                'voucher_id' => $voucher->id,
                'booking_id' => $booking->id,
                'email' => $email,
                'discount_amount' => $discountAmount,
            ]);
        }, attempts: 5);
    }
}
