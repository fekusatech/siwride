<?php

use App\Models\DriverService;

it('shows catalog items when the type query is empty', function () {
    $service = DriverService::factory()->create([
        'title' => 'Catalog Driver Service',
    ]);

    $this->get('/catalog?search=&sort=newest&type=')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customer/catalog')
            ->where('filters.type', '')
            ->where('items.0.title', $service->title));
});
