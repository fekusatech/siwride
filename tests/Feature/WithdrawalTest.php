<?php

use App\Mail\DriverWithdrawalStatusMail;
use App\Models\DriverWallet;
use App\Models\DriverWalletTransaction;
use App\Models\DriverWithdrawal;
use App\Models\Setting;
use App\Models\User;
use App\Services\DriverWithdrawalService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    Setting::setValue('min_withdrawal_amount', '100000');
});

it('creates a withdrawal request within available balance', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $wallet = DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $this->actingAs($driver, 'driver')
        ->post(route('driver.withdrawals.store'), [
            'amount' => 200000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $withdrawal = DriverWithdrawal::firstOrFail();

    expect($withdrawal->user_id)->toBe($driver->id)
        ->and($withdrawal->amount)->toBe('200000.00')
        ->and($withdrawal->status)->toBe(DriverWithdrawal::STATUS_PENDING);
});

it('rejects withdrawal above available balance', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $this->actingAs($driver, 'driver')
        ->post(route('driver.withdrawals.store'), [
            'amount' => 600000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi',
        ])
        ->assertSessionHasErrors('amount');
});

it('rejects withdrawal below minimum amount', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $this->actingAs($driver, 'driver')
        ->post(route('driver.withdrawals.store'), [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi',
        ])
        ->assertSessionHasErrors('amount');
});

it('locks balance while pending withdrawal exists (no double spend)', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'amount' => 400000,
        'status' => DriverWithdrawal::STATUS_PENDING,
    ]);

    $available = app(DriverWithdrawalService::class)->availableBalance($driver);

    expect($available)->toBe('100000.00');

    $this->actingAs($driver, 'driver')
        ->post(route('driver.withdrawals.store'), [
            'amount' => 150000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi',
        ])
        ->assertSessionHasErrors('amount');
});

it('marks paid withdrawal by debiting wallet exactly once', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $admin = User::factory()->create(['role' => 'admin']);
    $wallet = DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $withdrawal = DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'amount' => 200000,
        'status' => DriverWithdrawal::STATUS_APPROVED,
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.mark-paid', $withdrawal))
        ->assertRedirect()
        ->assertSessionHas('success');

    $wallet->refresh();
    $withdrawal->refresh();

    expect($withdrawal->status)->toBe(DriverWithdrawal::STATUS_PAID)
        ->and($withdrawal->paid_at)->not->toBeNull()
        ->and((string) $wallet->balance)->toBe('300000.00')
        ->and($wallet->transactions()->where('type', DriverWalletTransaction::TYPE_WITHDRAWAL)->count())->toBe(1);

    // Second call must not debit again
    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.mark-paid', $withdrawal))
        ->assertRedirect();

    $wallet->refresh();

    expect((string) $wallet->balance)->toBe('300000.00')
        ->and($wallet->transactions()->where('type', DriverWalletTransaction::TYPE_WITHDRAWAL)->count())->toBe(1);
});

it('sends status email to driver on approve, reject, and mark-paid', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $admin = User::factory()->create(['role' => 'admin']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $withdrawal = DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'amount' => 200000,
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.approve', $withdrawal))
        ->assertRedirect();

    Mail::assertSent(DriverWithdrawalStatusMail::class, 1);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.reject', $withdrawal), ['rejection_reason' => 'Nomor rekening salah'])
        ->assertRedirect()
        ->assertSessionHasErrors('status');

    $withdrawal->refresh();

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.mark-paid', $withdrawal))
        ->assertRedirect();

    Mail::assertSent(DriverWithdrawalStatusMail::class, 2);
});

it('rejects approved withdrawals only after pending', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $admin = User::factory()->create(['role' => 'admin']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $withdrawal = DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'status' => DriverWithdrawal::STATUS_APPROVED,
    ]);

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.reject', $withdrawal), ['rejection_reason' => 'Test'])
        ->assertSessionHasErrors('status');
});

it('releases locked balance when withdrawal is rejected', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $admin = User::factory()->create(['role' => 'admin']);
    DriverWallet::factory()->create(['user_id' => $driver->id, 'balance' => 500000]);

    $withdrawal = DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'amount' => 200000,
    ]);

    $service = app(DriverWithdrawalService::class);

    expect($service->availableBalance($driver))->toBe('300000.00');

    $this->actingAs($admin, 'web')
        ->patch(route('admin.withdrawals.reject', $withdrawal), ['rejection_reason' => 'Rekening tidak valid'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($service->availableBalance($driver))->toBe('500000.00');

    $this->actingAs($driver, 'driver')
        ->post(route('driver.withdrawals.store'), [
            'amount' => 400000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
});

it('shows only last four digits of account number in admin list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $driver = User::factory()->create(['role' => 'driver']);

    DriverWithdrawal::factory()->create([
        'user_id' => $driver->id,
        'bank_account_number' => '1234567890',
    ]);

    $response = $this->actingAs($admin, 'web')
        ->get(route('admin.withdrawals.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/Withdrawals/Index'));

    $first = $response->viewData('page')['props']['withdrawals']['data'][0] ?? null;

    expect($first['bank_account_last_four'])->toBe('7890');
});
