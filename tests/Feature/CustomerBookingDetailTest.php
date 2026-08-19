<?php

use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;

beforeEach(function () {
    $this->driver = User::factory()->create(['role' => 'driver', 'image' => 'https://example.com/driver.jpg']);
    $this->booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $this->driver->id])->id,
        'assigned_driver_id' => $this->driver->id,
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
    ]);
});

it('shows assigned driver only after booking is confirmed', function () {
    $response = $this->get(route('driver-services.booking.detail', $this->booking->booking_code))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('customer/service-booking-detail'));

    $props = $response->viewData('page')['props'];

    expect($props['assigned_driver']['id'])->toBe($this->driver->id)
        ->and($props['assigned_driver']['name'])->toBe($this->driver->name)
        ->and($props['assigned_driver']['image'])->toBe('https://example.com/driver.jpg');
});

it('does not show driver before confirmation', function () {
    $this->booking->update([
        'status' => DriverServiceBooking::STATUS_PENDING,
        'payment_status' => DriverServiceBooking::PAYMENT_PENDING,
        'assigned_driver_id' => null,
    ]);

    $response = $this->get(route('driver-services.booking.detail', $this->booking->booking_code))
        ->assertOk();

    $props = $response->viewData('page')['props'];

    expect($props['assigned_driver'])->toBeNull();
});

it('reflects the new driver after reassignment approval', function () {
    $newDriver = User::factory()->create(['role' => 'driver', 'image' => null]);

    $this->booking->update([
        'assigned_driver_id' => $newDriver->id,
        'assignment_status' => 'reassigned',
    ]);

    $response = $this->get(route('driver-services.booking.detail', $this->booking->booking_code))
        ->assertOk();

    $props = $response->viewData('page')['props'];

    expect($props['assigned_driver']['id'])->toBe($newDriver->id)
        ->and($props['assigned_driver']['image'])->toBeNull();
});

it('404s for unknown booking code', function () {
    $this->get(route('driver-services.booking.detail', 'SVC-NOPE'))
        ->assertNotFound();
});
