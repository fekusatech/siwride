<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'price_per_pax' => 150000,
            'min_pax' => 1,
            'max_pax' => 10,
            'duration_label' => '4 hours',
            'meeting_point' => fake()->address(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
