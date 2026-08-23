<?php

use App\Models\Activity;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;

function driverUser(): User
{
    return User::factory()->create(['role' => 'driver']);
}

it('shows public driver profile by slug with approved services', function () {
    $driver = driverUser();
    $slug = $driver->ensureDriverSlug();

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

    $this->get(route('drivers.profile', ['slug' => $slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customer/driver-profile')
            ->where('driver.id', $driver->id)
            ->where('driver.name', $driver->name)
            ->where('driver.slug', $slug)
            ->has('services', 1)
            ->where('services.0.title', 'Ubud Day Tour'));
});

it('generates unique slugs for drivers with the same name', function () {
    $first = driverUser();
    $second = driverUser();
    $second->update(['firstname' => $first->firstname, 'lastname' => $first->lastname]);

    expect($first->ensureDriverSlug())->toBe(Str::slug($first->name))
        ->and($second->ensureDriverSlug())->toBe(Str::slug($second->name).'-2');
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

    $this->get(route('drivers.profile', ['slug' => $driver->ensureDriverSlug()]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('driver.completed_bookings', 1));
});

it('404s for unknown slugs and non-driver users', function () {
    $this->get('/driver/does-not-exist')->assertNotFound();

    $admin = User::factory()->create(['role' => 'admin']);
    $admin->update(['slug' => 'admin-guy']);

    $this->get('/driver/admin-guy')->assertNotFound();
});

it('merges activities and driver services in homepage services', function () {
    $activity = Activity::factory()->create(['is_active' => true, 'sort_order' => 1]);
    $driver = driverUser();
    DriverService::factory()->create([
        'driver_id' => $driver->id,
        'title' => 'Driver Tour',
        'status' => DriverService::STATUS_APPROVED,
    ]);
    $slug = $driver->ensureDriverSlug();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('services', 2)
            ->where('services.0.type', 'activity')
            ->where('services.0.slug', $activity->slug)
            ->where('services.1.type', 'service')
            ->where('services.1.title', 'Driver Tour')
            ->where('services.1.driver_slug', $slug));
});
