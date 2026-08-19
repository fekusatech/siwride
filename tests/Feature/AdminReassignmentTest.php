<?php

use App\Mail\DriverServiceReassignApprovedMail;
use App\Mail\DriverServiceReassignRejectedMail;
use App\Models\DriverServiceBooking;
use App\Models\DriverServiceReassignment;
use App\Models\DriverWallet;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('shows the reassignments index page', function () {
    $reassignment = DriverServiceReassignment::factory()->create();

    $this->actingAs($this->admin, 'web')
        ->get(route('admin.service-reassignments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Reassignments/Index'))
        ->assertInertia(fn ($page) => $page->has('reassignments.data', 1));
});

it('shows the reassignment detail page', function () {
    $reassignment = DriverServiceReassignment::factory()->create();

    $this->actingAs($this->admin, 'web')
        ->get(route('admin.service-reassignments.show', $reassignment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Reassignments/Show'));
});

it('filters reassignments by status', function () {
    DriverServiceReassignment::factory()->create(['status' => 'pending']);
    DriverServiceReassignment::factory()->create(['status' => 'approved']);

    $pending = $this->actingAs($this->admin, 'web')
        ->get(route('admin.service-reassignments.index', ['status' => 'pending']))
        ->assertOk()
        ->inertiaProps();

    expect($pending['reassignments']['data'])->toHaveCount(1)
        ->and($pending['reassignments']['data'][0]['status'])->toBe('pending');
});

it('approves a pending reassignment and transfers DP between wallets', function () {
    $booking = DriverServiceBooking::factory()->create(['dp_amount' => 60000]);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);
    $fromWallet = DriverWallet::factory()->create(['user_id' => $fromDriver->id, 'balance' => 60000]);

    $reassignment = DriverServiceReassignment::factory()->create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertRedirect()
        ->assertSessionHas('success');

    $reassignment->refresh();
    $booking->refresh();
    $fromWallet->refresh();
    $toWallet = DriverWallet::where('user_id', $toDriver->id)->firstOrFail();

    expect($reassignment->status)->toBe('approved')
        ->and($reassignment->decided_by)->toBe($this->admin->id)
        ->and((string) $booking->assigned_driver_id)->toBe((string) $toDriver->id)
        ->and((float) $fromWallet->balance)->toBe(0.0)
        ->and((float) $toWallet->balance)->toBe(60000.0);

    Mail::assertSent(DriverServiceReassignApprovedMail::class, 1);
    Mail::assertSent(DriverServiceReassignRejectedMail::class, 1);
});

it('rejects a pending reassignment with reason and restores assignment status', function () {
    $booking = DriverServiceBooking::factory()->create();
    $reassignment = DriverServiceReassignment::factory()->create([
        'booking_id' => $booking->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin, 'web')
        ->patch(route('admin.service-reassignments.reject', $reassignment), [
            'rejection_reason' => 'Driver tujuan tidak tersedia',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $reassignment->refresh();
    $booking->refresh();

    expect($reassignment->status)->toBe('rejected')
        ->and($reassignment->rejection_reason)->toBe('Driver tujuan tidak tersedia')
        ->and($reassignment->decided_by)->toBe($this->admin->id)
        ->and($booking->assignment_status)->toBe('assigned');

    Mail::assertSent(DriverServiceReassignRejectedMail::class, 1);
});

it('blocks approve of a non-pending reassignment', function () {
    $reassignment = DriverServiceReassignment::factory()->create(['status' => 'approved']);

    $this->actingAs($this->admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertStatus(422);
});
