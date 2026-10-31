<?php

use App\Models\Customer;
use App\Models\Order;

it('does not disclose partial or wildcard booking matches', function (string $query) {
    Order::factory()->create(['booking_code' => 'SWABC123']);
    $this->getJson(route('booking.search', ['query' => $query]))->assertSuccessful()->assertJsonCount(0, 'orders');
})->with(['SW', 'SWABC', '%SW%', 'ORD']);

it('returns only minimal masked search results for exact codes', function () {
    Order::factory()->create(['booking_code' => 'SWABC123', 'customer_name' => 'Private Name']);
    $response = $this->getJson(route('booking.search', ['query' => 'SWABC123']))->assertSuccessful()->assertJsonCount(1, 'orders');
    expect(array_keys($response->json('orders.0')))->toBe(['booking_code', 'date', 'status', 'customer_name']);
    $response->assertJsonPath('orders.0.customer_name', 'P***');
});

it('requires the matching email for guest cancellation', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'pending', 'customer_email' => 'owner@example.com']);
    $this->postJson(route('booking.cancel', $order->booking_code))->assertUnprocessable();
    $this->postJson(route('booking.cancel', $order->booking_code), ['email' => 'wrong@example.com'])->assertForbidden();
    expect($order->fresh()->status)->toBe('pending');
    $this->postJson(route('booking.cancel', $order->booking_code), ['email' => 'OWNER@example.com'])->assertSuccessful();
    expect($order->fresh()->status)->toBe('cancelled');
});

it('rejects cancellation by another signed in customer', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'pending']);
    $this->actingAs(Customer::factory()->create(), 'customer')->postJson(route('booking.cancel', $order->booking_code))->assertForbidden();
});

it('throttles public booking searches', function () {
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->getJson(route('booking.search', ['query' => 'SWABC123']))->assertSuccessful();
    }
    $this->getJson(route('booking.search', ['query' => 'SWABC123']))->assertTooManyRequests();
});

it('does not expose cancellation email through guest booking pages', function () {
    $order = Order::factory()->create(['customer_id' => null, 'customer_email' => 'private@example.com', 'date' => now()->addDays(3)]);
    $this->get(route('booking.show', $order->booking_code))->assertSuccessful()
        ->assertInertia(fn ($page) => $page->missing('order.customer_email')->missing('order.customer'));
    $this->get(route('booking.payment-success', ['code' => $order->booking_code]))->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('order.email', null));
});
