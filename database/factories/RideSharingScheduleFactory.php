<?php

namespace Database\Factories;

use App\Models\RideSharingSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RideSharingSchedule>
 */
class RideSharingScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schedules = [
            ['time' => '06:00', 'label' => 'Early Morning – 06:00'],
            ['time' => '08:00', 'label' => 'Morning – 08:00'],
            ['time' => '10:00', 'label' => 'Mid-Morning – 10:00'],
            ['time' => '12:00', 'label' => 'Midday – 12:00'],
            ['time' => '14:00', 'label' => 'Afternoon – 14:00'],
            ['time' => '16:00', 'label' => 'Late Afternoon – 16:00'],
            ['time' => '18:00', 'label' => 'Evening – 18:00'],
        ];

        $schedule = fake()->randomElement($schedules);

        return [
            'departure_time' => $schedule['time'],
            'label' => $schedule['label'],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
