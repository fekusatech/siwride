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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'price_per_km' => 'decimal:2',
            'minimum_price' => 'decimal:2',
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

    public function calculate(float $distanceKm): float
    {
        $price = (float) $this->base_price + ($distanceKm * (float) $this->price_per_km);

        return max($price, (float) $this->minimum_price);
    }
}
