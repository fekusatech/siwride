<?php

use App\Models\User;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin@siwride.com',
    ]);

    $this->airport = Zone::create([
        'name' => 'Airport & Tuban',
        'is_active' => true,
        'coordinates' => [
            ['lat' => -8.7400, 'lng' => 115.1600],
            ['lat' => -8.7400, 'lng' => 115.1920],
            ['lat' => -8.7680, 'lng' => 115.1920],
            ['lat' => -8.7680, 'lng' => 115.1600],
        ],
    ]);

    $this->kuta = Zone::create([
        'name' => 'Kuta',
        'is_active' => true,
        'coordinates' => [
            ['lat' => -8.7050, 'lng' => 115.1580],
            ['lat' => -8.7050, 'lng' => 115.1750],
            ['lat' => -8.7250, 'lng' => 115.1750],
            ['lat' => -8.7250, 'lng' => 115.1580],
        ],
    ]);

    ZonePricingRule::create([
        'pickup_zone_id' => $this->airport->id,
        'dropoff_zone_id' => $this->kuta->id,
        'base_price' => 150000,
        'price_per_km' => 0,
        'minimum_price' => 150000,
        'distance_km' => 12,
        'is_active' => true,
    ]);
});

test('admin can auto-calculate the price for an airport transfer route', function () {
    // A point inside the "Airport & Tuban" polygon (Ngurah Rai Intl. Airport).
    $response = $this->actingAs($this->admin)->postJson('/admin/zones/pricing/calculate', [
        'pickup_latitude' => -8.7488,
        'pickup_longitude' => 115.1670,
        'dropoff_latitude' => -8.7150,
        'dropoff_longitude' => 115.1650,
    ]);

    $response->assertOk();
    $response->assertJson([
        'price' => 150000,
        'pickup_zone' => ['id' => $this->airport->id, 'name' => 'Airport & Tuban'],
        'dropoff_zone' => ['id' => $this->kuta->id, 'name' => 'Kuta'],
        'message' => null,
    ]);
});
