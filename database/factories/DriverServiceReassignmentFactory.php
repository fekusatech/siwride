<?php

namespace Database\Factories;

use App\Models\DriverServiceBooking;
use App\Models\DriverServiceReassignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverServiceReassignment>
 */
class DriverServiceReassignmentFactory extends Factory
{
    protected $model = DriverServiceReassignment::class;

    public function definition(): array
    {
        return [
            'booking_id' => DriverServiceBooking::factory(),
            'from_driver_id' => User::factory()->create(['role' => 'driver'])->id,
            'to_driver_id' => User::factory()->create(['role' => 'driver'])->id,
            'reason' => fake()->sentence(),
            'status' => DriverServiceReassignment::STATUS_PENDING,
        ];
    }
}
