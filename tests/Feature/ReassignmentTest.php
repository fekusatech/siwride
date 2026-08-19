<?php

use App\Mail\DriverServiceReassignApprovedMail;
use App\Mail\DriverServiceReassignRejectedMail;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\DriverServiceReassignment;
use App\Models\DriverWallet;
use App\Models\DriverWalletTransaction;
use App\Models\User;
use App\Services\DriverWalletService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

it('transfers DP from old driver to new driver atomically on approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);

    $walletService = app(DriverWalletService::class);
    $fromWallet = $walletService->getOrCreateWallet($fromDriver);
    $walletService->credit($fromWallet, 100000, DriverWalletTransaction::TYPE_DP_PAYMENT, 'SVC-ABC', 'DP');

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $fromDriver->id])->id,
        'assigned_driver_id' => $fromDriver->id,
        'assignment_status' => 'reassign_requested',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
        'dp_amount' => 100000,
        'total_amount' => 333333,
        'remaining_cash' => 233333,
    ]);

    $reassignment = DriverServiceReassignment::create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'reason' => 'Kendaraan bermasalah',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertRedirect()
        ->assertSessionHas('success');

    $fromWallet->refresh();
    $toWallet = DriverWallet::where('user_id', $toDriver->id)->firstOrFail();
    $booking->refresh();
    $reassignment->refresh();

    expect((string) $fromWallet->balance)->toBe('0.00')
        ->and((string) $toWallet->balance)->toBe('100000.00')
        ->and($booking->assigned_driver_id)->toBe($toDriver->id)
        ->and($booking->assignment_status)->toBe('reassigned')
        ->and($reassignment->status)->toBe('approved')
        ->and($reassignment->decided_by)->toBe($admin->id)
        ->and($reassignment->decided_at)->not->toBeNull();

    Mail::assertSent(DriverServiceReassignRejectedMail::class, 1);
    Mail::assertSent(DriverServiceReassignApprovedMail::class, 1);
});

it('does not transfer DP when approving without DP amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $fromDriver->id])->id,
        'assigned_driver_id' => $fromDriver->id,
        'assignment_status' => 'reassign_requested',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
        'dp_amount' => 0,
    ]);

    $reassignment = DriverServiceReassignment::create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'reason' => 'Tes tanpa DP',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertRedirect();

    expect(DriverWallet::where('user_id', $toDriver->id)->count())->toBe(0)
        ->and($booking->fresh()->assigned_driver_id)->toBe($toDriver->id);
});

it('rejects reassignment without moving DP and restores assignment status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);

    $walletService = app(DriverWalletService::class);
    $fromWallet = $walletService->getOrCreateWallet($fromDriver);
    $walletService->credit($fromWallet, 100000, DriverWalletTransaction::TYPE_DP_PAYMENT, 'SVC-XYZ', 'DP');

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $fromDriver->id])->id,
        'assigned_driver_id' => $fromDriver->id,
        'assignment_status' => 'reassign_requested',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
        'dp_amount' => 100000,
    ]);

    $reassignment = DriverServiceReassignment::create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'reason' => 'Jarak terlalu jauh',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.service-reassignments.reject', $reassignment), [
            'rejection_reason' => 'Driver baru tidak bersedia',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fromWallet->refresh();
    $booking->refresh();
    $reassignment->refresh();

    expect((string) $fromWallet->balance)->toBe('100000.00')
        ->and($booking->assigned_driver_id)->toBe($fromDriver->id)
        ->and($booking->assignment_status)->toBe('assigned')
        ->and($reassignment->status)->toBe('rejected')
        ->and($reassignment->rejection_reason)->toBe('Driver baru tidak bersedia');

    Mail::assertSent(DriverServiceReassignRejectedMail::class, 1);
});

it('keeps wallet invariant across reassignment approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);

    $walletService = app(DriverWalletService::class);
    $fromWallet = $walletService->getOrCreateWallet($fromDriver);
    $walletService->credit($fromWallet, 250000, DriverWalletTransaction::TYPE_DP_PAYMENT, 'SVC-INV', 'DP');

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $fromDriver->id])->id,
        'assigned_driver_id' => $fromDriver->id,
        'assignment_status' => 'reassign_requested',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
        'dp_amount' => 100000,
        'total_amount' => 333333,
    ]);

    $reassignment = DriverServiceReassignment::create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'reason' => 'Uji invariant',
        'status' => 'pending',
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertRedirect();

    $fromWallet->refresh();
    $toWallet = DriverWallet::where('user_id', $toDriver->id)->firstOrFail();

    expect((float) $fromWallet->balance)->toBe((float) $fromWallet->transactions()->sum('amount'))
        ->and((float) $toWallet->balance)->toBe((float) $toWallet->transactions()->sum('amount'))
        ->and((float) $fromWallet->balance + (float) $toWallet->balance)->toBe(250000.0);
});

it('rejects approving a reassignment that is not pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $fromDriver = User::factory()->create(['role' => 'driver']);
    $toDriver = User::factory()->create(['role' => 'driver']);

    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $fromDriver->id])->id,
        'assigned_driver_id' => $fromDriver->id,
        'assignment_status' => 'reassigned',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
        'dp_amount' => 100000,
    ]);

    $reassignment = DriverServiceReassignment::create([
        'booking_id' => $booking->id,
        'from_driver_id' => $fromDriver->id,
        'to_driver_id' => $toDriver->id,
        'reason' => 'Sudah diproses',
        'status' => 'approved',
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.service-reassignments.approve', $reassignment))
        ->assertStatus(422);
});
