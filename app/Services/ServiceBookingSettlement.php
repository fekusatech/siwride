<?php

namespace App\Services;

use App\Models\DriverServiceBooking;
use App\Models\DriverWalletTransaction;
use App\Models\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServiceBookingSettlement
{
    public function __construct(
        private DriverWalletService $driverWalletService,
    ) {}

    public function complete(DriverServiceBooking $booking): ?DriverWalletTransaction
    {
        $driver = $booking->assignedDriver;

        if (! $driver) {
            throw (new ModelNotFoundException)->setModel('App\\Models\\User', [$booking->assigned_driver_id]);
        }

        $commissionPercent = (float) Setting::getValue('platform_commission_percent', 10);
        $commissionAmount = round((float) $booking->total_amount * ($commissionPercent / 100), 2);

        if ($commissionAmount <= 0) {
            return null;
        }

        $wallet = $this->driverWalletService->getOrCreateWallet($driver);

        return $this->driverWalletService->debit(
            $wallet,
            $commissionAmount,
            DriverWalletTransaction::TYPE_PLATFORM_COMMISSION,
            $booking->booking_code,
            "Platform commission for completed service booking {$booking->booking_code}",
            minimumBalance: bcsub('0', (string) $commissionAmount, 2),
        );
    }
}
