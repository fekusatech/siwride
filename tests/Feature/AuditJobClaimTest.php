<?php

use App\Models\Driver;
use App\Models\JobClaim;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\JobClaimConcurrencyHelper;

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

it('uses the same claim decision rules in the admin panel', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$order, $claim, $driver] = auditClaimFixture();
    $this->actingAs($admin)->post(route('admin.orders.accept-claim', $order), ['claim_id' => $claim->id])->assertRedirect();
    expect($order->fresh()->driver_id)->toBe($driver->id);
    $this->post(route('admin.orders.reject-claim', $order), ['claim_id' => $claim->id])->assertConflict();
});

it('records the authenticated creator instead of body ownership', function () {
    $actor = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'driver']);
    Sanctum::actingAs($actor);
    $this->postJson('/api/v1/jobs', [
        'customer_name' => 'Guest', 'customer_phone' => '081234567890',
        'pickup_address' => 'Airport', 'dropoff_address' => 'Hotel',
        'date' => now()->addDay()->toDateString(), 'time' => '10:00', 'price' => 200000, 'posted_by' => $other->id,
    ])->assertCreated()->assertJsonPath('data.posted_by', $actor->id);
});

it('accepts exactly one claim when approvals race', function () {
    $owner = User::factory()->create(['role' => 'admin']);
    [$order, $firstClaim, $firstDriver] = auditClaimFixture();
    $secondDriver = Driver::factory()->create();
    $secondUser = User::factory()->create(['role' => 'driver', 'email' => $secondDriver->email]);
    $secondClaim = JobClaim::create(['order_id' => $order->id, 'driver_id' => $secondUser->id, 'status' => 'pending']);
    $connection = config('database.connections.mysql');
    DB::commit();
    DB::beginTransaction();

    try {
        $results = Concurrency::run([
            JobClaimConcurrencyHelper::attempt($order->id, $firstClaim->id, $owner->id, $connection),
            JobClaimConcurrencyHelper::attempt($order->id, $secondClaim->id, $owner->id, $connection),
        ]);
        sort($results);
        expect($results)->toBe([200, 409]);
        expect(JobClaim::where('order_id', $order->id)->where('status', 'approved')->count())->toBe(1);
    } finally {
        DB::rollBack();
        JobClaim::where('order_id', $order->id)->delete();
        $order->delete();
        User::whereIn('id', [$owner->id, $firstClaim->driver_id, $secondUser->id])->delete();
        Driver::whereIn('id', [$firstDriver->id, $secondDriver->id])->delete();
        DB::beginTransaction();
    }
});
