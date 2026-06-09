<?php

test('booking services page is accessible', function () {
    $response = $this->get('/booking');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('customer/booking-services'));
});

test('airport transfer booking page is accessible', function () {
    $response = $this->get('/booking/airport-transfer');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('customer/booking'));
});

test('tour booking page is accessible', function () {
    $response = $this->get('/booking/tour');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('customer/booking-tour'));
});

test('sharing ride booking page is accessible', function () {
    $response = $this->get('/booking/sharing-ride');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('RideSharing/Index'));
});

test('hourly service booking page is accessible', function () {
    $response = $this->get('/booking/hourly');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('customer/booking-hourly'));
});
