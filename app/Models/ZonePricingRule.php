<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZonePricingRule extends Model
{
    protected $fillable = [
        'pickup_zone_id',
        'dropoff_zone_id',
        'base_price',
        'price_per_km',
        'minimum_price',
        'distance_km',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'price_per_km' => 'decimal:2',
            'minimum_price' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function pickupZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'pickup_zone_id');
    }

    public function dropoffZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'dropoff_zone_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate the booking price for this zone pair.
     *
     * Formula: max(minimum_price, base_price + distance_km × vehicle.price_per_km)
     *
     * If a VehicleCategory is supplied its price_per_km is used as the per-km
     * rate; otherwise the zone's own price_per_km is used as a fallback so that
     * the method stays backward-compatible.
     */
    public function calculate(?VehicleCategory $vehicle = null, ?float $exactDistanceKm = null): float
    {
        $perKm = $vehicle
            ? (float) $vehicle->price_per_km
            : (float) $this->price_per_km;

        $dist = $exactDistanceKm !== null ? $exactDistanceKm : (float) $this->distance_km;

        $price = (float) $this->base_price + ($dist * $perKm);

        return max($price, (float) $this->minimum_price);
    }
}
