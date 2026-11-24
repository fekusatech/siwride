<?php

use App\Mail\BookingConfirmedMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\DisplayCurrency;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Http::preventStrayRequests();
});

function seedDisplayRates(): void
{
    Setting::setValue('display_exchange_rates', json_encode([
        'date' => now('UTC')->toDateString(), 'fetched_at' => now()->toIso8601String(),
        'rates' => ['IDR' => '1', 'USD' => '0.000054885000', 'GBP' => '0.000041977000'],
    ]));
}

it('refreshes and shares validated rates without fetching on page visits', function () {
    Http::fake(['api.frankfurter.dev/*' => Http::response([
        'base' => 'IDR', 'amount' => 1, 'date' => now('UTC')->toDateString(),
        'rates' => ['USD' => 0.000054885, 'GBP' => 0.000041977],
    ])]);
    $this->artisan('currency:refresh')->assertSuccessful();
    expect(app(DisplayCurrency::class)->context('USD')['rates']['USD'])->toBe('0.000054885000');
    $this->from(route('booking'))->put(route('currency.update'), ['currency' => 'USD'])->assertRedirect(route('booking'))->assertSessionHas('display_currency', 'USD');
    $this->get(route('booking'))->assertInertia(fn (Assert $page) => $page->where('currency.selected', 'USD')->where('currency.settlement', 'IDR'));
    Http::assertSentCount(1);
});

it('rejects unsupported currencies', function () {
    $this->putJson(route('currency.update'), ['currency' => 'EUR'])->assertUnprocessable()->assertJsonValidationErrors('currency');
});

it('keeps the display currency from catalog through transfer checkout', function (string $currency) {
    seedDisplayRates();
    $catalogUrl = route('catalog', ['search' => 'Bali', 'sort' => 'price_low']);
    $this->get($catalogUrl)->assertSuccessful();
    $this->from($catalogUrl)->put(route('currency.update'), ['currency' => $currency])
        ->assertRedirect($catalogUrl)
        ->assertSessionHas('display_currency', $currency);

    $this->get(route('booking.airport-transfer'))->assertInertia(fn (Assert $page) => $page
        ->component('customer/booking')
        ->where('currency.selected', $currency)
        ->where('currency.settlement', 'IDR'));

    $checkoutUrl = route('booking.checkout', [
        'pickup' => 'Ngurah Rai Airport',
        'dropoff' => 'Ubud',
        'passengers' => '2',
        'date' => now()->addDay()->toDateString(),
    ]);
    $this->get($checkoutUrl)->assertInertia(fn (Assert $page) => $page
        ->component('customer/checkout')
        ->where('currency.selected', $currency)
        ->where('currency.settlement', 'IDR')
        ->where('transfer.pickup', 'Ngurah Rai Airport')
        ->where('transfer.dropoff', 'Ubud')
        ->where('transfer.passengers', '2'));

    $this->from($checkoutUrl)->put(route('currency.update'), ['currency' => 'IDR'])
        ->assertRedirect($checkoutUrl)
        ->assertSessionHas('display_currency', 'IDR');

    Http::assertNothingSent();
})->with(['IDR', 'USD', 'GBP']);

it('falls back to IDR when rates are missing or stale and refuses stale preferences', function () {
    seedDisplayRates();
    $this->travel(8)->days();
    expect(app(DisplayCurrency::class)->context('GBP')['selected'])->toBe('IDR');
    $this->putJson(route('currency.update'), ['currency' => 'GBP'])->assertUnprocessable();
    $this->putJson(route('currency.update'), ['currency' => 'IDR'])->assertRedirect();
});

it('keeps last good rates on provider failure', function () {
    seedDisplayRates();
    $before = Setting::getValue('display_exchange_rates');
    Http::fake(['api.frankfurter.dev/*' => Http::response([], 503)]);
    expect(fn () => app(DisplayCurrency::class)->refresh())->toThrow(RequestException::class);
    expect(Setting::getValue('display_exchange_rates'))->toBe($before);
});

it('rejects malformed and stale provider data without overwriting rates', function (array $change) {
    seedDisplayRates();
    $before = Setting::getValue('display_exchange_rates');
    Http::fake(['api.frankfurter.dev/*' => Http::response(array_replace([
        'base' => 'IDR', 'amount' => 1, 'date' => now('UTC')->toDateString(), 'rates' => ['USD' => 0.00005, 'GBP' => 0.00004],
    ], $change))]);
    expect(fn () => app(DisplayCurrency::class)->refresh())->toThrow(ValidationException::class);
    expect(Setting::getValue('display_exchange_rates'))->toBe($before);
})->with([
    [['base' => 'USD']], [['rates' => ['USD' => -1, 'GBP' => 0.00004]]],
    [['rates' => ['USD' => 0.00005]]], [['date' => '2000-01-01']], [['date' => '2026-02-30']],
]);

it('freezes half-up estimates and ignores client amounts and rates', function (string $currency, string $expected) {
    seedDisplayRates();
    $this->withSession(['display_currency' => $currency]);
    $this->put(route('currency.update'), ['currency' => $currency, 'amount' => 1, 'rate' => 1]);
    $order = Order::factory()->create(['price' => 1000000]);
    $quote = app(DisplayCurrency::class)->capture($order, '1000000.00');
    expect($quote['amount'])->toBe($expected)->and($quote['base_amount'])->toBe('1000000.00');
    Setting::setValue('display_exchange_rates', '{}');
    expect(app(DisplayCurrency::class)->capture($order->fresh(), '1000000.00'))->toBe($quote);
    $html = (new BookingConfirmedMail($order))->render();
    expect($html)->toContain('Payment currency: IDR 1.000.000', $currency.' '.number_format((float) $expected, 2, '.', ','));
})->with([['USD', '54.89'], ['GBP', '41.98']]);

it('does not retry permanent provider errors', function () {
    Http::fake(['api.frankfurter.dev/*' => Http::response([], 400)]);
    expect(fn () => app(DisplayCurrency::class)->refresh())->toThrow(RequestException::class);
    Http::assertSentCount(1);
});

it('recovers from a temporary provider error', function () {
    Http::fake(['api.frankfurter.dev/*' => Http::sequence()->push([], 503)->push([
        'base' => 'IDR', 'amount' => 1, 'date' => now('UTC')->toDateString(), 'rates' => ['USD' => 0.00005, 'GBP' => 0.00004],
    ])]);
    expect(app(DisplayCurrency::class)->refresh()['rates']['GBP'])->toBe('0.000040000000');
    Http::assertSentCount(2);
});
