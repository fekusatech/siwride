<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\VehicleCategory;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper to create a minimal zone-pricing setup so the controller
 * can calculate a price without throwing a ValidationException.
 *
 * @return array{zone: Zone, category: VehicleCategory, rule: ZonePricingRule}
 */
function makeZonePricingSetup(): array
{
    // Square polygon that contains the test lat/lng (lat: 1.0, lng: 103.0)
    $zone = Zone::create([
        'name' => 'Test Zone',
        'coordinates' => [
            ['lat' => 0.5, 'lng' => 102.5],
            ['lat' => 0.5, 'lng' => 103.5],
            ['lat' => 1.5, 'lng' => 103.5],
            ['lat' => 1.5, 'lng' => 102.5],
            ['lat' => 0.5, 'lng' => 102.5],
        ],
        'is_active' => true,
    ]);

    $category = VehicleCategory::create([
        'title' => 'Test Vehicle',
        'slug' => 'test-vehicle',
        'vehicle_type' => 'sedan',
        'base_price' => 200000,
        'price_per_km' => 5000,
        'capacity' => 4,
        'luggage_capacity' => 2,
        'is_active' => true,
    ]);

    $rule = ZonePricingRule::create([
        'pickup_zone_id' => $zone->id,
        'dropoff_zone_id' => $zone->id,
        'vehicle_category_id' => $category->id,
        'minimum_price' => 150000,
        'base_price' => 200000,
        'is_active' => true,
    ]);

    return compact('zone', 'category', 'rule');
}

it('creates a single order for a one-way booking', function () {
    $data = [
        'customer_name' => 'John Doe',
        'email' => 'john@example.com',
        'customer_phone' => '+6281234567890',
        'pickup_address' => 'Ngurah Rai Airport',
        'pickup_latitude' => 1.0,
        'pickup_longitude' => 103.0,
        'dropoff_address' => 'Kuta Beach Hotel',
        'dropoff_latitude' => 1.1,
        'dropoff_longitude' => 103.1,
        'date' => now()->addDays(3)->toDateString(),
        'time' => '10:00',
        'passengers' => 2,
        'trip_type' => 'one_way',
    ];

    $response = $this->post(route('orders.store'), $data);

    // Should create exactly 1 order
    expect(Order::count())->toBe(1);

    $order = Order::first();
    expect($order->trip_type)->toBe('one_way');
    expect($order->is_return_trip)->toBeFalse();
    expect($order->linked_order_id)->toBeNull();
});

it('creates two linked orders for a round-trip booking', function () {
    $returnDate = now()->addDays(5)->toDateString();

    $data = [
        'customer_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'customer_phone' => '+6281234567891',
        'pickup_address' => 'Ngurah Rai Airport',
        'pickup_latitude' => 1.0,
        'pickup_longitude' => 103.0,
        'dropoff_address' => 'Seminyak Villa',
        'dropoff_latitude' => 1.1,
        'dropoff_longitude' => 103.1,
        'date' => now()->addDays(3)->toDateString(),
        'time' => '09:00',
        'passengers' => 2,
        'trip_type' => 'round_trip',
        'return_date' => $returnDate,
        'return_time' => '14:00',
    ];

    $response = $this->post(route('orders.store'), $data);

    // Should create exactly 2 orders
    expect(Order::count())->toBe(2);

    $parent = Order::where('is_return_trip', false)->first();
    $returnOrder = Order::where('is_return_trip', true)->first();

    expect($parent)->not->toBeNull();
    expect($returnOrder)->not->toBeNull();

    // Parent links to return
    expect($parent->linked_order_id)->toBe($returnOrder->id);
    // Return links back to parent
    expect($returnOrder->linked_order_id)->toBe($parent->id);

    // Trip type set on both
    expect($parent->trip_type)->toBe('round_trip');
    expect($returnOrder->trip_type)->toBe('round_trip');

    // Return trip date/time
    expect($returnOrder->date->toDateString())->toBe($returnDate);
    expect($returnOrder->time)->toBe('14:00:00');

    // Return trip has reversed route
    expect($returnOrder->pickup_address)->toBe($parent->dropoff_address);
    expect($returnOrder->dropoff_address)->toBe($parent->pickup_address);

    // Customer email same
    expect($returnOrder->customer_email)->toBe($parent->customer_email);
});

it('calculates round-trip total as 2x base price plus extras once', function () {
    ['category' => $category] = makeZonePricingSetup();

    $data = [
        'customer_name' => 'Mike Smith',
        'email' => 'mike@example.com',
        'customer_phone' => '+6281111111',
        'pickup_address' => 'Airport',
        'pickup_latitude' => 1.0,
        'pickup_longitude' => 103.0,
        'dropoff_address' => 'Hotel',
        'dropoff_latitude' => 1.05,
        'dropoff_longitude' => 103.05,
        'date' => now()->addDays(2)->toDateString(),
        'time' => '08:00',
        'passengers' => 1,
        'trip_type' => 'round_trip',
        'return_date' => now()->addDays(4)->toDateString(),
        'return_time' => '16:00',
        'vehicle_category_id' => $category->id,
        'extras' => [
            ['label' => 'Baby Seat', 'price' => 50000],
        ],
    ];

    $response = $this->post(route('orders.store'), $data);
    $response->assertSessionHasNoErrors();
    $parent = Order::where('is_return_trip', false)->first();
    $returnTrip = Order::where('is_return_trip', true)->first();

    // Return trip has no extras (extras charged on parent only)
    expect($returnTrip->extras)->toBeNull();

    // Parent total = base*2 + extras
    $basePrice = (float) $returnTrip->price; // return trip price = one leg base only
    expect((float) $parent->price)->toBe($basePrice * 2 + 50000);
});

it('fails round-trip validation when return_date is missing', function () {
    $data = [
        'customer_name' => 'Tom Test',
        'email' => 'tom@example.com',
        'pickup_address' => 'Airport',
        'dropoff_address' => 'Hotel',
        'date' => now()->addDays(2)->toDateString(),
        'time' => '08:00',
        'passengers' => 1,
        'trip_type' => 'round_trip',
        // return_date intentionally missing
        'return_time' => '16:00',
    ];

    $response = $this->post(route('orders.store'), $data);

    $response->assertSessionHasErrors('return_date');
    expect(Order::count())->toBe(0);
});

it('fails round-trip validation when return_date is before departure date', function () {
    $data = [
        'customer_name' => 'Sam Test',
        'email' => 'sam@example.com',
        'pickup_address' => 'Airport',
        'dropoff_address' => 'Hotel',
        'date' => now()->addDays(5)->toDateString(),
        'time' => '08:00',
        'passengers' => 1,
        'trip_type' => 'round_trip',
        'return_date' => now()->addDays(2)->toDateString(), // before departure!
        'return_time' => '16:00',
    ];

    $response = $this->post(route('orders.store'), $data);

    $response->assertSessionHasErrors('return_date');
    expect(Order::count())->toBe(0);
});

it('booking page passes trip_type and return_date to prefill', function () {
    $response = $this->get('/booking/airport-transfer?pickup=Airport&dropoff=Hotel&date='.now()->addDays(3)->toDateString().'&trip_type=round_trip&return_date='.now()->addDays(5)->toDateString());

    $response->assertInertia(fn ($page) => $page
        ->component('customer/booking')
        ->where('prefill.trip_type', 'round_trip')
        ->where('prefill.return_date', now()->addDays(5)->toDateString())
    );
});

it('checkout page passes trip_type and return_date to transfer prop', function () {
    $category = VehicleCategory::create([
        'title' => 'Test Vehicle',
        'slug' => 'test-vehicle',
        'vehicle_type' => 'sedan',
        'base_price' => 200000,
        'price_per_km' => 5000,
        'capacity' => 4,
        'luggage_capacity' => 2,
        'is_active' => true,
    ]);

    $response = $this->get('/booking/checkout?vehicle_category_id='.$category->id.'&pickup=Airport&dropoff=Hotel&date='.now()->addDays(3)->toDateString().'&time=10:00&trip_type=round_trip&return_date='.now()->addDays(5)->toDateString().'&return_time=14:00');

    $response->assertInertia(fn ($page) => $page
        ->component('customer/checkout')
        ->where('transfer.trip_type', 'round_trip')
        ->where('transfer.return_time', '14:00')
    );
});
