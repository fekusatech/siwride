<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LocationSearchController;
use App\Http\Requests\Api\Customer\SearchLocationsRequest;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __construct(private LocationSearchController $locations) {}

    public function __invoke(SearchLocationsRequest $request): JsonResponse
    {
        $response = $this->locations->search($request);

        return response()->json([
            'data' => $response->getData(true),
        ]);
    }
}
