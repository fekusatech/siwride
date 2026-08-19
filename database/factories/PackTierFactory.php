<?php

namespace Database\Factories;

use App\Models\PackTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackTier>
 */
class PackTierFactory extends Factory
{
    protected $model = PackTier::class;

    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'min_pax' => 4,
            'max_pax' => 5,
            'discount_type' => PackTier::TYPE_PERCENT,
            'discount_value' => 10,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
