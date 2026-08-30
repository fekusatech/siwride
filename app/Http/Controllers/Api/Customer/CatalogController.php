<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Customer\VehicleCategoryResource;
use App\Models\VehicleCategory;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $vehicles = VehicleCategory::query()->orderBy('base_price')->get();

        return response()->json([
            'data' => [
                'services' => [
                    [
                        'key' => 'airport_transfer',
                        'title' => 'Airport transfer',
                        'subtitle' => 'Private, on-time pickup',
                        'native_booking' => true,
                        'web_url' => route('booking.airport-transfer'),
                    ],
                    [
                        'key' => 'ride_sharing',
                        'title' => 'Ride sharing',
                        'subtitle' => 'Comfortable shared seats',
                        'native_booking' => false,
                        'web_url' => route('booking.sharing-ride'),
                    ],
                    [
                        'key' => 'hourly_service',
                        'title' => 'Hourly service',
                        'subtitle' => 'A driver on your schedule',
                        'native_booking' => false,
                        'web_url' => route('booking.hourly'),
                    ],
                    [
                        'key' => 'tours_activities',
                        'title' => 'Tours & activities',
                        'subtitle' => 'Discover the best of Bali',
                        'native_booking' => false,
                        'web_url' => route('booking.tour'),
                    ],
                ],
                'vehicles' => VehicleCategoryResource::collection($vehicles)->resolve(),
            ],
        ]);
    }
}
