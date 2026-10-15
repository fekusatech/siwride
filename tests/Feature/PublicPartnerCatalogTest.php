<?php

use App\Models\Partner;

it('lists only active partners in the public catalog', function () {
    $active = Partner::factory()->active()->create(['name' => 'Active Travel']);
    Partner::factory()->create(['name' => 'Pending Travel']);
    Partner::factory()->active()->create(['name' => 'Rejected Travel', 'status' => Partner::STATUS_REJECTED]);

    $this->get('/partners')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/Partners')
            ->has('partners', 1)
            ->where('partners.0.name', $active->name));
});

it('filters the catalog by partner type', function () {
    Partner::factory()->active()->travel()->create();
    Partner::factory()->active()->guide()->create(['name' => 'Guide One']);

    $this->get('/partners?type=guide')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('partners', 1)
            ->where('partners.0.name', 'Guide One'));
});

it('shows an active partner profile by slug', function () {
    $partner = Partner::factory()->active()->create(['name' => 'Shown Partner']);

    $this->get("/partners/{$partner->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Public/PartnerShow')
            ->where('partner.name', 'Shown Partner'));
});

it('returns 404 for a non-active partner profile', function () {
    $partner = Partner::factory()->create(['status' => Partner::STATUS_PENDING]);

    $this->get("/partners/{$partner->slug}")->assertNotFound();
});
