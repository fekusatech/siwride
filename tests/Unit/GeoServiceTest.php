<?php

use App\Services\GeoService;

test('haversineKm computes the known distance between two Bali points', function () {
    // Ngurah Rai Airport to Kuta Beach, ~2.6km apart.
    $km = GeoService::haversineKm(-8.7488, 115.1670, -8.7184, 115.1686);

    expect($km)->toBeGreaterThan(2.0)->toBeLessThan(3.5);
});

test('roadDistanceKm applies the 1.3x correction factor', function () {
    $haversine = GeoService::haversineKm(-8.7488, 115.1670, -8.7184, 115.1686);
    $road = GeoService::roadDistanceKm(-8.7488, 115.1670, -8.7184, 115.1686);

    expect($road)->toBe(round($haversine * 1.3, 2));
});
