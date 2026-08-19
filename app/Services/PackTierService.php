<?php

namespace App\Services;

use App\Models\PackTier;

class PackTierService
{
    /**
     * Find the applicable tier for a given pax count.
     * Returns null if no tier matches (use base price).
     */
    public function tierForPax(int $pax): ?PackTier
    {
        return PackTier::query()
            ->where('is_active', true)
            ->where('min_pax', '<=', $pax)
            ->where(function ($query) use ($pax) {
                $query->whereNull('max_pax')
                    ->orWhere('max_pax', '>=', $pax);
            })
            ->orderBy('min_pax', 'desc')
            ->first();
    }

    /**
     * Compute the effective price per pax for a given base price and pax count.
     *
     * @return array{price_per_pax: float, tier: ?PackTier, base_price: float}
     */
    public function priceForPax(float $basePrice, int $pax): array
    {
        $tier = $this->tierForPax($pax);

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
    public function allTiersForDisplay(): array
    {
        return PackTier::query()
            ->where('is_active', true)
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
            ])
            ->all();
    }
}
