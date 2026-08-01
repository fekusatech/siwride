<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            [
                'slug' => 'airport-transfer',
                'title' => 'Airport Transfer',
                'subtitle' => 'Point-to-Point',
                'description' => 'Seamless door-to-door transfer from/to the airport or any fixed destination. Fixed price, no hidden fees.',
                'href' => '/booking/airport-transfer',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'tour',
                'title' => 'Tour Package',
                'subtitle' => 'Multi-destination',
                'description' => 'Curated Bali tour packages with pre-planned itineraries. Visit multiple destinations in one trip at a fixed package price.',
                'href' => '/booking/tour',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'sharing-ride',
                'title' => 'Sharing Ride',
                'subtitle' => 'Shared Transport',
                'description' => 'Travel on scheduled routes (e.g., Denpasar to Gilimanuk) in a shared Hi-Ace or minibus alongside other passengers.',
                'href' => '/booking/sharing-ride',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'hourly',
                'title' => 'Hourly Service',
                'subtitle' => 'Car + Driver',
                'description' => 'Rent a car with a professional driver by the hour. No fixed destination — go wherever you want, whenever you want.',
                'href' => '/booking/hourly',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($activities as $activityData) {
            Activity::updateOrCreate(
                ['slug' => $activityData['slug']],
                $activityData
            );
        }
    }
}
