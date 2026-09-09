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
        $distanceKm ??= GeoService::haversineKm($pickupLat, $pickupLng, $dropoffLat, $dropoffLng);

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
            // calculate()'s first param is the optional VehicleCategory (whose
            // price_per_km should drive the per-km cost) - the admin manual-order
            // form has no vehicle-category selection to supply one, so this
            // falls back to the rule's own price_per_km (0 on every seeded
            // zone-pair), meaning auto-price here is base_price only. Passing
            // $distanceKm positionally as $vehicle threw a TypeError on every
            // call - the form silently kept price at 0 and showed a small
            // "Failed to calculate zone price" message easy to miss.
            'price' => round($rule->calculate(null, $distanceKm), 2),
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
}
