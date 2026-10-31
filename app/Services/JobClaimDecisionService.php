<?php

namespace App\Services;

use App\Models\JobClaim;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class JobClaimDecisionService
{
    public function decide(User $actor, int $id, int $claimId, bool $accept): Order
    {
        return DB::transaction(function () use ($actor, $id, $claimId, $accept) {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            abort_unless($actor->isAdmin() || ($actor->role === 'driver' && $order->posted_by !== null && (int) $order->posted_by === $actor->id), 403);
            $claim = JobClaim::query()->where('order_id', $order->id)
                ->lockForUpdate()->findOrFail($claimId);
            abort_if($claim->status !== 'pending' || $order->driver_id !== null || $order->is_cancelled || ! in_array($order->status, ['pending', 'shared'], true), 409);

            if ($accept) {
                $driverId = $claim->driver?->driver_id;
                abort_unless($driverId, 422, 'Driver record not found.');
                $order->update(['driver_id' => $driverId, 'is_shared' => false, 'status' => 'pending']);
                JobClaim::query()->where('order_id', $id)->where('status', 'pending')->update(['status' => 'rejected']);
            }
            $claim->update(['status' => $accept ? 'approved' : 'rejected']);

            return $order;
        }, attempts: 5);
    }
}
