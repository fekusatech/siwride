<?php

namespace Database\Factories;

use App\Models\RideSharingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RideSharingLocation>
 */
class RideSharingLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $areas = ['South Bali', 'Central Bali', 'North Bali', 'East Bali', 'West Bali'];

        return [
            'name' => fake()->unique()->city(),
            'area' => fake()->randomElement($areas),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
