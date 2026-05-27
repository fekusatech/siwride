<?php

use App\Models\User;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin@siwride.com',
    ]);
});

test('admin can view vehicle categories list', function () {
    VehicleCategory::create([
        'slug' => 'test-category',
        'title' => 'Test Category',
        'vehicle_type' => 'economy',
        'capacity' => '4 passengers',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.vehicle-categories.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/VehicleCategories/Index')
        ->has('categories.data', 1)
        ->where('categories.data.0.title', 'Test Category')
    );
});

test('admin can access create category page', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.vehicle-categories.create'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/VehicleCategories/Create')
    );
});

test('admin can store a new vehicle category', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('category.png');

    $response = $this->actingAs($this->admin)
        ->post(route('admin.vehicle-categories.store'), [
            'title' => 'New Category',
            'vehicle_type' => 'premium',
            'capacity' => '5 passengers',
            'examples' => 'A, B, C',
            'description' => 'Test Description',
            'image' => $file,
        ]);

    $response->assertRedirect(route('admin.vehicle-categories.index'));

    $this->assertDatabaseHas('vehicle_categories', [
        'title' => 'New Category',
        'slug' => 'new-category',
        'vehicle_type' => 'premium',
    ]);

    $category = VehicleCategory::where('slug', 'new-category')->first();
    expect($category->image)->not->toBeNull();
    Storage::disk('public')->assertExists($category->image);
});

test('admin can edit a vehicle category', function () {
    $category = VehicleCategory::create([
        'slug' => 'old-category',
        'title' => 'Old Category',
        'vehicle_type' => 'economy',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.vehicle-categories.edit', $category));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/VehicleCategories/Create')
        ->where('category.title', 'Old Category')
    );
});

test('admin can update a vehicle category', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('updated.png');

    $category = VehicleCategory::create([
        'slug' => 'old-category',
        'title' => 'Old Category',
        'vehicle_type' => 'economy',
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.vehicle-categories.update', $category), [
            'title' => 'Updated Category Title',
            'vehicle_type' => 'van',
            'capacity' => '10 passengers',
            'image' => $file,
        ]);

    $response->assertRedirect(route('admin.vehicle-categories.index'));

    $this->assertDatabaseHas('vehicle_categories', [
        'id' => $category->id,
        'title' => 'Updated Category Title',
        'slug' => 'updated-category-title',
        'vehicle_type' => 'van',
    ]);

    $category->refresh();
    Storage::disk('public')->assertExists($category->image);
});

test('admin can update a vehicle category without changing its image', function () {
    $category = VehicleCategory::create([
        'slug' => 'old-category',
        'title' => 'Old Category',
        'vehicle_type' => 'economy',
        'image' => 'vehicles/preserved.png',
    ]);

    $response = $this->actingAs($this->admin)
        ->put(route('admin.vehicle-categories.update', $category), [
            'title' => 'Updated Category Title',
            'vehicle_type' => 'van',
            'capacity' => '10 passengers',
        ]);

    $response->assertRedirect(route('admin.vehicle-categories.index'));

    $this->assertDatabaseHas('vehicle_categories', [
        'id' => $category->id,
        'title' => 'Updated Category Title',
        'image' => 'vehicles/preserved.png',
    ]);
});

test('admin can delete a vehicle category', function () {
    Storage::fake('public');

    $category = VehicleCategory::create([
        'slug' => 'to-delete',
        'title' => 'To Delete',
        'vehicle_type' => 'economy',
        'image' => 'vehicles/dummy.png',
    ]);

    Storage::disk('public')->put('vehicles/dummy.png', 'content');

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.vehicle-categories.destroy', $category));

    $response->assertRedirect(route('admin.vehicle-categories.index'));

    $this->assertDatabaseMissing('vehicle_categories', [
        'id' => $category->id,
    ]);

    Storage::disk('public')->assertMissing('vehicles/dummy.png');
});
