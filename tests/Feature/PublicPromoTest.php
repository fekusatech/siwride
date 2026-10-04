<?php

use App\Models\Voucher;
use Inertia\Testing\AssertableInertia as Assert;

it('lists only currently available vouchers on the public promo page', function () {
    $available = Voucher::factory()->create();
    Voucher::factory()->create(['is_active' => false]);
    Voucher::factory()->create(['valid_until' => now()->subDay()]);
    Voucher::factory()->create(['valid_from' => now()->addDay()]);
    Voucher::factory()->create(['usage_limit' => 5, 'used_count' => 5]);

    $this->get('/promos')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customer/promos')
            ->has('promos', 1)
            ->where('promos.0.code', $available->code));
});

it('shows the promo article page for an available voucher', function () {
    $voucher = Voucher::factory()->create(['title' => 'Diskon Akhir Tahun', 'description' => 'Syarat berlaku.']);

    $this->get("/promos/{$voucher->code}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customer/promo-detail')
            ->where('promo.title', 'Diskon Akhir Tahun')
            ->where('promo.description', 'Syarat berlaku.'));
});

it('returns 404 for expired or inactive promo article pages', function () {
    $expired = Voucher::factory()->create(['valid_until' => now()->subDay()]);
    $inactive = Voucher::factory()->create(['is_active' => false]);

    $this->get("/promos/{$expired->code}")->assertNotFound();
    $this->get("/promos/{$inactive->code}")->assertNotFound();
});
