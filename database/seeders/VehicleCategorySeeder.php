<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VehicleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing to avoid duplicates
        VehicleCategory::truncate();

        // Ensure vehicles directory exists in public storage disk
        Storage::disk('public')->makeDirectory('vehicles');

        // Copy static assets to the storage disk so they are served correctly
        $sourcePath = public_path('assets/images/vehicles');
        $files = ['sedan.png', 'minivan.png', 'minibus.png', 'electric.png', 'business.png'];

        foreach ($files as $file) {
            $srcFile = $sourcePath.'/'.$file;
            $destFile = storage_path('app/public/vehicles/'.$file);
            if (File::exists($srcFile)) {
                File::copy($srcFile, $destFile);
            }
        }

        $categories = [
            [
                'slug' => 'standard-vehicle',
                'title' => 'Standard Vehicle',
                'description' => 'Comfortable and affordable standard vehicles for everyday travel.',
                'capacity' => 'Up to 4 passengers',
                'examples' => 'Toyota Avanza, Honda Mobilio, Suzuki Ertiga',
                'image' => '/assets/images/vehicles/sedan.png',
                'vehicle_type' => 'economy',
            ],
            [
                'slug' => 'minivan',
                'title' => 'Minivan',
                'description' => 'Compact family van with extra cabin space and luggage capacity.',
                'capacity' => 'Up to 6 passengers',
                'examples' => 'Toyota Innova, Honda Freed, Toyota Sienta',
                'image' => '/assets/images/vehicles/minivan.png',
                'vehicle_type' => 'economy',
            ],
            [
                'slug' => 'van-minibus',
                'title' => 'Van / Minibus',
                'description' => 'Spacious vehicles tailored for larger families and group tours.',
                'capacity' => 'Up to 15 passengers',
                'examples' => 'Toyota Hiace, Suzuki APV, Mitsubishi L300',
                'image' => '/assets/images/vehicles/minibus.png',
                'vehicle_type' => 'van',
            ],
            [
                'slug' => 'listrik',
                'title' => 'Listrik',
                'description' => 'Eco-friendly electric vehicles for sustainable and quiet travel.',
                'capacity' => 'Up to 4 passengers',
                'examples' => 'Hyundai Ioniq 5, Wuling Binguo EV, BYD Atto 3',
                'image' => '/assets/images/vehicles/electric.png',
                'vehicle_type' => 'special',
            ],
            [
                'slug' => 'premium-vehicle',
                'title' => 'Premium Vehicle',
                'description' => 'Luxury vehicles for a premium and prestigious travel experience.',
                'capacity' => 'Up to 4 passengers',
                'examples' => 'Toyota Camry, Mercedes C-Class, Alphard',
                'image' => '/assets/images/vehicles/business.png',
                'vehicle_type' => 'premium',
            ],
        ];

        foreach ($categories as $category) {
            VehicleCategory::create($category);
        }
    }
}
