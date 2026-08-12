<?php

namespace App\Support;

use App\Models\ActivityBooking;
use App\Models\DriverReferral;
use App\Models\DriverServiceBooking;
use App\Models\Order;
use App\Models\Setting;

/**
 * Tracks commission owed to a driver when their own DriverService gets
 * booked. Unlike the old article/referral-link model, there's no separate
 * referred party here — the driver IS the service provider, so commission
 * is attributed directly at booking-creation time, no cookie needed.
 */
class DriverReferralAttribution
{
    /**
     * Record a pending-payment referral against a freshly created service
     * booking. It becomes payout-eligible only after the payment webhook
     * calls qualify().
     */
    public static function attributeService(DriverServiceBooking $booking): void
    {
        $service = $booking->driverService;

        DriverReferral::firstOrCreate(
            ['booking_key' => "service_booking:{$booking->id}"],
            [
                'driver_id' => $service->driver_id,
                'driver_service_id' => $service->id,
                'driver_service_booking_id' => $booking->id,
                'commission_amount' => (float) Setting::getValue('referral_commission_amount', 50000),
                'status' => DriverReferral::STATUS_PENDING_PAYMENT,
            ],
        );
    }

    public static function qualify(Order|ActivityBooking|DriverServiceBooking $booking): void
    {
        self::forBooking($booking)
            ->where('status', DriverReferral::STATUS_PENDING_PAYMENT)
            ->update(['status' => DriverReferral::STATUS_PENDING]);
    }

    public static function void(Order|ActivityBooking|DriverServiceBooking $booking): void
    {
        self::forBooking($booking)
            ->whereIn('status', [DriverReferral::STATUS_PENDING_PAYMENT, DriverReferral::STATUS_PENDING])
            ->update(['status' => DriverReferral::STATUS_VOID]);
    }

    private static function forBooking(Order|ActivityBooking|DriverServiceBooking $booking)
    {
        $bookingKey = match (true) {
            $booking instanceof Order => "order:{$booking->id}",
            $booking instanceof ActivityBooking => "activity:{$booking->id}",
            $booking instanceof DriverServiceBooking => "service_booking:{$booking->id}",
        };

        return DriverReferral::where('booking_key', $bookingKey);
    }

    private function __construct() {}
}
