<?php

use App\Models\RideSharingLocation;
use App\Models\RideSharingSchedule;

it('returns ride sharing locations and schedules on the home page', function () {
    RideSharingLocation::factory()->create(['name' => 'Kuta', 'area' => 'South Bali', 'is_active' => true]);
    RideSharingLocation::factory()->create(['name' => 'Ubud', 'area' => 'Central Bali', 'is_active' => false]);
    RideSharingSchedule::factory()->create(['departure_time' => '08:00', 'label' => 'Morning – 08:00', 'is_active' => true]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('Welcome')
            ->has('rideSharingLocations', 1) // only 1 active location
            ->has('rideSharingSchedules', 1)
            ->where('rideSharingLocations.0.name', 'Kuta');
    });
});

it('shows the ride sharing booking page', function () {
    RideSharingLocation::factory()->count(3)->create(['is_active' => true]);
    RideSharingSchedule::factory()->count(4)->create(['is_active' => true]);

    $response = $this->get('/ride-sharing');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('RideSharing/Index')
            ->has('locations', 3)
            ->has('schedules', 4)
            ->has('search');
    });
});

it('passes hero form search params to the ride sharing page', function () {
    $location = RideSharingLocation::factory()->create(['is_active' => true]);
    $schedule = RideSharingSchedule::factory()->create(['is_active' => true]);

    $response = $this->get("/ride-sharing?date=2026-07-01&pickup_location_id={$location->id}&dropoff_location_id={$location->id}&schedule_id={$schedule->id}&passengers=2");

    $response->assertStatus(200);
    $response->assertInertia(function ($page) use ($location, $schedule) {
        $page->component('RideSharing/Index')
            ->where('search.date', '2026-07-01')
            ->where('search.pickup_location_id', (string) $location->id)
            ->where('search.passengers', 2);
    });
});

it('only returns active ride sharing locations', function () {
    RideSharingLocation::factory()->create(['is_active' => true]);
    RideSharingLocation::factory()->create(['is_active' => false]);

    $response = $this->get('/ride-sharing');

    $response->assertInertia(function ($page) {
        $page->has('locations', 1);
    });
});

it('only returns active ride sharing schedules', function () {
    RideSharingSchedule::factory()->create(['is_active' => true]);
    RideSharingSchedule::factory()->create(['is_active' => false]);

    $response = $this->get('/ride-sharing');

    $response->assertInertia(function ($page) {
        $page->has('schedules', 1);
    });
});
