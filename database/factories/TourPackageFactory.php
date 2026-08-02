<?php

namespace Database\Factories;

use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TourPackage>
 */
class TourPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(3),
            'highlights' => fake()->paragraph(2),
            'price_per_pax' => fake()->numberBetween(300000, 1500000),
            'duration_hours' => fake()->randomElement([8, 10, 12]),
            'max_pax' => fake()->randomElement([6, 7, 8]),
            'min_pax' => 1,
            'destinations' => fake()->words(4),
            'itinerary' => [
                ['time' => '08:00', 'activity' => 'Hotel Pickup', 'location' => 'Your Hotel'],
                ['time' => '10:00', 'activity' => fake()->sentence(4), 'location' => fake()->word()],
                ['time' => '12:00', 'activity' => 'Lunch Break', 'location' => 'Local Restaurant'],
                ['time' => '17:00', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
            ],
            'includes' => ['Driver', 'Fuel', 'Mineral Water', 'Parking Fees'],
            'excludes' => ['Entrance Tickets', 'Meals', 'Personal Expenses'],
            'image' => null,
            'gallery' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
