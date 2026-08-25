<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

it('requires driver authentication for the profile page', function () {
    $this->get(route('driver.profile'))->assertRedirect(route('driver.login'));
});

it('shows the authenticated driver profile', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $this->actingAs($driver, 'driver')
        ->get(route('driver.profile'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Driver/Profile')
            ->where('user.id', $driver->id)
            ->where('user.email', $driver->email));
});

it('updates driver profile information and photo', function () {
    Storage::fake('public');
    $driver = User::factory()->create(['role' => 'driver']);
    $photo = UploadedFile::fake()->image('driver.jpg');

    $this->actingAs($driver, 'driver')
        ->put(route('driver.profile.update'), [
            'firstname' => 'Updated',
            'lastname' => 'Driver',
            'email' => 'updated@example.com',
            'phone' => '+628123456789',
            'image' => $photo,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $driver->refresh();

    expect($driver->firstname)->toBe('Updated')
        ->and($driver->email)->toBe('updated@example.com')
        ->and($driver->image)->toStartWith('profiles/');

    Storage::disk('public')->assertExists($driver->image);
});

it('validates driver profile information', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $this->actingAs($driver, 'driver')
        ->put(route('driver.profile.update'), [
            'firstname' => '',
            'lastname' => '',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors(['firstname', 'lastname', 'email']);
});

it('allows a driver to change their password', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $this->actingAs($driver, 'driver')
        ->put(route('driver.profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('new-password-123', $driver->refresh()->password))->toBeTrue();
});

it('rejects an incorrect current driver password', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $this->actingAs($driver, 'driver')
        ->put(route('driver.profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertSessionHasErrors('current_password');
});

it('keeps the existing photo when updating profile info without a new image', function () {
    Storage::fake('public');
    $driver = User::factory()->create(['role' => 'driver', 'image' => 'profiles/existing.jpg']);
    Storage::disk('public')->put('profiles/existing.jpg', 'fake-contents');

    $this->actingAs($driver, 'driver')
        ->put(route('driver.profile.update'), [
            'firstname' => 'Updated',
            'lastname' => 'Driver',
            'email' => $driver->email,
            'phone' => '+628123456789',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($driver->refresh()->image)->toBe('profiles/existing.jpg');
    Storage::disk('public')->assertExists('profiles/existing.jpg');
});
