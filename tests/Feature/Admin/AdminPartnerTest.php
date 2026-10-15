<?php

use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('lists partners for admin', function () {
    Partner::factory()->active()->create();

    $this->actingAs($this->admin)
        ->get('/admin/partners')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Partners/Index'));
});

it('approves a pending partner', function () {
    $partner = Partner::factory()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/partners/{$partner->id}/approve")
        ->assertRedirect();

    expect($partner->fresh()->status)->toBe(Partner::STATUS_ACTIVE)
        ->and($partner->fresh()->approved_at)->not->toBeNull();
});

it('rejects a pending partner', function () {
    $partner = Partner::factory()->create(['approved_at' => now()]);

    $this->actingAs($this->admin)
        ->patch("/admin/partners/{$partner->id}/reject")
        ->assertRedirect();

    expect($partner->fresh()->status)->toBe(Partner::STATUS_REJECTED)
        ->and($partner->fresh()->approved_at)->toBeNull();
});

it('blocks guests from admin partner routes', function () {
    $partner = Partner::factory()->create();

    $this->get('/admin/partners')->assertRedirect('/login-admin');
    $this->patch("/admin/partners/{$partner->id}/approve")->assertRedirect('/login-admin');
});

it('sends a whatsapp approval message with the catalog link', function () {
    Http::fake(['*' => Http::response('', 200)]);
    $partner = Partner::factory()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/partners/{$partner->id}/approve")
        ->assertRedirect();

    Http::assertSent(function ($request) use ($partner) {
        return str_contains($request['to'], '62')
            && str_contains($request['text'], 'DISETUJUI')
            && str_contains($request['text'], $partner->slug);
    });
});

it('sends a whatsapp rejection message', function () {
    Http::fake(['*' => Http::response('', 200)]);
    $partner = Partner::factory()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/partners/{$partner->id}/reject")
        ->assertRedirect();

    Http::assertSent(fn ($request) => str_contains($request['text'], 'belum dapat melanjutkan'));
});

it('still approves when the whatsapp api is down', function () {
    Http::fake(['*' => Http::response('', 500)]);
    $partner = Partner::factory()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/partners/{$partner->id}/approve")
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($partner->fresh()->status)->toBe(Partner::STATUS_ACTIVE);
});
