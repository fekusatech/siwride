<?php

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('never serializes partner identity fields', function () {
    $partner = Partner::factory()->create(['nik' => '1234567890123456', 'nik_image' => 'partners/nik/private.jpg']);
    expect($partner->toArray())->not->toHaveKeys(['nik', 'nik_image']);
});

it('stores identity uploads privately', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->post(route('become-partner.store'), [
        'type' => 'guide', 'name' => 'Private Guide', 'email' => 'private@example.com', 'phone' => '081234567890',
        'password' => 'password123', 'password_confirmation' => 'password123',
        'nik_image' => UploadedFile::fake()->image('identity.jpg'),
    ])->assertSessionHasNoErrors();
    $partner = Partner::where('email', 'private@example.com')->firstOrFail();
    Storage::disk('local')->assertExists($partner->nik_image);
    Storage::disk('public')->assertMissing($partner->nik_image);
});

it('serves private identity only to admins and preserves legacy public files', function () {
    Storage::fake('local');
    Storage::fake('public');
    $partner = Partner::factory()->create(['nik_image' => 'partners/nik/private.jpg']);
    Storage::disk('local')->put($partner->nik_image, 'private identity');
    $this->get(route('admin.partners.identity', $partner))->assertRedirect();
    $this->actingAs(User::factory()->create(['role' => 'driver']))->get(route('admin.partners.identity', $partner))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.partners.identity', $partner))->assertSuccessful();
    Storage::disk('local')->delete($partner->nik_image);
    Storage::disk('public')->put($partner->nik_image, 'legacy identity');
    $this->get(route('admin.partners.identity', $partner))->assertNotFound();
    Storage::disk('public')->assertExists($partner->nik_image);
});
