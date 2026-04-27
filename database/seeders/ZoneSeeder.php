<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Seed realistic service zones for Bali.
     * Polygons are approximate rectangles/quadrilaterals covering each area.
     * Coordinates use {lat, lng} order as per the Zone model convention.
     */
    public function run(): void
    {
        $zones = [
            // ─────────────────────────────────────────────────────────
            // BADUNG REGENCY — Southern Tourist Corridor
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Airport & Tuban',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.7400, 'lng' => 115.1600],
                    ['lat' => -8.7400, 'lng' => 115.1920],
                    ['lat' => -8.7680, 'lng' => 115.1920],
                    ['lat' => -8.7680, 'lng' => 115.1600],
                ],
            ],
            [
                'name' => 'Kuta',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.7050, 'lng' => 115.1580],
                    ['lat' => -8.7050, 'lng' => 115.1900],
                    ['lat' => -8.7400, 'lng' => 115.1900],
                    ['lat' => -8.7400, 'lng' => 115.1580],
                ],
            ],
            [
                'name' => 'Legian',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.6850, 'lng' => 115.1530],
                    ['lat' => -8.6850, 'lng' => 115.1820],
                    ['lat' => -8.7050, 'lng' => 115.1820],
                    ['lat' => -8.7050, 'lng' => 115.1530],
                ],
            ],
            [
                'name' => 'Seminyak',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.6620, 'lng' => 115.1480],
                    ['lat' => -8.6620, 'lng' => 115.1760],
                    ['lat' => -8.6850, 'lng' => 115.1760],
                    ['lat' => -8.6850, 'lng' => 115.1480],
                ],
            ],
            [
                'name' => 'Canggu & Berawa',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.6280, 'lng' => 115.1150],
                    ['lat' => -8.6280, 'lng' => 115.1680],
                    ['lat' => -8.6620, 'lng' => 115.1680],
                    ['lat' => -8.6620, 'lng' => 115.1150],
                ],
            ],
            [
                'name' => 'Jimbaran',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.7680, 'lng' => 115.1450],
                    ['lat' => -8.7680, 'lng' => 115.1720],
                    ['lat' => -8.8050, 'lng' => 115.1720],
                    ['lat' => -8.8050, 'lng' => 115.1450],
                ],
            ],
            [
                'name' => 'Nusa Dua & Benoa',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.7780, 'lng' => 115.2150],
                    ['lat' => -8.7780, 'lng' => 115.2620],
                    ['lat' => -8.8280, 'lng' => 115.2620],
                    ['lat' => -8.8280, 'lng' => 115.2150],
                ],
            ],
            [
                'name' => 'Uluwatu & Pecatu',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.8050, 'lng' => 115.0750],
                    ['lat' => -8.8050, 'lng' => 115.1450],
                    ['lat' => -8.8700, 'lng' => 115.1450],
                    ['lat' => -8.8700, 'lng' => 115.0750],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // DENPASAR CITY
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Denpasar',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.6250, 'lng' => 115.1900],
                    ['lat' => -8.6250, 'lng' => 115.2500],
                    ['lat' => -8.6980, 'lng' => 115.2500],
                    ['lat' => -8.6980, 'lng' => 115.1900],
                ],
            ],
            [
                'name' => 'Sanur',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.6800, 'lng' => 115.2480],
                    ['lat' => -8.6800, 'lng' => 115.2820],
                    ['lat' => -8.7350, 'lng' => 115.2820],
                    ['lat' => -8.7350, 'lng' => 115.2480],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // GIANYAR REGENCY — Cultural Heart of Bali
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Ubud',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.4700, 'lng' => 115.2300],
                    ['lat' => -8.4700, 'lng' => 115.3050],
                    ['lat' => -8.5450, 'lng' => 115.3050],
                    ['lat' => -8.5450, 'lng' => 115.2300],
                ],
            ],
            [
                'name' => 'Tegallalang & Gianyar',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.3900, 'lng' => 115.2600],
                    ['lat' => -8.3900, 'lng' => 115.3600],
                    ['lat' => -8.4700, 'lng' => 115.3600],
                    ['lat' => -8.4700, 'lng' => 115.2600],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // TABANAN REGENCY — Western Temples & Nature
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Tanah Lot & Kediri',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.5900, 'lng' => 115.0500],
                    ['lat' => -8.5900, 'lng' => 115.1200],
                    ['lat' => -8.6450, 'lng' => 115.1200],
                    ['lat' => -8.6450, 'lng' => 115.0500],
                ],
            ],
            [
                'name' => 'Bedugul & Baturiti',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.2350, 'lng' => 115.1300],
                    ['lat' => -8.2350, 'lng' => 115.2200],
                    ['lat' => -8.3200, 'lng' => 115.2200],
                    ['lat' => -8.3200, 'lng' => 115.1300],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // BANGLI REGENCY — Volcanic Highland
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Kintamani & Batur',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.1900, 'lng' => 115.3000],
                    ['lat' => -8.1900, 'lng' => 115.4350],
                    ['lat' => -8.3100, 'lng' => 115.4350],
                    ['lat' => -8.3100, 'lng' => 115.3000],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // KARANGASEM REGENCY — Eastern Bali
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Lempuyang & Sidemen',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.3600, 'lng' => 115.5500],
                    ['lat' => -8.3600, 'lng' => 115.6550],
                    ['lat' => -8.4400, 'lng' => 115.6550],
                    ['lat' => -8.4400, 'lng' => 115.5500],
                ],
            ],
            [
                'name' => 'Amed & Tulamben',
                'is_active' => true,
                'coordinates' => [
                    ['lat' => -8.2900, 'lng' => 115.5700],
                    ['lat' => -8.2900, 'lng' => 115.6800],
                    ['lat' => -8.3600, 'lng' => 115.6800],
                    ['lat' => -8.3600, 'lng' => 115.5700],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // BULELENG REGENCY — North Bali (inactive by default)
            // ─────────────────────────────────────────────────────────
            [
                'name' => 'Lovina & Singaraja',
                'is_active' => false,
                'coordinates' => [
                    ['lat' => -8.1300, 'lng' => 114.9800],
                    ['lat' => -8.1300, 'lng' => 115.1200],
                    ['lat' => -8.2000, 'lng' => 115.1200],
                    ['lat' => -8.2000, 'lng' => 114.9800],
                ],
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }
}
