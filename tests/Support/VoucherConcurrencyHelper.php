<?php

namespace Tests\Support;

use App\Models\DriverServiceBooking;
use App\Models\Voucher;
use App\Services\VoucherService;
use Closure;
use Illuminate\Validation\ValidationException;

class VoucherConcurrencyHelper
{
    public static function attempt(int $voucherId, int $bookingId, string $email): Closure
    {
        return static function () use ($voucherId, $bookingId, $email): string {
            try {
                $voucher = Voucher::findOrFail($voucherId);
                $booking = DriverServiceBooking::findOrFail($bookingId);

                app(VoucherService::class)->redeem($voucher, $booking, $email, 100000);

                return 'ok';
            } catch (ValidationException) {
                return 'limit-reached';
            }
        };
    }
}
