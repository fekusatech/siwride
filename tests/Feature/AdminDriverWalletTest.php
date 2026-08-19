<?php

use App\Models\DriverWalletTransaction;
use App\Models\User;
use App\Services\DriverWalletService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->driver = User::factory()->create(['role' => 'driver']);
});

it('shows wallet detail with balance, negative flag, and transactions', function () {
    $walletService = app(DriverWalletService::class);
    $wallet = $walletService->getOrCreateWallet($this->driver);
    $walletService->credit($wallet, 100000, DriverWalletTransaction::TYPE_DP_PAYMENT, 'SVC-ABC', 'DP payment');

    $response = $this->actingAs($this->admin, 'web')
        ->get(route('admin.driver-wallets.show', $this->driver))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/DriverWallets/Show'));

    $props = $response->viewData('page')['props'];

    expect($props['wallet']['balance'])->toBe('100000.00')
        ->and($props['wallet']['is_negative'])->toBeFalse()
        ->and($props['transactions'])->toHaveCount(1)
        ->and($props['transactions'][0]['type'])->toBe(DriverWalletTransaction::TYPE_DP_PAYMENT);
});

it('flags negative balance in wallet detail', function () {
    $walletService = app(DriverWalletService::class);
    $wallet = $walletService->getOrCreateWallet($this->driver);
    $walletService->debit($wallet, 50000, DriverWalletTransaction::TYPE_PLATFORM_COMMISSION, 'SVC-NEG', 'Komisi', minimumBalance: '-200000.00');

    $response = $this->actingAs($this->admin, 'web')
        ->get(route('admin.driver-wallets.show', $this->driver))
        ->assertOk();

    $props = $response->viewData('page')['props'];

    expect($props['wallet']['is_negative'])->toBeTrue()
        ->and((string) $props['wallet']['balance'])->toBe('-50000.00');
});

it('records manual adjustment with admin as created_by', function () {
    $walletService = app(DriverWalletService::class);
    $wallet = $walletService->getOrCreateWallet($this->driver);

    $this->actingAs($this->admin, 'web')
        ->post(route('admin.driver-wallets.adjustment', $this->driver), [
            'amount' => 250000,
            'reason' => 'Koreksi komisi booking SVC-ABC',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $wallet->refresh();
    $transaction = $wallet->transactions()->where('type', DriverWalletTransaction::TYPE_ADMIN_ADJUSTMENT)->firstOrFail();

    expect((string) $wallet->balance)->toBe('250000.00')
        ->and($transaction->created_by)->toBe($this->admin->id)
        ->and($transaction->description)->toContain('Koreksi komisi booking SVC-ABC');
});

it('debts wallet on negative adjustment', function () {
    $walletService = app(DriverWalletService::class);
    $wallet = $walletService->getOrCreateWallet($this->driver);
    $walletService->credit($wallet, 100000, DriverWalletTransaction::TYPE_DP_PAYMENT, 'SVC-DEB', 'DP');

    $this->actingAs($this->admin, 'web')
        ->post(route('admin.driver-wallets.adjustment', $this->driver), [
            'amount' => -40000,
            'reason' => 'Koreksi kelebihan DP',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $wallet->refresh();

    expect((string) $wallet->balance)->toBe('60000.00');
});

it('requires a reason for adjustment', function () {
    $this->actingAs($this->admin, 'web')
        ->post(route('admin.driver-wallets.adjustment', $this->driver), [
            'amount' => 100000,
            'reason' => '',
        ])
        ->assertSessionHasErrors('reason');
});

it('rejects zero amount adjustment', function () {
    $this->actingAs($this->admin, 'web')
        ->post(route('admin.driver-wallets.adjustment', $this->driver), [
            'amount' => 0,
            'reason' => 'Tes nol',
        ])
        ->assertSessionHasErrors('amount');
});
