<?php

namespace App\Services;

class GeoService
{
    private const EARTH_RADIUS_KM = 6371.0;

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Straight-line distance scaled by a fixed road-distance correction factor.
     *
     * ponytail: flat 1.3x factor, no real routing engine. Upgrade path: a
     * self-hosted OSRM/Valhalla call if pricing precision matters later.
     */
    public static function roadDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2, float $factor = 1.3): float
    {
        return round(self::haversineKm($lat1, $lng1, $lat2, $lng2) * $factor, 2);
    }
}
