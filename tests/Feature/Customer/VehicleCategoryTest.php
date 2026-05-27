<?php

use App\Models\VehicleCategory;
use Database\Seeders\VehicleCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('homepage can be rendered with vehicle categories', function () {
    $this->seed(VehicleCategorySeeder::class);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('vehicleCategories', 5)
    );
});

test('vehicles index page can be rendered with all categories', function () {
    $this->seed(VehicleCategorySeeder::class);

    $response = $this->get(route('vehicles'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customer/vehicles')
        ->has('vehicleCategories', 5)
    );
});

test('vehicle details page can be rendered for valid slug', function () {
    $this->seed(VehicleCategorySeeder::class);

    $category = VehicleCategory::first();

    $response = $this->get(route('vehicles.slug', ['slug' => $category->slug]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customer/vehicles/[slug]')
        ->has('vehicleCategory')
        ->where('vehicleCategory.slug', $category->slug)
    );
});

test('vehicle details page returns 404 for invalid slug', function () {
    $this->seed(VehicleCategorySeeder::class);

    $response = $this->get(route('vehicles.slug', ['slug' => 'non-existent-vehicle']));

    $response->assertNotFound();
});

test('booking page can be rendered with vehicle categories', function () {
    $this->seed(VehicleCategorySeeder::class);

    $response = $this->get(route('booking'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('customer/booking')
        ->has('vehicleCategories', 5)
    );
});
