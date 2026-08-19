<?php

use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Tests\Support\VoucherConcurrencyHelper;

beforeEach(function () {
    $this->service = DriverService::factory()->create([
        'driver_id' => User::factory()->create(['role' => 'driver'])->id,
        'price_per_pax' => 100000,
        'min_pax' => 1,
    ]);

    $this->customer = Customer::factory()->create();
});

it('redeems concurrently only once when usage limit is 1 (race-safe)', function () {
    $voucher = Voucher::factory()->create(['usage_limit' => 1]);

    $bookingA = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => $this->customer->id,
        'customer_email' => $this->customer->email,
    ]);

    $bookingB = DriverServiceBooking::factory()->create([
        'driver_service_id' => $this->service->id,
        'customer_id' => null,
        'customer_email' => 'other-'.$this->customer->email,
    ]);

    $voucherId = $voucher->id;
    $bookingAId = $bookingA->id;
    $bookingBId = $bookingB->id;
    $emailA = $this->customer->email;
    $emailB = 'other-'.$this->customer->email;

    // Commit seed data so child processes can see it, then re-open a transaction.
    DB::commit();
    DB::beginTransaction();

    $results = Concurrency::run([
        VoucherConcurrencyHelper::attempt($voucherId, $bookingAId, $emailA),
        VoucherConcurrencyHelper::attempt($voucherId, $bookingBId, $emailB),
    ]);

    sort($results);

    expect($results)->toBe(['limit-reached', 'ok']);

    $voucher->refresh();

    expect($voucher->used_count)->toBe(1)
        ->and($voucher->redemptions()->count())->toBe(1);
});
