<?php

namespace App\Services;

use App\Models\Zone;
use App\Models\ZonePricingRule;

class ZonePricingService
{
    /**
     * @return array{price: float|null, distance_km: float, pickup_zone: array{id:int,name:string}|null, dropoff_zone: array{id:int,name:string}|null, rule: array{id:int,base_price:float,price_per_km:float,minimum_price:float}|null, message: string|null}
     */
    public function calculate(float $pickupLat, float $pickupLng, float $dropoffLat, float $dropoffLng, ?float $distanceKm = null): array
    {
        $pickupZone = Zone::findContainingPoint($pickupLat, $pickupLng);
        $dropoffZone = Zone::findContainingPoint($dropoffLat, $dropoffLng);
        $distanceKm ??= $this->haversineDistanceKm($pickupLat, $pickupLng, $dropoffLat, $dropoffLng);

        if (! $pickupZone || ! $dropoffZone) {
            return [
                'price' => null,
                'distance_km' => round($distanceKm, 2),
                'pickup_zone' => $pickupZone ? ['id' => $pickupZone->id, 'name' => $pickupZone->name] : null,
                'dropoff_zone' => $dropoffZone ? ['id' => $dropoffZone->id, 'name' => $dropoffZone->name] : null,
                'rule' => null,
                'message' => 'Pickup or dropoff is outside active pricing zones.',
            ];
        }

        $rule = ZonePricingRule::active()
            ->where('pickup_zone_id', $pickupZone->id)
            ->where('dropoff_zone_id', $dropoffZone->id)
            ->first();

        if (! $rule) {
            return [
                'price' => null,
                'distance_km' => round($distanceKm, 2),
                'pickup_zone' => ['id' => $pickupZone->id, 'name' => $pickupZone->name],
                'dropoff_zone' => ['id' => $dropoffZone->id, 'name' => $dropoffZone->name],
                'rule' => null,
                'message' => 'No active pricing rule found for these zones.',
            ];
        }

        return [
            'price' => round($rule->calculate($distanceKm), 2),
            'distance_km' => round($distanceKm, 2),
            'pickup_zone' => ['id' => $pickupZone->id, 'name' => $pickupZone->name],
            'dropoff_zone' => ['id' => $dropoffZone->id, 'name' => $dropoffZone->name],
            'rule' => [
                'id' => $rule->id,
                'base_price' => (float) $rule->base_price,
                'price_per_km' => (float) $rule->price_per_km,
                'minimum_price' => (float) $rule->minimum_price,
            ],
            'message' => null,
        ];
    }

    private function haversineDistanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadiusKm = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
