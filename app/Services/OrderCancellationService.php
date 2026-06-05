<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderCancellationService
{
    /**
     * Payment deadline is 10 minutes before pickup time
     */
    private const PAYMENT_DEADLINE_MINUTES = 10;

    /**
     * Application timezone for order time comparisons.
     */
    private function appTimezone(): string
    {
        return config('app.timezone') ?: 'UTC';
    }

    /**
     * Calculate the payment deadline for an order (10 minutes before pickup)
     */
    public function getPaymentDeadline(Order $order): Carbon
    {
        $pickupDateTime = $this->getPickupDateTime($order);

        return $pickupDateTime->subMinutes(self::PAYMENT_DEADLINE_MINUTES);
    }

    /**
     * Get the pickup datetime from the order date and time.
     */
    private function getPickupDateTime(Order $order): Carbon
    {
        $dateString = $order->date instanceof Carbon
            ? $order->date->toDateString()
            : Carbon::parse($order->date)->toDateString();

        $timeString = is_string($order->time)
            ? $order->time
            : Carbon::parse($order->time)->format('H:i:s');

        if (strlen($timeString) === 5) {
            $timeString .= ':00';
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $dateString.' '.$timeString
            , $this->appTimezone()
        );
    }

    /**
     * Check if an order should be automatically cancelled
     *
     * Order should be cancelled if:
     * - Order date is today
     * - Status is pending
     * - Payment has not been completed
     * - Current time >= payment deadline (10 minutes before pickup)
     */
    public function shouldAutoCancelOrder(Order $order): bool
    {
        // Order must be pending
        if ($order->status !== 'pending') {
            return false;
        }

        // Order must be unpaid
        if ($order->payment_status === 'paid') {
            return false;
        }

        // Order must be for today
        if (! $order->date->isToday()) {
            return false;
        }

        // Check if current time >= payment deadline
        $deadline = $this->getPaymentDeadline($order);

        return now($this->appTimezone())->greaterThanOrEqualTo($deadline);
    }

    /**
     * Automatically cancel an order if conditions are met
     */
    public function autoCancelIfEligible(Order $order): bool
    {
        if ($this->shouldAutoCancelOrder($order)) {
            return $this->cancelOrder($order, 'automatic');
        }

        return false;
    }

    /**
     * Manually cancel an order (customer or admin initiated)
     */
    public function manuallyCancel(Order $order): bool
    {
        // Can only cancel pending orders
        if ($order->status !== 'pending') {
            return false;
        }

        // Can only cancel unpaid orders
        if ($order->payment_status === 'paid') {
            return false;
        }

        return $this->cancelOrder($order, 'manual');
    }

    /**
     * Cancel an order (internal helper)
     */
    private function cancelOrder(Order $order, string $cancelType = 'manual'): bool
    {
        $order->update([
            'status' => 'cancelled',
            'is_cancelled' => true,
        ]);

        return true;
    }

    /**
     * Check if order is eligible for cancellation
     */
    public function canBeCancelled(Order $order): bool
    {
        return $order->status === 'pending' && $order->payment_status !== 'paid';
    }

    /**
     * Get formatted payment deadline string for display
     */
    public function getFormattedDeadlineString(Order $order): string
    {
        $deadline = $this->getPaymentDeadline($order);

        return $deadline->format('h:i A');
    }

    /**
     * Check if order is expired (past pickup time or payment deadline)
     */
    public function isExpired(Order $order): bool
    {
        if ($order->status !== 'pending' || $order->payment_status === 'paid') {
            return false;
        }

        if (! $order->date->isToday()) {
            return false;
        }

        $pickupDateTime = $this->getPickupDateTime($order);

        return now($this->appTimezone())->greaterThan($pickupDateTime);
    }
}
