<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class DisplayCurrency
{
    public const CURRENCIES = ['IDR', 'USD', 'GBP'];

    /** @return array{date: string, fetched_at: string, rates: array<string, string>} */
    public function refresh(): array
    {
        $data = Http::acceptJson()->timeout(10)->connectTimeout(5)
            ->retry([200, 500], when: fn (\Exception $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && $exception->response->serverError()))
            ->get('https://api.frankfurter.dev/v1/latest', ['base' => 'IDR', 'symbols' => 'USD,GBP'])
            ->throw()->json();

        if (($data['base'] ?? null) !== 'IDR' || ($data['amount'] ?? null) != 1 || ! $this->validDate($data['date'] ?? null)) {
            throw ValidationException::withMessages(['currency' => 'Invalid exchange rate response.']);
        }

        $rates = ['IDR' => '1'];
        foreach (['USD', 'GBP'] as $currency) {
            $rate = $data['rates'][$currency] ?? null;
            if (! is_numeric($rate) || ! is_finite((float) $rate) || $rate <= 0 || $rate >= 1) {
                throw ValidationException::withMessages(['currency' => 'Invalid exchange rate response.']);
            }
            $rates[$currency] = number_format((float) $rate, 12, '.', '');
        }
        $snapshot = ['date' => $data['date'], 'fetched_at' => now()->toIso8601String(), 'rates' => $rates];
        Setting::setValue('display_exchange_rates', json_encode($snapshot, JSON_THROW_ON_ERROR));

        return $snapshot;
    }

    private function validDate(mixed $date): bool
    {
        return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)
            && CarbonImmutable::hasFormat($date, 'Y-m-d')
            && $date <= now('UTC')->toDateString()
            && $date >= now('UTC')->subDays(7)->toDateString();
    }

    /** @return array{selected: string, settlement: string, date: ?string, rates: array<string, string>} */
    public function context(?string $selected = null): array
    {
        $snapshot = json_decode(Setting::getValue('display_exchange_rates', '{}'), true);
        $rates = ['IDR' => '1'];
        if ($this->validDate($snapshot['date'] ?? null)) {
            foreach (['USD', 'GBP'] as $currency) {
                $rate = $snapshot['rates'][$currency] ?? null;
                if (is_string($rate) && preg_match('/^0\.\d{1,12}$/D', $rate) && bccomp($rate, '0', 12) > 0) {
                    $rates[$currency] = $rate;
                }
            }
        }

        return [
            'selected' => array_key_exists($selected ?? '', $rates) ? $selected : 'IDR',
            'settlement' => 'IDR',
            'date' => count($rates) > 1 ? $snapshot['date'] : null,
            'rates' => $rates,
        ];
    }

    /** @return array<string, mixed> */
    public function capture(Model $booking, string $amount): array
    {
        if ($booking->display_quote !== null) {
            return $booking->display_quote;
        }
        $selected = request()->hasSession() ? request()->session()->get('display_currency', 'IDR') : 'IDR';
        $context = $this->context($selected);
        $currency = $context['selected'];
        $quote = [
            ...$context,
            'base_amount' => $amount,
            'amount' => bcround(bcmul($amount, $context['rates'][$currency], 14), $currency === 'IDR' ? 0 : 2),
        ];
        $booking->forceFill(['display_quote' => $quote])->save();

        return $quote;
    }
}
