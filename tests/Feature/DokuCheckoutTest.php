<?php

use App\Mail\BookingConfirmedMail;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\DriverWalletTransaction;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Voucher;
use App\Services\CustomerOrderPaymentService;
use App\Services\DokuCheckout;
use App\Services\DokuSignature;
use App\Services\WhatsAppService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Http::preventStrayRequests();
    Mail::fake();
    config(['services.doku.enabled' => true, 'services.doku.environment' => 'sandbox', 'services.doku.client_id' => 'test-client', 'services.doku.secret_key' => 'test-secret']);
    Http::fake(['api-sandbox.doku.com/*' => function ($request) {
        return Http::response(['response' => [
            'order' => $request['order'], 'payment' => ['url' => 'https://sandbox.doku.com/checkout/test'],
        ]]);
    }]);
});

function notifyDoku(array $payload, bool $validSignature = true): TestResponse
{
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');
    $signature = app(DokuSignature::class)->generate('test-client', 'notification-1', $timestamp, '/api/webhooks/doku', $body, 'test-secret');

    return test()->call('POST', '/api/webhooks/doku', [], [], [], [
        'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
        'HTTP_CLIENT_ID' => 'test-client', 'HTTP_REQUEST_ID' => 'notification-1',
        'HTTP_REQUEST_TIMESTAMP' => $timestamp, 'HTTP_SIGNATURE' => $validSignature ? $signature : 'invalid',
    ], $body);
}

it('creates IDR checkout for each booking type and reuses the same pending attempt', function (string $class) {
    $booking = $class::factory()->create();
    $url = app(DokuCheckout::class)->create($booking, '100000', route('booking'));
    expect($url)->toBe('https://sandbox.doku.com/checkout/test');
    expect(app(DokuCheckout::class)->create($booking, '100000', route('booking')))->toBe($url);
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['order']['currency'] === 'IDR' && $request['order']['amount'] === 100000);
    expect($booking->fresh()->payment_method)->toBe('DOKU Checkout');
})->with([Order::class, ActivityBooking::class, DriverServiceBooking::class]);

it('includes both round trip legs in one IDR invoice', function () {
    $return = Order::factory()->create(['price' => 250000, 'is_return_trip' => true]);
    $order = Order::factory()->create(['price' => 300000, 'trip_type' => 'round_trip', 'linked_order_id' => $return->id]);
    app(CustomerOrderPaymentService::class)->createInvoice($order);
    Http::assertSent(fn ($request) => $request['order']['amount'] === 550000);
});

it('does not create another invoice after ambiguous timeout', function () {
    Http::swap(new Factory);
    Http::preventStrayRequests();
    Http::fake(['api-sandbox.doku.com/*' => Http::failedConnection()]);
    $order = Order::factory()->create();
    expect(fn () => app(DokuCheckout::class)->create($order, '100000', route('booking')))->toThrow(LogicException::class);
    expect(DB::table('doku_payments')->value('status'))->toBe('unknown');
    Http::fake();
    expect(fn () => app(DokuCheckout::class)->create($order, '100000', route('booking')))->toThrow(LogicException::class);
    Http::assertNothingSent();
});

it('rejects fractional IDR and existing invoices without making requests', function () {
    $order = Order::factory()->create(['payment_reference' => 'https://checkout.xendit.co/old']);
    expect(fn () => app(DokuCheckout::class)->create($order, '100.50', route('booking')))->toThrow(LogicException::class);
    expect(fn () => app(DokuCheckout::class)->create($order, '100000', route('booking')))->toThrow(LogicException::class);
    Http::assertNothingSent();
});

it('validates signed IDR webhooks and ignores failed checkout channel attempts', function () {
    $order = Order::factory()->create();
    app(DokuCheckout::class)->create($order, '100000', route('booking'));
    $payload = ['order' => ['invoice_number' => DB::table('doku_payments')->value('invoice_number'), 'amount' => 100000, 'currency' => 'IDR'], 'transaction' => ['status' => 'SUCCESS']];
    notifyDoku($payload, false)->assertForbidden();
    notifyDoku(array_replace_recursive($payload, ['order' => ['amount' => 1]]))->assertUnprocessable();
    notifyDoku(array_replace_recursive($payload, ['order' => ['currency' => 'USD']]))->assertUnprocessable();
    notifyDoku(array_replace_recursive($payload, ['transaction' => ['status' => 'FAILED']]))->assertSuccessful();
    expect($order->fresh()->payment_status)->toBe('pending');
    notifyDoku($payload)->assertSuccessful();
    notifyDoku($payload)->assertSuccessful();
    expect($order->fresh()->payment_status)->toBe('paid')->and($order->fresh()->status)->toBe('confirmed');
    Mail::assertSent(BookingConfirmedMail::class, 1);
});

it('accepts late success without reopening a cancelled booking', function () {
    $order = Order::factory()->create();
    app(DokuCheckout::class)->create($order, '100000', route('booking'));
    $order->update(['status' => 'cancelled']);
    notifyDoku(['order' => ['invoice_number' => DB::table('doku_payments')->value('invoice_number'), 'amount' => 100000], 'transaction' => ['status' => 'SUCCESS']])->assertSuccessful();
    expect($order->fresh()->payment_status)->toBe('paid')->and($order->fresh()->status)->toBe('cancelled');
    Mail::assertNothingSent();
});

it('routes service and activity web bookings to IDR checkout after voucher discounts', function (string $kind) {
    $this->mock(WhatsAppService::class)->shouldReceive('sendGroupMessage')->zeroOrMoreTimes();
    Setting::setValue('dp_percent_default', '30');
    Setting::setValue('display_exchange_rates', json_encode([
        'date' => now('UTC')->toDateString(), 'rates' => ['IDR' => '1', 'USD' => '0.000050000000', 'GBP' => '0.000040000000'],
    ]));
    $voucher = Voucher::factory()->create(['type' => 'percent', 'value' => 10]);
    $product = $kind === 'activity'
        ? Activity::factory()->create(['price_per_pax' => 150000, 'min_pax' => 1, 'is_active' => true])
        : DriverService::factory()->create(['price_per_pax' => 150000, 'min_pax' => 1, 'status' => 'approved', 'dp_percent' => 30]);
    $route = $kind === 'activity' ? 'activities.book' : 'driver-services.book';
    $this->withSession(['display_currency' => 'GBP'])->withHeader('X-Inertia', 'true')->post(route($route, $product->slug), [
        'booking_date' => now()->addDays(2)->toDateString(), 'pax' => 2,
        'customer_name' => 'Test Customer', 'customer_email' => 'test@example.com',
        'customer_phone' => '+6281234567890', 'voucher_code' => $voucher->code,
    ])->assertStatus(409)->assertHeader('X-Inertia-Location', 'https://sandbox.doku.com/checkout/test');
    Http::assertSent(fn ($request) => $request['order']['amount'] === 81000 && $request['order']['currency'] === 'IDR');
    $booking = $kind === 'activity' ? ActivityBooking::query()->first() : DriverServiceBooking::query()->first();
    expect($booking->display_quote['selected'])->toBe('GBP')
        ->and($booking->display_quote['amount'])->toBe('3.24')
        ->and((float) $booking->remaining_cash)->toBe(189000.0);
})->with(['activity', 'service']);

it('credits a driver deposit once on duplicate success notifications', function () {
    $booking = DriverServiceBooking::factory()->create(['dp_amount' => 81000]);
    app(DokuCheckout::class)->create($booking, '81000', route('booking'));
    $payload = ['order' => ['invoice_number' => DB::table('doku_payments')->value('invoice_number'), 'amount' => 81000], 'transaction' => ['status' => 'SUCCESS']];
    notifyDoku($payload)->assertSuccessful();
    notifyDoku($payload)->assertSuccessful();
    expect($booking->fresh()->payment_status)->toBe('paid');
    expect(DriverWalletTransaction::query()->where('booking_code', $booking->booking_code)->count())->toBe(1);
});

it('keeps ambiguous attempts on DOKU when the module is subsequently disabled', function () {
    Http::swap(new Factory);
    Http::preventStrayRequests();
    Http::fake(['api-sandbox.doku.com/*' => Http::failedConnection()]);
    $order = Order::factory()->create(['price' => 100000]);
    expect(fn () => app(CustomerOrderPaymentService::class)->createInvoice($order))->toThrow(LogicException::class);
    config(['services.doku.enabled' => false]);
    Http::fake();
    expect(fn () => app(CustomerOrderPaymentService::class)->createInvoice($order->fresh()))->toThrow(LogicException::class);
    Http::assertNothingSent();
});

it('rejects mismatched checkout responses without creating another payment', function () {
    Http::swap(new Factory);
    Http::preventStrayRequests();
    Http::fake(['api-sandbox.doku.com/*' => Http::response(['response' => ['order' => ['invoice_number' => 'wrong', 'amount' => 1], 'payment' => ['url' => 'https://attacker.example/checkout']]])]);
    $order = Order::factory()->create();
    expect(fn () => app(DokuCheckout::class)->create($order, '100000', route('booking')))->toThrow(LogicException::class);
    expect(DB::table('doku_payments')->value('status'))->toBe('unknown');
});
