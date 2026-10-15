<?php

use App\Models\Partner;

it('renders the become-partner page', function () {
    $this->get('/become-partner')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Public/BecomePartner'));
});

it('creates a pending partner application', function () {
    $this->post('/become-partner', [
        'type' => 'travel',
        'name' => 'Bali Travel Co',
        'email' => 'apply@example.com',
        'phone' => '081234567890',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
        'description' => 'We run day tours.',
    ])->assertRedirect('/become-partner');

    $this->assertDatabaseHas('partners', [
        'email' => 'apply@example.com',
        'status' => Partner::STATUS_PENDING,
        'type' => Partner::TYPE_TRAVEL,
    ]);

    expect(Partner::first()->slug)->not->toBeEmpty();
});

it('rejects duplicate partner email', function () {
    Partner::factory()->create(['email' => 'taken@example.com']);

    $this->post('/become-partner', [
        'type' => 'guide',
        'name' => 'Another Guide',
        'email' => 'taken@example.com',
        'phone' => '089999999999',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
    ])->assertSessionHasErrors('email');

    expect(Partner::count())->toBe(1);
});

it('rejects invalid partner type', function () {
    $this->post('/become-partner', [
        'type' => 'chauffeur',
        'name' => 'Bad Type',
        'email' => 'badtype@example.com',
        'phone' => '088888888888',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
    ])->assertSessionHasErrors('type');
});
