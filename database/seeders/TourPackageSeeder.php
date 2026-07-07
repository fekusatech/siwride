<?php

namespace Database\Seeders;

use App\Models\TourPackage;
use Illuminate\Database\Seeder;

class TourPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Bali Classic Tour',
                'slug' => 'bali-classic-tour',
                'description' => 'Experience the most iconic landmarks of Bali in one unforgettable day. From the majestic Tanah Lot sea temple to the spectacular Uluwatu cliff temple with its Kecak fire dance at sunset, this tour covers the very best of Bali\'s cultural and natural wonders.',
                'highlights' => 'Watch the stunning sunset Kecak dance at Uluwatu Temple. Visit the iconic Tanah Lot sea temple. Explore the GWK Cultural Park featuring the grand Garuda Wisnu Kencana statue.',
                'price_per_pax' => 450000,
                'duration_hours' => 10,
                'max_pax' => 7,
                'min_pax' => 1,
                'destinations' => ['Tanah Lot Temple', 'Kuta Beach', 'GWK Cultural Park', 'Uluwatu Temple'],
                'itinerary' => [
                    ['time' => '08:00', 'activity' => 'Hotel Pickup', 'location' => 'Your Hotel (Kuta / Seminyak / Nusa Dua area)'],
                    ['time' => '09:00', 'activity' => 'Visit Tanah Lot Temple — iconic sea temple perched on a rocky islet', 'location' => 'Tanah Lot, Tabanan'],
                    ['time' => '11:00', 'activity' => 'Stroll along Kuta Beach — Bali\'s most famous beach strip', 'location' => 'Kuta, Badung'],
                    ['time' => '12:30', 'activity' => 'Lunch at local Balinese restaurant (own expense)', 'location' => 'Seminyak area'],
                    ['time' => '14:00', 'activity' => 'Visit GWK Cultural Park — towering Garuda Wisnu Kencana statue', 'location' => 'Ungasan, Badung'],
                    ['time' => '16:00', 'activity' => 'Visit Uluwatu Temple — dramatic clifftop temple at 70m above the ocean', 'location' => 'Pecatu, Badung'],
                    ['time' => '18:00', 'activity' => 'Sunset Kecak Fire Dance performance', 'location' => 'Uluwatu Temple Amphitheatre'],
                    ['time' => '19:30', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
                ],
                'includes' => ['Professional driver', 'Fuel', 'Mineral water (1 bottle)', 'Parking fees', 'Air-conditioned vehicle'],
                'excludes' => ['Entrance tickets', 'Meals & drinks', 'Personal expenses', 'Tips for driver'],
                'image' => '/assets/images/tour/bali-classic.jpg',
                'gallery' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ubud Cultural Tour',
                'slug' => 'ubud-cultural-tour',
                'description' => 'Dive deep into Bali\'s artistic soul in the lush highland town of Ubud. Walk through the Sacred Monkey Forest Sanctuary, marvel at the terraced Tegallalang rice paddies, and purify yourself at the ancient Tirta Empul holy spring temple.',
                'highlights' => 'Explore the Sacred Monkey Forest Sanctuary. Marvel at the UNESCO-listed Tegallalang rice terraces. Experience the spiritual purification ritual at Tirta Empul holy spring.',
                'price_per_pax' => 400000,
                'duration_hours' => 9,
                'max_pax' => 7,
                'min_pax' => 1,
                'destinations' => ['Ubud Monkey Forest', 'Tegallalang Rice Terrace', 'Tirta Empul Temple', 'Ubud Royal Palace'],
                'itinerary' => [
                    ['time' => '08:30', 'activity' => 'Hotel Pickup', 'location' => 'Your Hotel'],
                    ['time' => '09:30', 'activity' => 'Visit Ubud Royal Palace & Ubud Art Market', 'location' => 'Central Ubud'],
                    ['time' => '10:30', 'activity' => 'Explore Sacred Monkey Forest Sanctuary', 'location' => 'Padangtegal, Ubud'],
                    ['time' => '12:00', 'activity' => 'Lunch at a traditional Warung overlooking rice fields (own expense)', 'location' => 'Ubud'],
                    ['time' => '13:30', 'activity' => 'Photo stop at Tegallalang Rice Terrace — stunning UNESCO heritage landscape', 'location' => 'Tegallalang, Gianyar'],
                    ['time' => '15:00', 'activity' => 'Visit Tirta Empul Temple — sacred spring and ritual purification pools', 'location' => 'Tampaksiring, Gianyar'],
                    ['time' => '16:30', 'activity' => 'Optional: Visit a traditional Balinese silver or woodcraft workshop', 'location' => 'Celuk or Mas Village'],
                    ['time' => '17:30', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
                ],
                'includes' => ['Professional driver', 'Fuel', 'Mineral water (1 bottle)', 'Parking fees', 'Air-conditioned vehicle'],
                'excludes' => ['Entrance tickets', 'Meals & drinks', 'Personal expenses', 'Sarong rental at temples'],
                'image' => '/assets/images/tour/ubud-cultural.jpg',
                'gallery' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'East Bali Explorer',
                'slug' => 'east-bali-explorer',
                'description' => 'Venture off the beaten path to discover East Bali\'s breathtaking landscapes. From the magnificent "Gateway of Heaven" at Lempuyang Temple to the serene black-sand beaches of Amed, this tour offers Bali\'s most dramatic and spiritual scenery.',
                'highlights' => 'Photograph the iconic "Gates of Heaven" at Pura Lempuyang with Mount Agung as backdrop. Visit Besakih — Bali\'s largest and holiest Hindu temple. Relax at the serene black-sand beach of Amed.',
                'price_per_pax' => 600000,
                'duration_hours' => 12,
                'max_pax' => 7,
                'min_pax' => 1,
                'destinations' => ['Besakih Temple', 'Lempuyang Temple', 'Tirta Gangga', 'Amed Beach'],
                'itinerary' => [
                    ['time' => '07:00', 'activity' => 'Hotel Pickup (early start for the long journey)', 'location' => 'Your Hotel'],
                    ['time' => '09:00', 'activity' => 'Visit Pura Besakih — the Mother Temple of Bali on the slopes of Mount Agung', 'location' => 'Besakih, Karangasem'],
                    ['time' => '11:00', 'activity' => 'Visit Lempuyang Temple — photograph the legendary "Gates of Heaven"', 'location' => 'Karangasem'],
                    ['time' => '13:00', 'activity' => 'Lunch at local restaurant (own expense)', 'location' => 'Karangasem area'],
                    ['time' => '14:00', 'activity' => 'Explore Tirta Gangga — royal water palace with ornate fountains and ponds', 'location' => 'Abang, Karangasem'],
                    ['time' => '15:30', 'activity' => 'Relax at Amed Beach — peaceful black-sand beach with snorkeling opportunities', 'location' => 'Amed, Karangasem'],
                    ['time' => '17:00', 'activity' => 'Scenic coastal drive back to South Bali', 'location' => 'East Bali Coast Road'],
                    ['time' => '19:00', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
                ],
                'includes' => ['Professional driver', 'Fuel', 'Mineral water (2 bottles)', 'Parking fees', 'Air-conditioned vehicle'],
                'excludes' => ['Entrance tickets', 'Meals & drinks', 'Lempuyang guide (mandatory on-site)', 'Personal expenses'],
                'image' => '/assets/images/tour/east-bali.jpg',
                'gallery' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'North Bali Adventure',
                'slug' => 'north-bali-adventure',
                'description' => 'Escape the tourist crowds and discover Bali\'s wild and spectacular north. Watch the sunrise over the volcanic crater lake at Kintamani, chase the multi-tiered Sekumpul waterfall through lush jungle trails, and visit the scenic Lovina dolphin coast.',
                'highlights' => 'Sunrise view over the Batur Volcano caldera and Lake Batur at Kintamani. Trek to the magnificent Sekumpul Waterfall — Bali\'s most beautiful waterfall. Visit the Git Git waterfall and the cool air of Bedugul highlands.',
                'price_per_pax' => 550000,
                'duration_hours' => 11,
                'max_pax' => 7,
                'min_pax' => 1,
                'destinations' => ['Kintamani Volcano', 'Sekumpul Waterfall', 'Bedugul', 'Lovina Beach'],
                'itinerary' => [
                    ['time' => '07:00', 'activity' => 'Hotel Pickup', 'location' => 'Your Hotel'],
                    ['time' => '09:00', 'activity' => 'Kintamani Volcano viewpoint — panoramic views of Mount Batur & crater lake', 'location' => 'Kintamani, Bangli'],
                    ['time' => '10:30', 'activity' => 'Trek to Sekumpul Waterfall — Bali\'s most spectacular multi-tier waterfall', 'location' => 'Sekumpul, Singaraja'],
                    ['time' => '13:00', 'activity' => 'Lunch at highland restaurant (own expense)', 'location' => 'Singaraja area'],
                    ['time' => '14:00', 'activity' => 'Visit Git Git Waterfall and cool highland villages', 'location' => 'Git Git, Buleleng'],
                    ['time' => '15:30', 'activity' => 'Ulun Danu Beratan Temple — floating temple on Lake Beratan at Bedugul', 'location' => 'Bedugul, Tabanan'],
                    ['time' => '17:00', 'activity' => 'Visit Jatiluwih UNESCO rice terraces (scenic drive)', 'location' => 'Jatiluwih, Tabanan'],
                    ['time' => '18:00', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
                ],
                'includes' => ['Professional driver', 'Fuel', 'Mineral water (2 bottles)', 'Parking fees', 'Air-conditioned vehicle'],
                'excludes' => ['Entrance tickets', 'Waterfall local guide', 'Meals & drinks', 'Personal expenses'],
                'image' => '/assets/images/tour/north-bali.jpg',
                'gallery' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Full-Day South Bali',
                'slug' => 'full-day-south-bali',
                'description' => 'A leisurely full-day exploration of Bali\'s vibrant southern coast. From the trendy beach clubs and surf spots of Canggu and Seminyak to the upscale resort enclave of Nusa Dua, enjoy the best of South Bali\'s chic lifestyle and beautiful beaches at your own pace.',
                'highlights' => 'Explore the hipster beach village of Canggu and its iconic Batu Bolong beach. Browse the boutique shops and beach clubs of Seminyak. Enjoy the pristine white-sand beaches of Nusa Dua.',
                'price_per_pax' => 350000,
                'duration_hours' => 8,
                'max_pax' => 7,
                'min_pax' => 1,
                'destinations' => ['Canggu', 'Seminyak', 'Legian', 'Nusa Dua'],
                'itinerary' => [
                    ['time' => '09:00', 'activity' => 'Hotel Pickup', 'location' => 'Your Hotel'],
                    ['time' => '09:30', 'activity' => 'Canggu — explore Batu Bolong beach and the famous surf breaks', 'location' => 'Canggu, Badung'],
                    ['time' => '11:00', 'activity' => 'Seminyak — browse designer boutiques and beach clubs on Jalan Kayu Aya', 'location' => 'Seminyak, Badung'],
                    ['time' => '12:30', 'activity' => 'Lunch at a beachfront restaurant of your choice (own expense)', 'location' => 'Seminyak or Legian'],
                    ['time' => '14:00', 'activity' => 'Legian & Kuta — walk along the famous beach and visit local shops', 'location' => 'Legian, Badung'],
                    ['time' => '15:30', 'activity' => 'Nusa Dua — visit the pristine white-sand beach and luxury resort area', 'location' => 'Nusa Dua, Badung'],
                    ['time' => '17:00', 'activity' => 'Hotel Drop Off', 'location' => 'Your Hotel'],
                ],
                'includes' => ['Professional driver', 'Fuel', 'Mineral water (1 bottle)', 'Parking fees', 'Air-conditioned vehicle'],
                'excludes' => ['Meals & drinks', 'Personal shopping', 'Beach club entrance fees', 'Personal expenses'],
                'image' => '/assets/images/tour/south-bali.jpg',
                'gallery' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($packages as $packageData) {
            TourPackage::updateOrCreate(
                ['slug' => $packageData['slug']],
                $packageData
            );
        }
    }
}
