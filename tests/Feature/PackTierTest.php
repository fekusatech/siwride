<?php

use App\Models\Activity;
use App\Models\PackTier;
use App\Models\User;
use App\Models\Voucher;
use App\Services\PackTierService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->activity = Activity::factory()->create([
        'price_per_pax' => 150000,
        'min_pax' => 1,
    ]);
});

it('computes percent tier price per pax', function () {
    $tier = PackTier::factory()->create([
        'min_pax' => 4,
        'max_pax' => 5,
        'discount_type' => PackTier::TYPE_PERCENT,
        'discount_value' => 10,
    ]);

    expect($tier->pricePerPax(150000))->toBe(135000.0)
        ->and($tier->discountLabel())->toBe('10% off');
});

it('computes flat tier price per pax', function () {
    $tier = PackTier::factory()->create([
        'min_pax' => 6,
        'max_pax' => null,
        'discount_type' => PackTier::TYPE_FLAT,
        'discount_value' => 30000,
    ]);

    expect($tier->pricePerPax(150000))->toBe(120000.0)
        ->and($tier->discountLabel())->toBe('Rp 30.000 off / pax');
});

it('finds the correct tier by pax count', function () {
    PackTier::factory()->create(['min_pax' => 4, 'max_pax' => 5, 'discount_value' => 10]);
    PackTier::factory()->create(['min_pax' => 6, 'max_pax' => null, 'discount_value' => 15]);

    $service = app(PackTierService::class);

    expect($service->tierForPax(2))->toBeNull()
        ->and((float) $service->tierForPax(4)?->discount_value)->toBe(10.0)
        ->and((float) $service->tierForPax(5)?->discount_value)->toBe(10.0)
        ->and((float) $service->tierForPax(6)?->discount_value)->toBe(15.0)
        ->and((float) $service->tierForPax(10)?->discount_value)->toBe(15.0);
});

it('shows admin pack tiers index page', function () {
    PackTier::factory()->create(['label' => 'Group 4-5']);

    $this->actingAs($this->admin, 'web')
        ->get(route('admin.pack-tiers.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/PackTiers/Index'))
        ->assertInertia(fn ($page) => $page->has('tiers.data', 1));
});

it('creates a pack tier via admin store', function () {
    $this->actingAs($this->admin, 'web')
        ->post(route('admin.pack-tiers.store'), [
            'label' => 'Group 4-5',
            'min_pax' => 4,
            'max_pax' => 5,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(PackTier::where('label', 'Group 4-5')->exists())->toBeTrue();
});

it('updates a pack tier', function () {
    $tier = PackTier::factory()->create(['label' => 'Old']);

    $this->actingAs($this->admin, 'web')
        ->patch(route('admin.pack-tiers.update', $tier), [
            'label' => 'Updated',
            'min_pax' => $tier->min_pax,
            'max_pax' => $tier->max_pax,
            'discount_type' => $tier->discount_type,
            'discount_value' => 20,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $tier->refresh();
    expect($tier->label)->toBe('Updated')
        ->and((float) $tier->discount_value)->toBe(20.0);
});

it('deletes a pack tier', function () {
    $tier = PackTier::factory()->create();

    $this->actingAs($this->admin, 'web')
        ->delete(route('admin.pack-tiers.destroy', $tier))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(PackTier::find($tier->id))->toBeNull();
});

it('passes pack tiers to activity detail page', function () {
    PackTier::factory()->create(['label' => 'Group 4-5']);

    $this->get(route('activities.show', $this->activity->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('packTiers', 1));
});

it('validate-voucher endpoint returns tier-adjusted subtotal', function () {
    PackTier::factory()->create([
        'label' => 'Group 4-5',
        'min_pax' => 4,
        'max_pax' => 5,
        'discount_type' => PackTier::TYPE_PERCENT,
        'discount_value' => 10,
    ]);

    $voucher = Voucher::factory()->create(['type' => 'percent', 'value' => 5]);

    $response = $this->postJson(route('activities.validate-voucher', $this->activity->slug), [
        'code' => $voucher->code,
        'pax' => 4,
        'email' => 'test@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('price_per_pax', 135000)
        ->assertJsonPath('subtotal', 540000)
        ->assertJsonPath('tier_label', 'Group 4-5');
});
