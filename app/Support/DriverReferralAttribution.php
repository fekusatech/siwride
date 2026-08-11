<?php

namespace App\Support;

use App\Models\ActivityBooking;
use App\Models\DriverArticle;
use App\Models\DriverReferral;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Tracks which driver's guide article, if any, led a visitor to a booking.
 *
 * A driver's "referral link" is simply the public URL of their approved
 * article — remember() drops a first-touch cookie when that page is read,
 * attribute() redeems it once a booking is actually created.
 */
class DriverReferralAttribution
{
    private const COOKIE_NAME = 'driver_ref';

    /**
     * Remember the driver behind an approved article, unless a visitor is
     * already carrying an earlier referral (first-touch wins).
     */
    public static function remember(Request $request, DriverArticle $article): void
    {
        if ($request->cookie(self::COOKIE_NAME)) {
            return;
        }

        $windowDays = (int) Setting::getValue('referral_window_days', 30);

        Cookie::queue(self::COOKIE_NAME, "{$article->driver_id}:{$article->id}", $windowDays * 24 * 60);
    }

    /**
     * Record a referral against a freshly created booking. It becomes payout-
     * eligible only after the payment webhook calls qualify().
     */
    public static function attribute(Request $request, Order|ActivityBooking $booking): void
    {
        if ($booking instanceof Order && $booking->payment_status === 'paid') {
            self::qualify($booking);

            return;
        }

        if ($booking instanceof ActivityBooking && $booking->payment_status === ActivityBooking::PAYMENT_PAID) {
            self::qualify($booking);

            return;
        }

        $cookie = $request->cookie(self::COOKIE_NAME);

        if (! $cookie || ! str_contains($cookie, ':')) {
            return;
        }

        [$driverId, $articleId] = explode(':', $cookie, 2);

        if (! ctype_digit($driverId) || ! ctype_digit($articleId)) {
            return;
        }

        $article = DriverArticle::whereKey((int) $articleId)
            ->where('driver_id', (int) $driverId)
            ->first();

        if (! $article) {
            return;
        }

        $bookingKey = $booking instanceof Order
            ? "order:{$booking->id}"
            : "activity:{$booking->id}";

        DriverReferral::firstOrCreate(
            ['booking_key' => $bookingKey],
            [
                'driver_id' => $article->driver_id,
                'driver_article_id' => $article->id,
                'order_id' => $booking instanceof Order ? $booking->id : null,
                'activity_booking_id' => $booking instanceof ActivityBooking ? $booking->id : null,
                'commission_amount' => (float) Setting::getValue('referral_commission_amount', 50000),
                'status' => DriverReferral::STATUS_PENDING_PAYMENT,
            ],
        );

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    public static function qualify(Order|ActivityBooking $booking): void
    {
        self::forBooking($booking)
            ->where('status', DriverReferral::STATUS_PENDING_PAYMENT)
            ->update(['status' => DriverReferral::STATUS_PENDING]);
    }

    public static function void(Order|ActivityBooking $booking): void
    {
        self::forBooking($booking)
            ->whereIn('status', [DriverReferral::STATUS_PENDING_PAYMENT, DriverReferral::STATUS_PENDING])
            ->update(['status' => DriverReferral::STATUS_VOID]);
    }

    private static function forBooking(Order|ActivityBooking $booking)
    {
        $bookingKey = $booking instanceof Order
            ? "order:{$booking->id}"
            : "activity:{$booking->id}";

        return DriverReferral::where('booking_key', $bookingKey);
    }

    private function __construct() {}
}
