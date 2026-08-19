<?php

use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Services\VoucherService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->service = DriverService::factory()->create([
        'driver_id' => User::factory()->create(['role' => 'driver'])->id,
        'price_per_pax' => 100000,
        'min_pax' => 1,
    ]);

    $this->customer = Customer::factory()->create();
});

it('validates a percent voucher and computes capped discount', function () {
    $voucher = Voucher::factory()->create([
        'type' => Voucher::TYPE_PERCENT,
        'value' => 10,
        'max_discount' => 20000,
        'min_spend' => 50000,
    ]);

    $result = app(VoucherService::class)->validate($voucher->code, 200000, $this->customer->email);

    expect($result['voucher']->id)->toBe($voucher->id)
        ->and($result['discount_amount'])->toBe(20000.0);
});

it('rejects voucher below minimum spend with specific message', function () {
    $voucher = Voucher::factory()->create(['min_spend' => 50000]);

    try {
        app(VoucherService::class)->validate($voucher->code, 40000, $this->customer->email);
        $this->fail('Expected validation to throw');
    } catch (ValidationException $exception) {
        expect($exception->errors()['voucher_code'][0])->toContain('Minimum pembelanjaan');
    }
});

it('rejects expired and inactive vouchers', function () {
    $expired = Voucher::factory()->create(['valid_until' => now()->subDay()]);
    $inactive = Voucher::factory()->create(['is_active' => false]);

    expect(fn () => app(VoucherService::class)->validate($expired->code, 200000, $this->customer->email))
        ->toThrow(ValidationException::class, 'Voucher sudah kedaluwarsa.');

    expect(fn () => app(VoucherService::class)->validate($inactive->code, 200000, $this->customer->email))
        ->toThrow(ValidationException::class, 'Voucher tidak aktif.');
});

it('redeems a voucher atomically and increments used count once', function () {
    $voucher = Voucher::factory()->create(['usage_limit' => 1]);

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    $redemption = app(VoucherService::class)->redeem($voucher, $booking, $this->customer->email, 100000);

    $voucher->refresh();

    expect($voucher->used_count)->toBe(1)
        ->and($redemption->voucher_id)->toBe($voucher->id)
        ->and($redemption->booking_id)->toBe($booking->id);
});

it('throws when usage limit is already reached (race-safe guard)', function () {
    $voucher = Voucher::factory()->create(['usage_limit' => 1, 'used_count' => 1]);

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    expect(fn () => app(VoucherService::class)->redeem($voucher, $booking, $this->customer->email, 100000))
        ->toThrow(ValidationException::class, 'Voucher sudah mencapai batas pemakaian.');
});

it('enforces per-user limit by email', function () {
    $voucher = Voucher::factory()->create(['usage_limit_per_user' => 1]);

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    app(VoucherService::class)->redeem($voucher, $booking, $this->customer->email, 100000);

    expect(fn () => app(VoucherService::class)->validate($voucher->code, 100000, $this->customer->email))
        ->toThrow(ValidationException::class, 'Voucher sudah digunakan untuk email ini.');
});

it('enforces per-user limit by email for guests without an account', function () {
    $voucher = Voucher::factory()->create(['usage_limit_per_user' => 1]);
    $guestEmail = 'guest-'.uniqid().'@example.com';

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => null,
        'customer_email' => $guestEmail,
    ]);

    app(VoucherService::class)->redeem($voucher, $booking, $guestEmail, 100000);

    expect(fn () => app(VoucherService::class)->validate($voucher->code, 100000, $guestEmail))
        ->toThrow(ValidationException::class, 'Voucher sudah digunakan untuk email ini.')
        ->and(fn () => app(VoucherService::class)->validate($voucher->code, 100000, 'other-'.$guestEmail))
        ->not->toThrow(ValidationException::class);
});

it('creates and lists vouchers from admin API', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'web')
        ->post(route('admin.promos.store'), [
            'code' => 'HEMAT20',
            'type' => Voucher::TYPE_PERCENT,
            'value' => 20,
            'min_spend' => 100000,
            'max_discount' => 50000,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
            'valid_from' => null,
            'valid_until' => null,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.promos.index'));

    $this->get(route('admin.promos.index'))->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Promos/Index'));
});

it('prevents deleting a voucher that has redemptions', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $voucher = Voucher::factory()->create();
    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    VoucherRedemption::create([
        'voucher_id' => $voucher->id,
        'booking_id' => $booking->id,
        'email' => $this->customer->email,
        'discount_amount' => 10000,
    ]);

    $this->actingAs($admin, 'web')
        ->delete(route('admin.promos.destroy', $voucher))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($voucher->fresh())->not->toBeNull();
});

it('keeps total discount equal to sum of redemptions', function () {
    $voucher = Voucher::factory()->create();

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    app(VoucherService::class)->redeem($voucher, $booking, $this->customer->email, 100000);

    expect((float) $voucher->redemptions()->sum('discount_amount'))->toBe(10000.0);
});

it('applies voucher discount to booking totals on store', function () {
    $voucher = Voucher::factory()->create([
        'type' => Voucher::TYPE_PERCENT,
        'value' => 10,
        'max_discount' => 20000,
    ]);

    $this->post(route('driver-services.book', $this->service->slug), [
        'booking_date' => now()->addDay()->toDateString(),
        'pax' => 2,
        'customer_name' => 'Budi',
        'customer_email' => $this->customer->email,
        'customer_phone' => '08123456789',
        'voucher_code' => $voucher->code,
    ]);

    $booking = DriverServiceBooking::where('customer_email', $this->customer->email)->latest()->firstOrFail();

    expect((string) $booking->subtotal)->toBe('200000.00')
        ->and((string) $booking->discount_amount)->toBe('20000.00')
        ->and((string) $booking->total_amount)->toBe('180000.00')
        ->and($booking->voucher_code)->toBe($voucher->code)
        ->and((string) $booking->dp_amount)->toBe('54000.00')
        ->and((string) $booking->remaining_cash)->toBe('126000.00');

    $voucher->refresh();
    expect($voucher->used_count)->toBe(1);
});

it('rejects invalid voucher code on store', function () {
    $this->post(route('driver-services.book', $this->service->slug), [
        'booking_date' => now()->addDay()->toDateString(),
        'pax' => 2,
        'customer_name' => 'Budi',
        'customer_email' => $this->customer->email,
        'voucher_code' => 'TIDAKADA',
    ])->assertSessionHasErrors('voucher_code');
});

it('rate limits voucher validation endpoint to 10 requests per minute', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson(route('driver-services.validate-voucher', $this->service->slug), [
            'code' => 'APAPUN',
            'pax' => 1,
        ])->assertStatus(422);
    }

    $this->postJson(route('driver-services.validate-voucher', $this->service->slug), [
        'code' => 'APAPUN',
        'pax' => 1,
    ])->assertStatus(429);
});
