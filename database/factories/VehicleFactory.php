<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'brand' => $this->faker->randomElement(['Toyota', 'Honda', 'Mitsubishi', 'Daihatsu']),
            'model' => $this->faker->randomElement(['Innova', 'Avanza', 'Fortuner', 'Reborn']),
            'type' => $this->faker->randomElement(['MPV', 'SUV', 'Sedan']),
            'registration_number' => strtoupper($this->faker->bothify('DK #### ??')),
            'color' => $this->faker->colorName(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance']),
        ];
    }
}
