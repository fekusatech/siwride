<?php

namespace App\Services;

use App\Models\VehicleCategory;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CustomerTransferPricingService
{
    /**
     * @return array{
     *     pickup_zone: string,
     *     dropoff_zone: string,
     *     distance_km: float,
     *     duration_minutes: int,
     *     prices: Collection<int, array{id: int, estimated_price: float, price_per_km: float}>
     * }
     */
    public function estimate(
        float $pickupLatitude,
        float $pickupLongitude,
        float $dropoffLatitude,
        float $dropoffLongitude,
        ?int $passengers = null,
    ): array {
        $pickupZone = Zone::findContainingPoint($pickupLatitude, $pickupLongitude);
        $dropoffZone = Zone::findContainingPoint($dropoffLatitude, $dropoffLongitude);

        if (! $pickupZone) {
            throw ValidationException::withMessages([
                'pickup_address' => 'The pickup address is outside our service area.',
            ]);
        }

        if (! $dropoffZone) {
            throw ValidationException::withMessages([
                'dropoff_address' => 'The dropoff address is outside our service area.',
            ]);
        }

        $pricingRule = ZonePricingRule::query()
            ->active()
            ->where('pickup_zone_id', $pickupZone->id)
            ->where('dropoff_zone_id', $dropoffZone->id)
            ->first();

        if (! $pricingRule) {
            throw ValidationException::withMessages([
                'pickup_address' => 'The selected route is outside our service area.',
            ]);
        }

        $distance = GeoService::roadDistanceKm(
            $pickupLatitude,
            $pickupLongitude,
            $dropoffLatitude,
            $dropoffLongitude,
        );

        $vehicles = VehicleCategory::query()
            ->when($passengers !== null, function ($query) use ($passengers): void {
                $query->where(function ($query) use ($passengers): void {
                    $query->whereNull('passenger_capacity')
                        ->orWhere('passenger_capacity', '>=', $passengers);
                });
            })
            ->orderBy('base_price')
            ->get();

        return [
            'pickup_zone' => $pickupZone->name,
            'dropoff_zone' => $dropoffZone->name,
            'distance_km' => $distance,
            'duration_minutes' => (int) round($distance / 30 * 60),
            'prices' => $vehicles->map(fn (VehicleCategory $vehicle): array => [
                'id' => $vehicle->id,
                'estimated_price' => round($pricingRule->calculate($vehicle, $distance), 2),
                'price_per_km' => (float) $vehicle->price_per_km,
            ]),
        ];
    }

    public function priceForVehicle(array $estimate, int $vehicleCategoryId): float
    {
        $price = $estimate['prices']->firstWhere('id', $vehicleCategoryId);

        if (! $price) {
            throw ValidationException::withMessages([
                'vehicle_category_id' => 'The selected vehicle is not available for this trip.',
            ]);
        }

        return (float) $price['estimated_price'];
    }
}
