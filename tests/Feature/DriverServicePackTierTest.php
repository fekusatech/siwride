<?php

use App\Models\DriverService;
use App\Models\PackTier;

it('exposes driver service pack tiers on the booking page', function () {
    $service = DriverService::factory()->create([
        'slug' => 'pack-tier-service',
    ]);

    PackTier::create([
        'driver_service_id' => $service->id,
        'label' => 'Group 4+',
        'min_pax' => 4,
        'max_pax' => null,
        'discount_type' => PackTier::TYPE_PERCENT,
        'discount_value' => 10,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->get(route('driver-services.show', $service->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customer/service-detail')
            ->where('packTiers.0.driver_service_id', $service->id)
            ->where('packTiers.0.discount_label', '10% off'));
});
