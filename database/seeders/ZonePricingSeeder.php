<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\ZonePricingRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds realistic zone-to-zone pricing rules for Bali transfers.
 *
 * Pricing model:
 *   price = max(minimum_price, base_price + distance_km × vehicle.price_per_km)
 *
 * base_price   = fixed booking/service fee for the route (covers tolls, airport fees, etc.)
 * distance_km  = typical road distance for the zone pair
 * minimum_price = floor price (so short trips are never loss-making)
 *
 * The vehicle.price_per_km (set in VehicleCategorySeeder) is the IDR/km rate
 * that gets multiplied by distance_km to produce the variable portion of the fare.
 */
class ZonePricingSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ZonePricingRule::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /** @var array<string, int> $zones  name → id */
        $zones = Zone::all()->pluck('id', 'name')->toArray();

        if (empty($zones)) {
            $this->command->warn('No zones found — run ZoneSeeder first.');

            return;
        }

        /**
         * Route definitions.
         *
         * [pickup_zone, dropoff_zone, base_price, distance_km, minimum_price]
         *
         * base_price:    fixed route fee in IDR (airport tax, parking, toll, etc.)
         * distance_km:   realistic road distance between zone centres (one-way)
         * minimum_price: price floor in IDR — guarantees a viable minimum fare
         *
         * Prices are symmetric (A→B == B→A), so we only define one direction
         * and mirror it below.
         */
        $routes = [
            // ── Airport & Tuban routes ────────────────────────────────────────
            ['Airport & Tuban',      'Kuta',               25_000,  8,   75_000],
            ['Airport & Tuban',      'Legian',              25_000, 11,   90_000],
            ['Airport & Tuban',      'Seminyak',            25_000, 13,   95_000],
            ['Airport & Tuban',      'Canggu & Berawa',     25_000, 21,  120_000],
            ['Airport & Tuban',      'Jimbaran',            25_000, 10,   80_000],
            ['Airport & Tuban',      'Nusa Dua & Benoa',    30_000, 18,  110_000],
            ['Airport & Tuban',      'Uluwatu & Pecatu',    25_000, 22,  130_000],
            ['Airport & Tuban',      'Denpasar',            25_000, 14,   90_000],
            ['Airport & Tuban',      'Sanur',               25_000, 19,  110_000],
            ['Airport & Tuban',      'Ubud',                25_000, 38,  180_000],
            ['Airport & Tuban',      'Tegallalang & Gianyar', 25_000, 48, 220_000],
            ['Airport & Tuban',      'Tanah Lot & Kediri',  25_000, 28,  150_000],
            ['Airport & Tuban',      'Bedugul & Baturiti',  25_000, 65,  280_000],
            ['Airport & Tuban',      'Kintamani & Batur',   25_000, 72,  320_000],
            ['Airport & Tuban',      'Lempuyang & Sidemen', 25_000, 85,  380_000],
            ['Airport & Tuban',      'Amed & Tulamben',     25_000, 95,  430_000],
            ['Airport & Tuban',      'Lovina & Singaraja',  25_000, 95,  430_000],

            // ── Kuta / Legian / Seminyak routes ──────────────────────────────
            ['Kuta',                 'Legian',               0,      4,   40_000],
            ['Kuta',                 'Seminyak',              0,      6,   50_000],
            ['Kuta',                 'Canggu & Berawa',       0,     14,   80_000],
            ['Kuta',                 'Jimbaran',              0,      9,   70_000],
            ['Kuta',                 'Nusa Dua & Benoa',      0,     16,   90_000],
            ['Kuta',                 'Uluwatu & Pecatu',      0,     20,  110_000],
            ['Kuta',                 'Denpasar',              0,     10,   70_000],
            ['Kuta',                 'Sanur',                 0,     15,   85_000],
            ['Kuta',                 'Ubud',                  0,     36,  170_000],
            ['Kuta',                 'Tanah Lot & Kediri',    0,     22,  120_000],
            ['Legian',               'Seminyak',              0,      3,   40_000],
            ['Legian',               'Canggu & Berawa',       0,     11,   70_000],
            ['Legian',               'Jimbaran',              0,     12,   80_000],
            ['Legian',               'Nusa Dua & Benoa',      0,     18,  100_000],
            ['Legian',               'Ubud',                  0,     37,  175_000],
            ['Seminyak',             'Canggu & Berawa',       0,      8,   55_000],
            ['Seminyak',             'Jimbaran',              0,     14,   85_000],
            ['Seminyak',             'Nusa Dua & Benoa',      0,     20,  110_000],
            ['Seminyak',             'Uluwatu & Pecatu',      0,     22,  120_000],
            ['Seminyak',             'Ubud',                  0,     38,  180_000],
            ['Seminyak',             'Tanah Lot & Kediri',    0,     16,   95_000],

            // ── Canggu / Tanah Lot ────────────────────────────────────────────
            ['Canggu & Berawa',      'Jimbaran',              0,     22,  120_000],
            ['Canggu & Berawa',      'Nusa Dua & Benoa',      0,     28,  150_000],
            ['Canggu & Berawa',      'Uluwatu & Pecatu',      0,     30,  160_000],
            ['Canggu & Berawa',      'Denpasar',              0,     18,  100_000],
            ['Canggu & Berawa',      'Ubud',                  0,     42,  200_000],
            ['Canggu & Berawa',      'Tanah Lot & Kediri',    0,     10,   65_000],
            ['Tanah Lot & Kediri',   'Ubud',                  0,     42,  200_000],
            ['Tanah Lot & Kediri',   'Bedugul & Baturiti',    0,     40,  190_000],

            // ── South / Nusa Dua / Jimbaran / Uluwatu ────────────────────────
            ['Jimbaran',             'Nusa Dua & Benoa',      0,      9,   60_000],
            ['Jimbaran',             'Uluwatu & Pecatu',      0,     13,   80_000],
            ['Jimbaran',             'Ubud',                  0,     43,  200_000],
            ['Nusa Dua & Benoa',     'Uluwatu & Pecatu',      0,     15,   90_000],
            ['Nusa Dua & Benoa',     'Denpasar',              0,     14,   85_000],
            ['Nusa Dua & Benoa',     'Sanur',                 0,     12,   75_000],
            ['Nusa Dua & Benoa',     'Ubud',                  0,     42,  200_000],
            ['Uluwatu & Pecatu',     'Ubud',                  0,     55,  250_000],

            // ── Denpasar / Sanur ──────────────────────────────────────────────
            ['Denpasar',             'Sanur',                 0,      7,   50_000],
            ['Denpasar',             'Ubud',                  0,     28,  150_000],
            ['Denpasar',             'Tegallalang & Gianyar', 0,     38,  180_000],

            // ── Ubud area ─────────────────────────────────────────────────────
            ['Sanur',                'Ubud',                  0,     30,  150_000],
            ['Ubud',                 'Tegallalang & Gianyar', 0,     12,   70_000],
            ['Ubud',                 'Kintamani & Batur',     0,     32,  165_000],
            ['Ubud',                 'Lempuyang & Sidemen',   0,     52,  250_000],
            ['Ubud',                 'Bedugul & Baturiti',    0,     45,  220_000],

            // ── North / East Bali ─────────────────────────────────────────────
            ['Bedugul & Baturiti',   'Kintamani & Batur',     0,     42,  200_000],
            ['Bedugul & Baturiti',   'Lovina & Singaraja',    0,     30,  155_000],
            ['Kintamani & Batur',    'Lempuyang & Sidemen',   0,     55,  260_000],
            ['Kintamani & Batur',    'Amed & Tulamben',       0,     60,  280_000],
            ['Lempuyang & Sidemen',  'Amed & Tulamben',       0,     18,   95_000],
            ['Lovina & Singaraja',   'Kintamani & Batur',     0,     55,  260_000],
        ];

        $rules = [];

        // 1. Generate intra-zone rules (same pickup and dropoff)
        foreach ($zones as $zoneName => $zoneId) {
            $rules[] = [
                'pickup_zone_id' => $zoneId,
                'dropoff_zone_id' => $zoneId,
                'base_price' => 0,
                'price_per_km' => 0,
                'minimum_price' => 50_000, // standard minimum for intra-zone trips
                'distance_km' => 5, // fallback distance
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2. Generate cross-zone rules
        foreach ($routes as [$pickupName, $dropoffName, $base, $dist, $min]) {
            $pickupId = $zones[$pickupName] ?? null;
            $dropoffId = $zones[$dropoffName] ?? null;

            if (! $pickupId || ! $dropoffId) {
                $this->command->warn("Zone not found: '{$pickupName}' or '{$dropoffName}' — skipping.");

                continue;
            }

            // Forward direction (A → B)
            $rules[] = [
                'pickup_zone_id' => $pickupId,
                'dropoff_zone_id' => $dropoffId,
                'base_price' => $base,
                'price_per_km' => 0,   // not used; vehicle.price_per_km drives per-km cost
                'minimum_price' => $min,
                'distance_km' => $dist,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Reverse direction (B → A) — same cost
            $rules[] = [
                'pickup_zone_id' => $dropoffId,
                'dropoff_zone_id' => $pickupId,
                'base_price' => $base,
                'price_per_km' => 0,
                'minimum_price' => $min,
                'distance_km' => $dist,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch-insert for speed
        foreach (array_chunk($rules, 50) as $chunk) {
            ZonePricingRule::insert($chunk);
        }

        $this->command->info('ZonePricingSeeder: '.count($rules).' rules seeded ('.($rules ? count($rules) / 2 : 0).' routes × 2 directions).');
    }
}
