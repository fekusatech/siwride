<?php

namespace App\Services;

use App\Models\ActivityBooking;
use App\Models\DriverServiceBooking;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

class DokuCheckout
{
    public function __construct(private DokuClient $client, private DokuConfiguration $configuration) {}

    public function enabled(Order|ActivityBooking|DriverServiceBooking $booking): bool
    {
        return $booking->payment_method === 'DOKU Checkout'
            || (! request()->is('api/*') && ! $booking->payment_reference && $this->configuration->active()['enabled'] === true);
    }

    public function create(Order|ActivityBooking|DriverServiceBooking $booking, string $amount, string $callbackUrl): string
    {
        $configuration = $this->configuration->active();
        if ($configuration['enabled'] !== true) {
            throw new LogicException('DOKU module is disabled.');
        }
        if (! preg_match('/^\d+(\.\d{1,2})?$/D', $amount) || bccomp($amount, '0', 2) <= 0 || bccomp($amount, '999999999999', 2) > 0) {
            throw new LogicException('Invalid IDR payment amount.');
        }
        // ponytail: DOKU accepts whole IDR; reject fractional totals until pricing supports explicit IDR rounding.
        if (bccomp($amount, bcround($amount, 0), 2) !== 0) {
            throw new LogicException('DOKU requires a whole IDR amount. Please contact support.');
        }
        $amount = bcround($amount, 0);

        $payment = DB::transaction(function () use ($booking, $amount, $configuration) {
            $locked = $booking->newQuery()->lockForUpdate()->findOrFail($booking->getKey());
            if ($locked->payment_status === 'paid' || $locked->status !== 'pending') {
                throw new LogicException('Booking is no longer awaiting payment.');
            }
            $existing = DB::table('doku_payments')->where('booking_type', $booking::class)->where('booking_id', $booking->getKey())->latest('id')->first();
            if ($existing) {
                return $existing;
            }
            if ($locked->payment_reference) {
                throw new LogicException('An existing payment must be reconciled before switching providers.');
            }
            app(DisplayCurrency::class)->capture($locked, $amount);
            $locked->update(['payment_method' => 'DOKU Checkout']);
            $id = DB::table('doku_payments')->insertGetId([
                'invoice_number' => 'D'.strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 24)),
                'booking_type' => $booking::class,
                'booking_id' => $booking->getKey(),
                'environment' => $configuration['environment'],
                'client_id' => $configuration['client_id'],
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => 'creating',
                'expires_at' => now()->addHour(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('doku_payments')->find($id);
        }, attempts: 5);

        if (bccomp((string) $payment->amount, $amount, 2) !== 0 || $payment->client_id !== $configuration['client_id'] || $payment->environment !== $configuration['environment']) {
            throw new LogicException('Existing payment requires reconciliation before changing amount or merchant configuration.');
        }

        if ($payment->payment_url !== null && $payment->status === 'pending' && now()->lt($payment->expires_at)) {
            $this->attach($booking, $payment->payment_url, $payment->expires_at);

            return $payment->payment_url;
        }
        // ponytail: ambiguous/expired attempts require reconciliation; add status/cancel APIs before automated replacement.
        $claimed = DB::table('doku_payments')->where('id', $payment->id)->where('status', 'creating')->update(['status' => 'processing', 'updated_at' => now()]);
        if ($claimed !== 1) {
            throw new LogicException('Payment is being processed or requires reconciliation. Please contact support before retrying.');
        }

        try {
            $response = $this->client->post('/checkout/v1/payment', [
                'order' => [
                    'amount' => (int) $amount,
                    'invoice_number' => $payment->invoice_number,
                    'currency' => 'IDR',
                    'callback_url' => $callbackUrl,
                    'callback_url_result' => $callbackUrl,
                    'auto_redirect' => true,
                    'language' => 'EN',
                ],
                'payment' => ['payment_due_date' => 60],
                'customer' => ['name' => $booking->customer_name, 'email' => $booking->customer_email],
                'additional_info' => ['override_notification_url' => route('webhooks.doku')],
            ])->json('response');
            $url = $response['payment']['url'] ?? null;
            $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;
            if (! is_string($url) || parse_url($url, PHP_URL_SCHEME) !== 'https' || ! is_string($host)
                || ! ($host === 'doku.com' || str_ends_with($host, '.doku.com'))
                || ($response['order']['invoice_number'] ?? null) !== $payment->invoice_number
                || ! is_numeric($response['order']['amount'] ?? null)
                || bccomp((string) $response['order']['amount'], $amount, 2) !== 0
                || ($response['order']['currency'] ?? 'IDR') !== 'IDR') {
                throw new LogicException('Invalid DOKU checkout response.');
            }
            DB::transaction(function () use ($payment, $booking, $url): void {
                $current = DB::table('doku_payments')->where('id', $payment->id)->lockForUpdate()->first();
                DB::table('doku_payments')->where('id', $payment->id)->update([
                    'status' => $current->status === 'paid' ? 'paid' : 'pending',
                    'payment_url' => $url,
                    'updated_at' => now(),
                ]);
                $this->attach($booking, $url, $payment->expires_at);
            });

            return $url;
        } catch (Throwable $exception) {
            DB::table('doku_payments')->where('id', $payment->id)->where('status', 'processing')->update(['status' => 'unknown', 'updated_at' => now()]);
            report($exception);
            throw new LogicException('DOKU payment requires reconciliation. Please contact support before retrying.', previous: $exception);
        }
    }

    private function attach(Order|ActivityBooking|DriverServiceBooking $booking, string $url, string $expiresAt): void
    {
        $booking->refresh();
        $attributes = ['payment_method' => 'DOKU Checkout', 'payment_reference' => $url];
        if ($booking instanceof Order) {
            $attributes['payment_expiry'] = $expiresAt;
        }
        $booking->update($attributes);
    }
}
