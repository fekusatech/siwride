<?php

namespace Database\Seeders;

use App\Models\RideSharingLocation;
use App\Models\RideSharingSchedule;
use Illuminate\Database\Seeder;

class RideSharingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Kuta', 'area' => 'South Bali', 'sort_order' => 1],
            ['name' => 'Seminyak', 'area' => 'South Bali', 'sort_order' => 2],
            ['name' => 'Legian', 'area' => 'South Bali', 'sort_order' => 3],
            ['name' => 'Canggu', 'area' => 'South Bali', 'sort_order' => 4],
            ['name' => 'Sanur', 'area' => 'South Bali', 'sort_order' => 5],
            ['name' => 'Jimbaran', 'area' => 'South Bali', 'sort_order' => 6],
            ['name' => 'Nusa Dua', 'area' => 'South Bali', 'sort_order' => 7],
            ['name' => 'Uluwatu', 'area' => 'South Bali', 'sort_order' => 8],
            ['name' => 'Ubud', 'area' => 'Central Bali', 'sort_order' => 9],
            ['name' => 'Tegallalang', 'area' => 'Central Bali', 'sort_order' => 10],
            ['name' => 'Gianyar', 'area' => 'Central Bali', 'sort_order' => 11],
            ['name' => 'Denpasar', 'area' => 'Central Bali', 'sort_order' => 12],
            ['name' => 'Ngurah Rai Airport', 'area' => 'South Bali', 'sort_order' => 13],
            ['name' => 'Bedugul', 'area' => 'North Bali', 'sort_order' => 14],
            ['name' => 'Singaraja', 'area' => 'North Bali', 'sort_order' => 15],
            ['name' => 'Lovina', 'area' => 'North Bali', 'sort_order' => 16],
            ['name' => 'Amed', 'area' => 'East Bali', 'sort_order' => 17],
            ['name' => 'Candidasa', 'area' => 'East Bali', 'sort_order' => 18],
            ['name' => 'Padangbai', 'area' => 'East Bali', 'sort_order' => 19],
            ['name' => 'Tabanan', 'area' => 'West Bali', 'sort_order' => 20],
        ];

        foreach ($locations as $location) {
            RideSharingLocation::firstOrCreate(
                ['name' => $location['name']],
                ['area' => $location['area'], 'is_active' => true, 'sort_order' => $location['sort_order']],
            );
        }

        $schedules = [
            ['departure_time' => '06:00', 'label' => 'Early Morning – 06:00', 'sort_order' => 1],
            ['departure_time' => '08:00', 'label' => 'Morning – 08:00', 'sort_order' => 2],
            ['departure_time' => '10:00', 'label' => 'Mid-Morning – 10:00', 'sort_order' => 3],
            ['departure_time' => '12:00', 'label' => 'Midday – 12:00', 'sort_order' => 4],
            ['departure_time' => '14:00', 'label' => 'Afternoon – 14:00', 'sort_order' => 5],
            ['departure_time' => '16:00', 'label' => 'Late Afternoon – 16:00', 'sort_order' => 6],
            ['departure_time' => '18:00', 'label' => 'Evening – 18:00', 'sort_order' => 7],
            ['departure_time' => '20:00', 'label' => 'Night – 20:00', 'sort_order' => 8],
        ];

        foreach ($schedules as $schedule) {
            RideSharingSchedule::firstOrCreate(
                ['departure_time' => $schedule['departure_time']],
                ['label' => $schedule['label'], 'is_active' => true, 'sort_order' => $schedule['sort_order']],
            );
        }
    }
}
