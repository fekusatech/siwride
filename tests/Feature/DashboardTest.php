<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('admin.login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('drivers cannot access admin pages', function (string $routeName) {
    $user = User::factory()->create(['role' => 'driver']);

    $this->actingAs($user)->get(route($routeName))->assertForbidden();
})->with(['dashboard', 'profile.edit', 'security.edit', 'admin.orders.import']);
