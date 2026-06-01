<?php

use App\Models\VehicleCategory;
use Database\Seeders\VehicleCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/**
 * Helper that posts an order with the given date/time and returns the response.
 *
 * @param  string  $date  Y-m-d
 * @param  string  $time  H:i
 */
function postOrder(string $date, string $time): TestResponse
{
    $category = VehicleCategory::first();

    return test()->post(route('orders.store'), [
        'customer_name' => 'John Doe',
        'email' => 'john@example.com',
        'customer_phone' => '1234567890',
        'pickup_address' => 'Airport',
        'dropoff_address' => 'Hotel',
        'date' => $date,
        'time' => $time,
        'passengers' => 2,
        'vehicle_category_id' => $category->id,
    ]);
}

beforeEach(function () {
    $this->seed(VehicleCategorySeeder::class);
});

// ──────────────────────────────────────────────────────────────────────────────
// Bookings that must be REJECTED
// ──────────────────────────────────────────────────────────────────────────────

test('rejects a pickup time in the past for today', function () {
    // 25 minutes ago — clearly stale, outside both the 30-minute buffer and
    // the 10-minute grace period, so it must be rejected.
    $time = now()->subMinutes(25)->format('H:i');

    postOrder(now()->format('Y-m-d'), $time)
        ->assertSessionHasErrors(['time']);
});

test('rejects a pickup time that is 15 minutes too early (beyond grace period)', function () {
    // The customer would have needed 30 min lead time, so 15 min from now is
    // 15 minutes short of the 30-minute buffer.
    // Grace covers only 10 minutes — the remaining 5-minute gap must be rejected.
    $time = now()->addMinutes(15)->format('H:i');

    postOrder(now()->format('Y-m-d'), $time)
        ->assertSessionHasErrors(['time']);
});

// ──────────────────────────────────────────────────────────────────────────────
// Bookings that must be ACCEPTED
// ──────────────────────────────────────────────────────────────────────────────

test('accepts a pickup time 45 minutes from now (well within the buffer)', function () {
    $time = now()->addMinutes(45)->format('H:i');

    postOrder(now()->format('Y-m-d'), $time)
        ->assertSessionDoesntHaveErrors(['time']);
});

test('accepts a pickup time exactly at the 30-minute buffer boundary', function () {
    // Round up to the nearest 5-minute slot, matching the frontend rounding.
    $min = now()->addMinutes(30);
    $remainder = (int) $min->format('i') % 5;
    if ($remainder !== 0) {
        $min->addMinutes(5 - $remainder);
    }

    postOrder(now()->format('Y-m-d'), $min->format('H:i'))
        ->assertSessionDoesntHaveErrors(['time']);
});

test('accepts a pickup time within the 10-minute grace period (5 minutes short)', function () {
    // 5 minutes short of the 30-minute buffer; backend grace period covers this.
    $time = now()->addMinutes(25)->format('H:i');

    postOrder(now()->format('Y-m-d'), $time)
        ->assertSessionDoesntHaveErrors(['time']);
});

test('accepts any pickup time for a future date', function () {
    // An early-morning time on tomorrow would fail for today — but it's a future date.
    postOrder(now()->addDay()->format('Y-m-d'), '01:00')
        ->assertSessionDoesntHaveErrors(['time']);
});
