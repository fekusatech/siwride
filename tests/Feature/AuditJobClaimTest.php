<?php

use App\Models\Driver;
use App\Models\JobClaim;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function auditClaimFixture(?User $owner = null): array
{
    $driver = Driver::factory()->create();
    $user = User::factory()->create(['role' => 'driver', 'email' => $driver->email]);
    $order = Order::factory()->create(['driver_id' => null, 'status' => 'pending', 'is_cancelled' => false, 'posted_by' => $owner?->id]);
    $claim = JobClaim::create(['order_id' => $order->id, 'driver_id' => $user->id, 'status' => 'pending']);

    return [$order, $claim, $driver];
}

it('allows the posting driver or admin to accept a specific claim exactly once', function (string $role) {
    $actor = User::factory()->create(['role' => $role]);
    [$order, $claim, $driver] = auditClaimFixture($role === 'driver' ? $actor : null);
    Sanctum::actingAs($actor);
    $this->postJson('/api/v1/jobs/'.$order->id.'/accept-claim', ['claim_id' => $claim->id])->assertSuccessful();
    expect($order->fresh()->driver_id)->toBe($driver->id)->and($claim->fresh()->status)->toBe('approved');
    $this->postJson('/api/v1/jobs/'.$order->id.'/accept-claim', ['claim_id' => $claim->id])->assertConflict();
})->with(['driver', 'admin']);

it('denies other drivers including the claimant and jobs with no owner', function () {
    [$order, $claim] = auditClaimFixture();
    Sanctum::actingAs(User::findOrFail($claim->driver_id));
    $this->postJson('/api/v1/jobs/'.$order->id.'/accept-claim', ['claim_id' => $claim->id])->assertForbidden();
    $this->postJson('/api/v1/jobs/'.$order->id.'/reject-claim', ['claim_id' => $claim->id])->assertForbidden();
    expect($order->fresh()->driver_id)->toBeNull()->and($claim->fresh()->status)->toBe('pending');
});

it('rejects spoofed driver ids and claims from other jobs', function () {
    $actor = User::factory()->create(['role' => 'admin']);
    [$order, $claim] = auditClaimFixture();
    [$otherOrder, $otherClaim] = auditClaimFixture();
    Sanctum::actingAs($actor);
    $this->postJson('/api/v1/jobs/'.$order->id.'/accept-claim', ['driver_id' => $claim->driver_id, 'claim_id' => $claim->id])->assertUnprocessable();
    $this->postJson('/api/v1/jobs/'.$order->id.'/accept-claim', ['claim_id' => $otherClaim->id])->assertNotFound();
});

it('lets owners reject only pending claims', function () {
    $owner = User::factory()->create(['role' => 'driver']);
    [$order, $claim] = auditClaimFixture($owner);
    Sanctum::actingAs($owner);
    $this->postJson('/api/v1/jobs/'.$order->id.'/reject-claim', ['claim_id' => $claim->id])->assertSuccessful();
    $this->postJson('/api/v1/jobs/'.$order->id.'/reject-claim', ['claim_id' => $claim->id])->assertConflict();
    expect($claim->fresh()->status)->toBe('rejected');
});
