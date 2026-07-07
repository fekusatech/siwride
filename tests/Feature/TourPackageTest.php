<?php

use App\Models\TourPackage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('displays featured tour packages on the homepage', function () {
    TourPackage::factory()->count(3)->create(['is_active' => true, 'sort_order' => 0]);
    TourPackage::factory()->create(['is_active' => false]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Welcome')
            ->has('tourPackages', 3)
    );
});

it('renders the tour packages booking page with active tours', function () {
    TourPackage::factory()->count(4)->create(['is_active' => true]);
    TourPackage::factory()->create(['is_active' => false]);

    $response = $this->get('/booking/tour');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('customer/booking-tour')
            ->has('tours', 4)
    );
});

it('filters tour packages by search query', function () {
    TourPackage::factory()->create([
        'is_active' => true,
        'name' => 'Ubud Cultural Tour',
        'description' => 'Explore the cultural heart of Bali',
    ]);
    TourPackage::factory()->create([
        'is_active' => true,
        'name' => 'South Bali Explorer',
        'description' => 'Visit the beaches of south Bali',
    ]);

    $response = $this->get('/booking/tour?search=Ubud');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('customer/booking-tour')
            ->has('tours', 1)
    );
});

it('shows a tour package detail page', function () {
    $tour = TourPackage::factory()->create([
        'is_active' => true,
        'slug' => 'bali-classic-tour',
    ]);

    $response = $this->get("/tour-packages/{$tour->slug}");

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('TourPackage/Show')
            ->where('tourPackage.slug', $tour->slug)
    );
});

it('returns 404 for inactive tour packages', function () {
    $tour = TourPackage::factory()->create([
        'is_active' => false,
        'slug' => 'inactive-tour',
    ]);

    $response = $this->get("/tour-packages/{$tour->slug}");

    $response->assertStatus(404);
});
