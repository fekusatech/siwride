<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'slug' => 'airport-transfer',
                'title' => 'Airport Transfer',
                'subtitle' => 'Point-to-Point',
                'description' => 'Seamless door-to-door transfer from/to the airport or any fixed destination. Fixed price, no hidden fees.',
                'href' => '/booking/airport-transfer',
                'is_active' => true,
            ],
            [
                'slug' => 'tour',
                'title' => 'Tour Package',
                'subtitle' => 'Multi-destination',
                'description' => 'Curated Bali tour packages with pre-planned itineraries. Visit multiple destinations in one trip at a fixed package price.',
                'href' => '/booking/tour',
                'is_active' => true,
            ],
            [
                'slug' => 'sharing-ride',
                'title' => 'Sharing Ride',
                'subtitle' => 'Shared Transport',
                'description' => 'Travel on scheduled routes (e.g., Denpasar to Gilimanuk) in a shared Hi-Ace or minibus alongside other passengers.',
                'href' => '/booking/sharing-ride',
                'is_active' => true,
            ],
            [
                'slug' => 'hourly',
                'title' => 'Hourly Service',
                'subtitle' => 'Car + Driver',
                'description' => 'Rent a car with a professional driver by the hour. No fixed destination — go wherever you want, whenever you want.',
                'href' => '/booking/hourly',
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::updateOrCreate(
                ['slug' => $serviceData['slug']],
                $serviceData
            );
        }
    }
}
