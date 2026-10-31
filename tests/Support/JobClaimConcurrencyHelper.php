<?php

namespace Tests\Support;

use App\Http\Controllers\Api\JobController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class JobClaimConcurrencyHelper
{
    public static function attempt(int $orderId, int $claimId, int $actorId, array $connection): Closure
    {
        return static function () use ($orderId, $claimId, $actorId, $connection): int {
            config(['database.default' => 'mysql', 'database.connections.mysql' => $connection]);
            DB::purge('mysql');
            if (DB::connection()->getDatabaseName() !== 'siwride_testing') {
                throw new \RuntimeException('Claim concurrency test requires siwride_testing.');
            }
            $request = Request::create('/claim', 'POST', ['claim_id' => $claimId]);
            $request->setUserResolver(static fn () => User::findOrFail($actorId));

            try {
                return app(JobController::class)->acceptClaim($request, $orderId)->getStatusCode();
            } catch (HttpExceptionInterface $exception) {
                return $exception->getStatusCode();
            }
        };
    }
}
