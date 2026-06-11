<?php

use App\Models\RideSharingCity;

it('shows the ride sharing booking page with locations', function () {
    RideSharingCity::create(['name' => 'Kuta', 'address' => 'Jl. Pantai Kuta']);
    RideSharingCity::create(['name' => 'Ubud', 'address' => 'Jl. Raya Ubud']);

    $response = $this->get('/ride-sharing');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('RideSharing/Index')
            ->has('locations', 2)
            ->has('availableRoutes')
            ->has('search');
    });
});

it('returns empty availableRoutes when no search params provided', function () {
    $response = $this->get('/ride-sharing');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('RideSharing/Index')
            ->where('availableRoutes', []);
    });
});

it('passes search params back to the page', function () {
    $from = RideSharingCity::create(['name' => 'Denpasar', 'address' => 'Ngurah Rai Airport']);
    $to = RideSharingCity::create(['name' => 'Seminyak', 'address' => 'Jl. Kayu Aya']);

    $response = $this->get("/ride-sharing?date=2026-07-01&pickup_location_id={$from->id}&dropoff_location_id={$to->id}&passengers=2");

    $response->assertStatus(200);
    $response->assertInertia(function ($page) use ($from, $to) {
        $page->component('RideSharing/Index')
            ->where('search.date', '2026-07-01')
            ->where('search.pickup_location_id', (string) $from->id)
            ->where('search.dropoff_location_id', (string) $to->id)
            ->where('search.passengers', 2);
    });
});

it('returns locations with address field', function () {
    RideSharingCity::create(['name' => 'Nusa Dua', 'address' => 'BTDC Area']);

    $response = $this->get('/ride-sharing');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('RideSharing/Index')
            ->has('locations.0', function ($city) {
                $city->where('name', 'Nusa Dua')
                    ->where('address', 'BTDC Area')
                    ->etc();
            });
    });
});
