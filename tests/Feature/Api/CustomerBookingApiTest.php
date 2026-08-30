<?php

use App\Models\Order;
use App\Models\VehicleCategory;
use App\Services\CustomerOrderPaymentService;
use App\Services\CustomerTransferBookingService;
use App\Services\CustomerTransferPricingService;
use Illuminate\Support\Facades\Mail;

it('returns the customer booking catalog', function () {
    $vehicle = VehicleCategory::query()->create([
        'slug' => 'standard-car',
        'title' => 'Standard Car',
        'passenger_capacity' => 3,
        'luggage_capacity' => 3,
        'base_price' => 200000,
        'price_per_km' => 2000,
        'vehicle_type' => 'economy',
    ]);

    $this->getJson('/api/v1/customer/catalog')
        ->assertSuccessful()
        ->assertJsonPath('data.services.0.key', 'airport_transfer')
        ->assertJsonPath('data.services.0.native_booking', true)
        ->assertJsonPath('data.vehicles.0.id', $vehicle->id)
        ->assertJsonPath('data.vehicles.0.title', 'Standard Car');
});

it('validates customer price estimate coordinates', function () {
    $this->postJson('/api/v1/customer/price-estimates', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'pickup_latitude',
            'pickup_longitude',
            'dropoff_latitude',
            'dropoff_longitude',
        ]);
});

it('returns server calculated prices', function () {
    $this->mock(CustomerTransferPricingService::class)
        ->shouldReceive('estimate')
        ->once()
        ->andReturn([
            'pickup_zone' => 'Airport',
            'dropoff_zone' => 'Seminyak',
            'distance_km' => 12.5,
            'duration_minutes' => 25,
            'prices' => collect([[
                'id' => 1,
                'estimated_price' => 225000.0,
                'price_per_km' => 2000.0,
            ]]),
        ]);

    $this->postJson('/api/v1/customer/price-estimates', [
        'pickup_latitude' => -8.7488,
        'pickup_longitude' => 115.1670,
        'dropoff_latitude' => -8.6894,
        'dropoff_longitude' => 115.1565,
        'passengers' => 2,
    ])->assertSuccessful()
        ->assertJsonPath('data.distance_km', 12.5)
        ->assertJsonPath('data.prices.0.estimated_price', 225000);
});

it('creates a customer booking and returns its payment url', function () {
    Mail::fake();

    $vehicle = VehicleCategory::query()->create([
        'slug' => 'standard-car',
        'title' => 'Standard Car',
        'passenger_capacity' => 3,
        'luggage_capacity' => 3,
        'base_price' => 200000,
        'price_per_km' => 2000,
        'vehicle_type' => 'economy',
    ]);
    $order = Order::factory()->create([
        'booking_code' => 'SWABC123',
        'customer_email' => 'guest@example.com',
        'vehicle_category_id' => $vehicle->id,
        'date' => now()->addDay()->toDateString(),
    ]);

    $this->mock(CustomerTransferBookingService::class)
        ->shouldReceive('create')
        ->once()
        ->andReturn($order);
    $this->mock(CustomerOrderPaymentService::class)
        ->shouldReceive('createInvoice')
        ->once()
        ->andReturn('https://checkout.xendit.co/example');

    $this->postJson('/api/v1/customer/bookings', [
        'customer_name' => 'Guest Customer',
        'email' => 'guest@example.com',
        'customer_phone' => '08123456789',
        'pickup_address' => 'Ngurah Rai International Airport',
        'pickup_latitude' => -8.7488,
        'pickup_longitude' => 115.1670,
        'dropoff_address' => 'Seminyak, Bali',
        'dropoff_latitude' => -8.6894,
        'dropoff_longitude' => 115.1565,
        'date' => now()->addDay()->toDateString(),
        'time' => '10:30',
        'passengers' => 2,
        'vehicle_category_id' => $vehicle->id,
        'trip_type' => 'one_way',
    ])->assertCreated()
        ->assertJsonPath('data.booking_code', 'SWABC123')
        ->assertJsonPath('payment_url', 'https://checkout.xendit.co/example');
});

it('requires the booking email when tracking', function () {
    Order::factory()->create([
        'booking_code' => 'SWABC123',
        'customer_email' => 'owner@example.com',
        'is_return_trip' => false,
    ]);

    $this->postJson('/api/v1/customer/bookings/track', [
        'booking_code' => 'SWABC123',
        'email' => 'other@example.com',
    ])->assertNotFound();

    $this->postJson('/api/v1/customer/bookings/track', [
        'booking_code' => 'SWABC123',
        'email' => 'owner@example.com',
    ])->assertSuccessful()
        ->assertJsonPath('data.booking_code', 'SWABC123');
});
