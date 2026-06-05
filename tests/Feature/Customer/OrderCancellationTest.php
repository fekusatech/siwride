<?php

use App\Models\Order;
use App\Services\OrderCancellationService;
use Carbon\Carbon;
use Database\Seeders\VehicleCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(VehicleCategorySeeder::class);
});

// ──────────────────────────────────────────────────────────────────────────────
// OrderCancellationService Tests
// ──────────────────────────────────────────────────────────────────────────────

test('payment deadline is 10 minutes before pickup time', function () {
    $order = Order::factory()->create([
        'date' => '2024-06-05',
        'time' => '09:00:00',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    $deadline = $service->getPaymentDeadline($order);

    expect($deadline->format('Y-m-d H:i'))->toBe('2024-06-05 08:50');
});

test('formatted deadline string returns correct time', function () {
    $order = Order::factory()->create([
        'date' => '2024-06-05',
        'time' => '01:00:00',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    $formatted = $service->getFormattedDeadlineString($order);

    expect($formatted)->toBe('12:50 AM');
});

test('should not auto cancel order if status is not pending', function () {
    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => now()->subMinutes(15)->format('H:i:s'),
        'status' => 'confirmed',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->shouldAutoCancelOrder($order))->toBeFalse();
});

test('should not auto cancel order if already paid', function () {
    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => now()->subMinutes(15)->format('H:i:s'),
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $service = new OrderCancellationService();
    expect($service->shouldAutoCancelOrder($order))->toBeFalse();
});

test('should not auto cancel order if order date is not today', function () {
    $order = Order::factory()->create([
        'date' => now()->addDay()->toDateString(),
        'time' => now()->subMinutes(15)->format('H:i:s'),
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->shouldAutoCancelOrder($order))->toBeFalse();
});

test('should auto cancel order if current time passed payment deadline', function () {
    // Create an order for today with a pickup time in the past
    $pickupTime = now()->subMinutes(15)->format('H:i:s');

    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => $pickupTime,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->shouldAutoCancelOrder($order))->toBeTrue();
});

test('auto cancel order updates status to cancelled', function () {
    $pickupTime = now()->subMinutes(15)->format('H:i:s');

    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => $pickupTime,
        'status' => 'pending',
        'payment_status' => 'pending',
        'is_cancelled' => false,
    ]);

    $service = new OrderCancellationService();
    $service->autoCancelIfEligible($order);

    $order->refresh();
    expect($order->status)->toBe('cancelled');
    expect($order->is_cancelled)->toBeTrue();
});

test('can only manually cancel pending unpaid orders', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->canBeCancelled($order))->toBeTrue();
});

test('cannot manually cancel paid orders', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $service = new OrderCancellationService();
    expect($service->canBeCancelled($order))->toBeFalse();
});

test('cannot manually cancel cancelled orders', function () {
    $order = Order::factory()->create([
        'status' => 'cancelled',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->canBeCancelled($order))->toBeFalse();
});

test('manually cancel order updates status to cancelled', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
        'is_cancelled' => false,
    ]);

    $service = new OrderCancellationService();
    $result = $service->manuallyCancel($order);

    expect($result)->toBeTrue();
    $order->refresh();
    expect($order->status)->toBe('cancelled');
    expect($order->is_cancelled)->toBeTrue();
});

test('manually cancel fails if order is already paid', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $service = new OrderCancellationService();
    $result = $service->manuallyCancel($order);

    expect($result)->toBeFalse();
});

test('manually cancel fails if order is not pending', function () {
    $order = Order::factory()->create([
        'status' => 'confirmed',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    $result = $service->manuallyCancel($order);

    expect($result)->toBeFalse();
});

test('order is expired if current time is past pickup time', function () {
    $pickupTime = now()->subMinutes(5)->format('H:i:s');

    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => $pickupTime,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->isExpired($order))->toBeTrue();
});

test('order is not expired if current time is before pickup time', function () {
    $pickupTime = now()->addMinutes(30)->format('H:i:s');

    $order = Order::factory()->create([
        'date' => now()->toDateString(),
        'time' => $pickupTime,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $service = new OrderCancellationService();
    expect($service->isExpired($order))->toBeFalse();
});

// ──────────────────────────────────────────────────────────────────────────────
// Cancel Order Endpoint Tests
// ──────────────────────────────────────────────────────────────────────────────

test('can cancel pending unpaid order', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    test()->post(route('booking.cancel', $order->booking_code))
        ->assertOk()
        ->assertJsonPath('message', 'Order has been cancelled successfully.');

    $order->refresh();
    expect($order->status)->toBe('cancelled');
});

test('cannot cancel already cancelled order', function () {
    $order = Order::factory()->create([
        'status' => 'cancelled',
        'payment_status' => 'pending',
    ]);

    test()->post(route('booking.cancel', $order->booking_code))
        ->assertStatus(422)
        ->assertJsonPath('message', 'This order cannot be cancelled. It may have already been paid or cancelled.');
});

test('cannot cancel already paid order', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    test()->post(route('booking.cancel', $order->booking_code))
        ->assertStatus(422)
        ->assertJsonPath('message', 'This order cannot be cancelled. It may have already been paid or cancelled.');
});

test('cancel order endpoint returns updated order data', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    test()->post(route('booking.cancel', $order->booking_code))
        ->assertOk()
        ->assertJsonPath('order.status', 'cancelled')
        ->assertJsonPath('order.booking_code', $order->booking_code);
});

test('404 when trying to cancel non-existent order', function () {
    test()->post(route('booking.cancel', 'INVALID_CODE'))
        ->assertStatus(404);
});
