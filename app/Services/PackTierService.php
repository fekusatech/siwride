<?php

namespace App\Services;

use App\Models\PackTier;

class PackTierService
{
    /**
     * Find the applicable tier for a given pax count.
     * Activity-specific tiers take precedence over global tiers (activity_id null).
     * Returns null if no tier matches (use base price).
     */
    public function tierForPax(int $pax, ?int $activityId = null, ?int $driverServiceId = null): ?PackTier
    {
        $baseQuery = fn () => PackTier::query()
            ->where('is_active', true)
            ->where('min_pax', '<=', $pax)
            ->where(function ($query) use ($pax) {
                $query->whereNull('max_pax')
                    ->orWhere('max_pax', '>=', $pax);
            })
            ->orderBy('min_pax', 'desc');

        if ($driverServiceId !== null) {
            $query = $baseQuery();

            $scoped = (clone $query)->where('driver_service_id', $driverServiceId)->first();

            return $scoped ?? $query->whereNull('activity_id')->whereNull('driver_service_id')->first();
        }

        if ($activityId !== null) {
            $query = $baseQuery();

            $scoped = (clone $query)->where('activity_id', $activityId)->first();

            return $scoped ?? $query->whereNull('activity_id')->first();
        }

        return $baseQuery()->first();
    }

    /**
     * Compute the effective price per pax for a given base price and pax count.
     *
     * @return array{price_per_pax: float, tier: ?PackTier, base_price: float}
     */
    public function priceForPax(float $basePrice, int $pax, ?int $activityId = null, ?int $driverServiceId = null): array
    {
        $tier = $this->tierForPax($pax, $activityId, $driverServiceId);

        return [
            'price_per_pax' => $tier ? $tier->pricePerPax($basePrice) : $basePrice,
            'tier' => $tier,
            'base_price' => $basePrice,
        ];
    }

    /**
     * Get all active tiers ordered by min_pax for display.
     *
     * @return array<int, array>
     */
    public function allTiersForDisplay(?int $activityId = null, ?int $driverServiceId = null): array
    {
        return PackTier::query()
            ->where('is_active', true)
            ->where(function ($query) use ($activityId, $driverServiceId) {
                $query->whereNull('activity_id')->whereNull('driver_service_id');

                if ($activityId !== null) {
                    $query->orWhere('activity_id', $activityId);
                }

                if ($driverServiceId !== null) {
                    $query->orWhere('driver_service_id', $driverServiceId);
                }
            })
            ->orderByRaw('(activity_id IS NOT NULL OR driver_service_id IS NOT NULL) DESC')
            ->orderBy('min_pax')
            ->get()
            ->map(fn (PackTier $tier) => [
                'id' => $tier->id,
                'label' => $tier->label,
                'min_pax' => $tier->min_pax,
                'max_pax' => $tier->max_pax,
                'discount_type' => $tier->discount_type,
                'discount_value' => (float) $tier->discount_value,
                'discount_label' => $tier->discountLabel(),
                'activity_id' => $tier->activity_id,
                'driver_service_id' => $tier->driver_service_id,
            ])
            ->all();
    }
}
