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
