<?php

namespace Database\Factories;

use App\Models\DriverService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverService>
 */
class DriverServiceFactory extends Factory
{
    protected $model = DriverService::class;

    public function definition(): array
    {
        return [
            'driver_id' => User::factory()->state(['role' => 'driver']),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'price_per_pax' => 100000,
            'dp_percent' => 30,
            'min_pax' => 1,
            'max_pax' => 10,
            'status' => DriverService::STATUS_APPROVED,
        ];
    }
}
