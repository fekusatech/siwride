<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\EstimatePriceRequest;
use App\Services\CustomerTransferPricingService;
use Illuminate\Http\JsonResponse;

class PriceEstimateController extends Controller
{
    public function __construct(private CustomerTransferPricingService $pricing) {}

    public function __invoke(EstimatePriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json([
            'data' => $this->pricing->estimate(
                (float) $validated['pickup_latitude'],
                (float) $validated['pickup_longitude'],
                (float) $validated['dropoff_latitude'],
                (float) $validated['dropoff_longitude'],
                isset($validated['passengers']) ? (int) $validated['passengers'] : null,
            ),
        ]);
    }
}
