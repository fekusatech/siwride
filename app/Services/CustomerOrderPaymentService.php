<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use GuzzleHttp\Client;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class CustomerOrderPaymentService
{
    public function createInvoice(Order $order): string
    {
        $amount = (string) $order->price;
        $linkedOrder = null;

        if ($order->trip_type === 'round_trip' && $order->linked_order_id && ! $order->is_return_trip) {
            $linkedOrder = $order->linkedOrder()->firstOrFail();
            $amount = bcadd($amount, (string) $linkedOrder->price, 2);
        }

        $quote = app(DisplayCurrency::class)->capture($order, $amount);
        if ($linkedOrder && $linkedOrder->display_quote === null) {
            $linkedOrder->forceFill(['display_quote' => $quote])->save();
        }
        if (app(DokuCheckout::class)->enabled($order)) {
            return app(DokuCheckout::class)->create($order, (string) $amount, route('booking.show', $order->booking_code));
        }

        $xenditKey = Setting::getValue('xendit_secret_key') ?: config('services.xendit.secret_key');
        Configuration::setXenditKey($xenditKey);

        $client = new Client([
            'verify' => ! app()->environment('local'),
            'timeout' => 15,
            'connect_timeout' => 5,
        ]);
        $api = new InvoiceApi($client);
        $invoice = $api->createInvoice(new CreateInvoiceRequest([
            'external_id' => $order->booking_code.'_'.time(),
            'amount' => $amount,
            'payer_email' => $order->customer_email,
            'description' => 'Payment for Booking '.$order->booking_code,
            'success_redirect_url' => route('booking.show', $order->booking_code).'?payment=success',
            'failure_redirect_url' => route('booking.show', $order->booking_code).'?payment=failed',
        ]));
        $paymentUrl = $invoice->getInvoiceUrl();

        $order->update([
            'payment_method' => 'Xendit Invoice',
            'payment_reference' => $paymentUrl,
            'payment_status' => 'pending',
            'payment_expiry' => now()->addHours(24),
        ]);

        return str_starts_with($paymentUrl, 'http')
            ? $paymentUrl
            : route('booking.payment-success', ['code' => $order->booking_code]);
    }
}
