<?php

use App\Models\User;
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

it('sanitizes promo description html on save', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post('/admin/promos', [
        'code' => 'SAFE-1234',
        'type' => 'percent',
        'value' => 10,
        'description' => '<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a><a href="https://siwride.com" onclick="x()">ok</a>',
    ]);

    $description = Voucher::where('code', 'SAFE-1234')->value('description');

    expect($description)->not->toContain('script')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->toContain('href="https://siwride.com"');
});
