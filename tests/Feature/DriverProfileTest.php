<?php

use App\Models\Activity;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;

function driverUser(): User
{
    return User::factory()->create(['role' => 'driver']);
}

it('shows public driver profile with approved services', function () {
    $driver = driverUser();

    DriverService::factory()->create([
        'driver_id' => $driver->id,
        'title' => 'Ubud Day Tour',
        'status' => DriverService::STATUS_APPROVED,
    ]);
    DriverService::factory()->create([
        'driver_id' => $driver->id,
        'title' => 'Draft Service',
        'status' => DriverService::STATUS_PENDING,
    ]);

    $this->get(route('drivers.profile', $driver))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customer/driver-profile')
            ->where('driver.id', $driver->id)
            ->where('driver.name', $driver->name)
            ->has('services', 1)
            ->where('services.0.title', 'Ubud Day Tour'));
});

it('counts completed bookings on driver profile', function () {
    $driver = driverUser();
    $service = DriverService::factory()->create(['driver_id' => $driver->id]);

    DriverServiceBooking::factory()->create([
        'driver_service_id' => $service->id,
        'status' => DriverServiceBooking::STATUS_COMPLETED,
    ]);
    DriverServiceBooking::factory()->create([
        'driver_service_id' => $service->id,
        'status' => DriverServiceBooking::STATUS_PENDING,
    ]);

    $this->get(route('drivers.profile', $driver))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('driver.completed_bookings', 1));
});

it('404s for non-driver users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->get(route('drivers.profile', $admin))->assertNotFound();
});

it('merges activities and driver services in homepage services', function () {
    $activity = Activity::factory()->create(['is_active' => true, 'sort_order' => 1]);
    $driver = driverUser();
    DriverService::factory()->create([
        'driver_id' => $driver->id,
        'title' => 'Driver Tour',
        'status' => DriverService::STATUS_APPROVED,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('services', 2)
            ->where('services.0.type', 'activity')
            ->where('services.0.slug', $activity->slug)
            ->where('services.1.type', 'service')
            ->where('services.1.title', 'Driver Tour')
            ->where('services.1.driver_id', $driver->id));
});
