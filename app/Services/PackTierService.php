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
    public function tierForPax(int $pax, ?int $activityId = null): ?PackTier
    {
        $baseQuery = fn () => PackTier::query()
            ->where('is_active', true)
            ->where('min_pax', '<=', $pax)
            ->where(function ($query) use ($pax) {
                $query->whereNull('max_pax')
                    ->orWhere('max_pax', '>=', $pax);
            })
            ->orderBy('min_pax', 'desc');

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
    public function priceForPax(float $basePrice, int $pax, ?int $activityId = null): array
    {
        $tier = $this->tierForPax($pax, $activityId);

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
    public function allTiersForDisplay(?int $activityId = null): array
    {
        return PackTier::query()
            ->where('is_active', true)
            ->where(function ($query) use ($activityId) {
                $query->whereNull('activity_id');

                if ($activityId !== null) {
                    $query->orWhere('activity_id', $activityId);
                }
            })
            ->orderByRaw('activity_id IS NOT NULL DESC')
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
            ])
            ->all();
    }
}
