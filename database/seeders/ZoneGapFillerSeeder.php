<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\ZonePricingRule;
use Illuminate\Database\Seeder;

/**
 * Non-destructive counterpart to ZoneSeeder/ZonePricingSeeder: fills in
 * whatever zones/routes are missing without ever truncating anything, so an
 * environment with hand-drawn zones and real pricing rules already in place
 * keeps them untouched. Every write is firstOrCreate, keyed on the thing
 * that must stay unique (zone name; pickup+dropoff zone pair) - re-running
 * this is always safe.
 *
 * $zoneNameAliases lets an existing zone under a different name stand in
 * for one of the standard 18 (e.g. a real "Airport Bali" zone instead of
 * creating a second, overlapping "Airport & Tuban") - its polygon is never
 * touched, only its id gets used for the new pricing rules.
 */
class ZoneGapFillerSeeder extends Seeder
{
    /** @var array<string, string> standard zone name => existing zone name to reuse instead */
    private array $zoneNameAliases = [
        'Airport & Tuban' => 'Airport Bali',
    ];

    public function run(): void
    {
        $zoneSpecs = [
            ['name' => 'Airport & Tuban', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.7400, 'lng' => 115.1600], ['lat' => -8.7400, 'lng' => 115.1920],
                ['lat' => -8.7680, 'lng' => 115.1920], ['lat' => -8.7680, 'lng' => 115.1600],
            ]],
            ['name' => 'Kuta', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.7050, 'lng' => 115.1580], ['lat' => -8.7050, 'lng' => 115.1900],
                ['lat' => -8.7400, 'lng' => 115.1900], ['lat' => -8.7400, 'lng' => 115.1580],
            ]],
            ['name' => 'Legian', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.6850, 'lng' => 115.1530], ['lat' => -8.6850, 'lng' => 115.1820],
                ['lat' => -8.7050, 'lng' => 115.1820], ['lat' => -8.7050, 'lng' => 115.1530],
            ]],
            ['name' => 'Seminyak', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.6620, 'lng' => 115.1480], ['lat' => -8.6620, 'lng' => 115.1760],
                ['lat' => -8.6850, 'lng' => 115.1760], ['lat' => -8.6850, 'lng' => 115.1480],
            ]],
            ['name' => 'Canggu & Berawa', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.6280, 'lng' => 115.1150], ['lat' => -8.6280, 'lng' => 115.1680],
                ['lat' => -8.6620, 'lng' => 115.1680], ['lat' => -8.6620, 'lng' => 115.1150],
            ]],
            ['name' => 'Jimbaran', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.7680, 'lng' => 115.1450], ['lat' => -8.7680, 'lng' => 115.1720],
                ['lat' => -8.8050, 'lng' => 115.1720], ['lat' => -8.8050, 'lng' => 115.1450],
            ]],
            ['name' => 'Nusa Dua & Benoa', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.7780, 'lng' => 115.2150], ['lat' => -8.7780, 'lng' => 115.2620],
                ['lat' => -8.8280, 'lng' => 115.2620], ['lat' => -8.8280, 'lng' => 115.2150],
            ]],
            ['name' => 'Uluwatu & Pecatu', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.8050, 'lng' => 115.0750], ['lat' => -8.8050, 'lng' => 115.1450],
                ['lat' => -8.8700, 'lng' => 115.1450], ['lat' => -8.8700, 'lng' => 115.0750],
            ]],
            ['name' => 'Denpasar', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.6250, 'lng' => 115.1900], ['lat' => -8.6250, 'lng' => 115.2500],
                ['lat' => -8.6980, 'lng' => 115.2500], ['lat' => -8.6980, 'lng' => 115.1900],
            ]],
            ['name' => 'Sanur', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.6800, 'lng' => 115.2480], ['lat' => -8.6800, 'lng' => 115.2820],
                ['lat' => -8.7350, 'lng' => 115.2820], ['lat' => -8.7350, 'lng' => 115.2480],
            ]],
            ['name' => 'Ubud', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.4700, 'lng' => 115.2300], ['lat' => -8.4700, 'lng' => 115.3050],
                ['lat' => -8.5450, 'lng' => 115.3050], ['lat' => -8.5450, 'lng' => 115.2300],
            ]],
            ['name' => 'Tegallalang & Gianyar', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.3900, 'lng' => 115.2600], ['lat' => -8.3900, 'lng' => 115.3600],
                ['lat' => -8.4700, 'lng' => 115.3600], ['lat' => -8.4700, 'lng' => 115.2600],
            ]],
            ['name' => 'Tanah Lot & Kediri', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.5900, 'lng' => 115.0500], ['lat' => -8.5900, 'lng' => 115.1200],
                ['lat' => -8.6450, 'lng' => 115.1200], ['lat' => -8.6450, 'lng' => 115.0500],
            ]],
            ['name' => 'Bedugul & Baturiti', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.2350, 'lng' => 115.1300], ['lat' => -8.2350, 'lng' => 115.2200],
                ['lat' => -8.3200, 'lng' => 115.2200], ['lat' => -8.3200, 'lng' => 115.1300],
            ]],
            ['name' => 'Kintamani & Batur', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.1900, 'lng' => 115.3000], ['lat' => -8.1900, 'lng' => 115.4350],
                ['lat' => -8.3100, 'lng' => 115.4350], ['lat' => -8.3100, 'lng' => 115.3000],
            ]],
            ['name' => 'Lempuyang & Sidemen', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.3600, 'lng' => 115.5500], ['lat' => -8.3600, 'lng' => 115.6550],
                ['lat' => -8.4400, 'lng' => 115.6550], ['lat' => -8.4400, 'lng' => 115.5500],
            ]],
            ['name' => 'Amed & Tulamben', 'is_active' => true, 'coordinates' => [
                ['lat' => -8.2900, 'lng' => 115.5700], ['lat' => -8.2900, 'lng' => 115.6800],
                ['lat' => -8.3600, 'lng' => 115.6800], ['lat' => -8.3600, 'lng' => 115.5700],
            ]],
            ['name' => 'Lovina & Singaraja', 'is_active' => false, 'coordinates' => [
                ['lat' => -8.1300, 'lng' => 114.9800], ['lat' => -8.1300, 'lng' => 115.1200],
                ['lat' => -8.2000, 'lng' => 115.1200], ['lat' => -8.2000, 'lng' => 114.9800],
            ]],
        ];

        /** @var array<string, int> name → id, resolved through the alias map */
        $zoneIds = [];
        foreach ($zoneSpecs as $spec) {
            $lookupName = $this->zoneNameAliases[$spec['name']] ?? $spec['name'];
            $zone = Zone::firstOrCreate(['name' => $lookupName], $spec);
            $zoneIds[$spec['name']] = $zone->id;
        }

        $routes = [
            ['Airport & Tuban', 'Kuta', 25_000, 8, 75_000],
            ['Airport & Tuban', 'Legian', 25_000, 11, 90_000],
            ['Airport & Tuban', 'Seminyak', 25_000, 13, 95_000],
            ['Airport & Tuban', 'Canggu & Berawa', 25_000, 21, 120_000],
            ['Airport & Tuban', 'Jimbaran', 25_000, 10, 80_000],
            ['Airport & Tuban', 'Nusa Dua & Benoa', 30_000, 18, 110_000],
            ['Airport & Tuban', 'Uluwatu & Pecatu', 25_000, 22, 130_000],
            ['Airport & Tuban', 'Denpasar', 25_000, 14, 90_000],
            ['Airport & Tuban', 'Sanur', 25_000, 19, 110_000],
            ['Airport & Tuban', 'Ubud', 25_000, 38, 180_000],
            ['Airport & Tuban', 'Tegallalang & Gianyar', 25_000, 48, 220_000],
            ['Airport & Tuban', 'Tanah Lot & Kediri', 25_000, 28, 150_000],
            ['Airport & Tuban', 'Bedugul & Baturiti', 25_000, 65, 280_000],
            ['Airport & Tuban', 'Kintamani & Batur', 25_000, 72, 320_000],
            ['Airport & Tuban', 'Lempuyang & Sidemen', 25_000, 85, 380_000],
            ['Airport & Tuban', 'Amed & Tulamben', 25_000, 95, 430_000],
            ['Airport & Tuban', 'Lovina & Singaraja', 25_000, 95, 430_000],
            ['Kuta', 'Legian', 0, 4, 40_000],
            ['Kuta', 'Seminyak', 0, 6, 50_000],
            ['Kuta', 'Canggu & Berawa', 0, 14, 80_000],
            ['Kuta', 'Jimbaran', 0, 9, 70_000],
            ['Kuta', 'Nusa Dua & Benoa', 0, 16, 90_000],
            ['Kuta', 'Uluwatu & Pecatu', 0, 20, 110_000],
            ['Kuta', 'Denpasar', 0, 10, 70_000],
            ['Kuta', 'Sanur', 0, 15, 85_000],
            ['Kuta', 'Ubud', 0, 36, 170_000],
            ['Kuta', 'Tanah Lot & Kediri', 0, 22, 120_000],
            ['Legian', 'Seminyak', 0, 3, 40_000],
            ['Legian', 'Canggu & Berawa', 0, 11, 70_000],
            ['Legian', 'Jimbaran', 0, 12, 80_000],
            ['Legian', 'Nusa Dua & Benoa', 0, 18, 100_000],
            ['Legian', 'Ubud', 0, 37, 175_000],
            ['Seminyak', 'Canggu & Berawa', 0, 8, 55_000],
            ['Seminyak', 'Jimbaran', 0, 14, 85_000],
            ['Seminyak', 'Nusa Dua & Benoa', 0, 20, 110_000],
            ['Seminyak', 'Uluwatu & Pecatu', 0, 22, 120_000],
            ['Seminyak', 'Ubud', 0, 38, 180_000],
            ['Seminyak', 'Tanah Lot & Kediri', 0, 16, 95_000],
            ['Canggu & Berawa', 'Jimbaran', 0, 22, 120_000],
            ['Canggu & Berawa', 'Nusa Dua & Benoa', 0, 28, 150_000],
            ['Canggu & Berawa', 'Uluwatu & Pecatu', 0, 30, 160_000],
            ['Canggu & Berawa', 'Denpasar', 0, 18, 100_000],
            ['Canggu & Berawa', 'Ubud', 0, 42, 200_000],
            ['Canggu & Berawa', 'Tanah Lot & Kediri', 0, 10, 65_000],
            ['Tanah Lot & Kediri', 'Ubud', 0, 42, 200_000],
            ['Tanah Lot & Kediri', 'Bedugul & Baturiti', 0, 40, 190_000],
            ['Jimbaran', 'Nusa Dua & Benoa', 0, 9, 60_000],
            ['Jimbaran', 'Uluwatu & Pecatu', 0, 13, 80_000],
            ['Jimbaran', 'Ubud', 0, 43, 200_000],
            ['Nusa Dua & Benoa', 'Uluwatu & Pecatu', 0, 15, 90_000],
            ['Nusa Dua & Benoa', 'Denpasar', 0, 14, 85_000],
            ['Nusa Dua & Benoa', 'Sanur', 0, 12, 75_000],
            ['Nusa Dua & Benoa', 'Ubud', 0, 42, 200_000],
            ['Uluwatu & Pecatu', 'Ubud', 0, 55, 250_000],
            ['Denpasar', 'Sanur', 0, 7, 50_000],
            ['Denpasar', 'Ubud', 0, 28, 150_000],
            ['Denpasar', 'Tegallalang & Gianyar', 0, 38, 180_000],
            ['Sanur', 'Ubud', 0, 30, 150_000],
            ['Ubud', 'Tegallalang & Gianyar', 0, 12, 70_000],
            ['Ubud', 'Kintamani & Batur', 0, 32, 165_000],
            ['Ubud', 'Lempuyang & Sidemen', 0, 52, 250_000],
            ['Ubud', 'Bedugul & Baturiti', 0, 45, 220_000],
            ['Bedugul & Baturiti', 'Kintamani & Batur', 0, 42, 200_000],
            ['Bedugul & Baturiti', 'Lovina & Singaraja', 0, 30, 155_000],
            ['Kintamani & Batur', 'Lempuyang & Sidemen', 0, 55, 260_000],
            ['Kintamani & Batur', 'Amed & Tulamben', 0, 60, 280_000],
            ['Lempuyang & Sidemen', 'Amed & Tulamben', 0, 18, 95_000],
            ['Lovina & Singaraja', 'Kintamani & Batur', 0, 55, 260_000],
        ];

        $created = 0;
        $skipped = 0;

        // Intra-zone (same pickup/dropoff) rules, one per zone.
        foreach ($zoneIds as $zoneId) {
            $rule = ZonePricingRule::firstOrCreate(
                ['pickup_zone_id' => $zoneId, 'dropoff_zone_id' => $zoneId],
                ['base_price' => 0, 'price_per_km' => 0, 'minimum_price' => 50_000, 'distance_km' => 5, 'is_active' => true],
            );
            $rule->wasRecentlyCreated ? $created++ : $skipped++;
        }

        foreach ($routes as [$pickupName, $dropoffName, $base, $dist, $min]) {
            $pickupId = $zoneIds[$pickupName];
            $dropoffId = $zoneIds[$dropoffName];

            foreach ([[$pickupId, $dropoffId], [$dropoffId, $pickupId]] as [$from, $to]) {
                $rule = ZonePricingRule::firstOrCreate(
                    ['pickup_zone_id' => $from, 'dropoff_zone_id' => $to],
                    ['base_price' => $base, 'price_per_km' => 0, 'minimum_price' => $min, 'distance_km' => $dist, 'is_active' => true],
                );
                $rule->wasRecentlyCreated ? $created++ : $skipped++;
            }
        }

        $this->command?->info("ZoneGapFillerSeeder: {$created} pricing rules created, {$skipped} already existed and were left alone.");
    }
}
