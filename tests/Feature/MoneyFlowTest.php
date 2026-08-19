<?php

use App\Mail\DriverServiceAssignedMail;
use App\Mail\DriverServiceBookingCompletedMail;
use App\Mail\DriverServiceBookingCustomerCompletedMail;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\DriverWallet;
use App\Models\DriverWalletTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Services\DriverWalletService;
use Illuminate\Support\Facades\Mail;

it('assigns and credits a paid service booking only once', function () {
    Mail::fake();

    $driver = User::factory()->create(['role' => 'driver']);
    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $driver->id])->id,
        'total_amount' => 333333,
        'dp_amount' => 99999.90,
    ]);

    $payload = [
        'status' => 'PAID',
        'external_id' => $booking->booking_code.'_12345',
    ];

    $this->postJson(route('webhooks.xendit.invoice'), $payload)->assertSuccessful();
    $this->postJson(route('webhooks.xendit.invoice'), $payload)->assertSuccessful();

    $booking->refresh();
    $wallet = DriverWallet::where('user_id', $driver->id)->firstOrFail();

    expect($booking->assigned_driver_id)->toBe($driver->id)
        ->and($booking->assignment_status)->toBe('assigned')
        ->and($wallet->transactions()->where('type', DriverWalletTransaction::TYPE_DP_PAYMENT)->count())->toBe(1)
        ->and((string) $wallet->balance)->toBe('99999.90');

    Mail::assertSent(DriverServiceAssignedMail::class, 1);
});

it('completes a paid booking, debits commission, and emails both parties', function () {
    Mail::fake();
    Setting::setValue('platform_commission_percent', '10');

    $driver = User::factory()->create(['role' => 'driver']);
    $booking = DriverServiceBooking::factory()->create([
        'driver_service_id' => DriverService::factory()->create(['driver_id' => $driver->id])->id,
        'assigned_driver_id' => $driver->id,
        'assignment_status' => 'accepted',
        'status' => DriverServiceBooking::STATUS_CONFIRMED,
        'payment_status' => DriverServiceBooking::PAYMENT_PAID,
    ]);

    $this->actingAs($driver, 'driver')
        ->post(route('driver.service-bookings.cash-received', $booking->booking_code))
        ->assertRedirect();

    $booking->refresh();
    $wallet = DriverWallet::where('user_id', $driver->id)->firstOrFail();

    expect($booking->status)->toBe(DriverServiceBooking::STATUS_COMPLETED)
        ->and($booking->cash_confirmed_at)->not->toBeNull()
        ->and((string) $wallet->transactions()->where('type', DriverWalletTransaction::TYPE_PLATFORM_COMMISSION)->value('amount'))->toBe('-20000.00');

    Mail::assertSent(DriverServiceBookingCompletedMail::class, 1);
    Mail::assertSent(DriverServiceBookingCustomerCompletedMail::class, 1);
});

it('keeps wallet balance equal to transaction sum after money flow', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $wallet = app(DriverWalletService::class)->getOrCreateWallet($driver);

    app(DriverWalletService::class)->credit($wallet, 333333, DriverWalletTransaction::TYPE_DP_PAYMENT, 'FLOW-1', 'DP');
    app(DriverWalletService::class)->debit($wallet, 20000, DriverWalletTransaction::TYPE_PLATFORM_COMMISSION, 'FLOW-1', 'Commission', minimumBalance: '-20000.00');

    $wallet->refresh();

    expect((float) $wallet->balance)->toBe((float) $wallet->transactions()->sum('amount'));
});
