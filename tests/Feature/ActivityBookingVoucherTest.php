<?php

use App\Mail\PaymentReminderMail;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Voucher;
use App\Services\VoucherService;

beforeEach(function () {
    Setting::setValue('dp_percent_default', '30');
    $this->activity = Activity::factory()->create([
        'price_per_pax' => 150000,
        'min_pax' => 1,
    ]);
    $this->customer = Customer::factory()->create();
});

it('renders activity detail page with dp_percent', function () {
    $this->get(route('activities.show', $this->activity->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('payment.dp_percent'));
});

it('validates a voucher via the activity validate-voucher endpoint', function () {
    $voucher = Voucher::factory()->create([
        'type' => Voucher::TYPE_PERCENT,
        'value' => 10,
    ]);

    $response = $this->postJson(route('activities.validate-voucher', $this->activity->slug), [
        'code' => $voucher->code,
        'pax' => 2,
        'email' => $this->customer->email,
    ]);

    $response->assertOk()
        ->assertJson([
            'valid' => true,
            'code' => $voucher->code,
            'subtotal' => 300000,
            'discount_amount' => 30000,
            'total_amount' => 270000,
        ]);
});

it('rejects an invalid voucher code via the endpoint', function () {
    $response = $this->postJson(route('activities.validate-voucher', $this->activity->slug), [
        'code' => 'NOTEXIST',
        'pax' => 2,
    ]);

    $response->assertStatus(422)
        ->assertJson(['valid' => false]);
});

it('redeems a voucher against an activity booking and stores polymorphic booking_type', function () {
    $voucher = Voucher::factory()->create(['usage_limit' => 1]);

    $booking = ActivityBooking::factory()->create([
        'activity_id' => $this->activity->id,
        'voucher_code' => $voucher->code,
        'subtotal' => 300000,
        'discount_amount' => 30000,
        'total_price' => 270000,
    ]);

    $redemption = app(VoucherService::class)->redeem(
        $voucher,
        $booking,
        $booking->customer_email,
        300000,
    );

    expect($redemption->booking_type)->toBe('activity')
        ->and($redemption->booking_id)->toBe($booking->id)
        ->and($redemption->discount_amount)->toBe('30000.00');

    $voucher->refresh();
    expect($voucher->used_count)->toBe(1);
});

it('renders activity payment reminder email with discount and DP breakdown', function () {
    $booking = ActivityBooking::factory()->create([
        'activity_id' => $this->activity->id,
        'voucher_code' => 'SAVE10',
        'discount_amount' => 30000,
        'subtotal' => 300000,
        'total_price' => 270000,
        'dp_percent' => 30,
        'dp_amount' => 81000,
        'remaining_cash' => 189000,
    ]);

    $html = (new PaymentReminderMail($booking, 'https://xendit.test/pay', isActivity: true))->render();

    expect($html)->toContain('Discount (SAVE10)')
        ->and($html)->toContain('− IDR 30.000')
        ->and($html)->toContain('Bayar sekarang (DP 30%)')
        ->and($html)->toContain('IDR 81.000')
        ->and($html)->toContain('Sisa tunai')
        ->and($html)->toContain('IDR 189.000');
});
