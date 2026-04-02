<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nid' => 'siw'.$this->faker->unique()->numberBetween(1, 9999),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => bcrypt('password'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'nik' => $this->faker->numerify('################'),
            'sim' => $this->faker->numerify('################'),
        ];
    }
}
