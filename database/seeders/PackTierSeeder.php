<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\PackTier;
use Illuminate\Database\Seeder;

class PackTierSeeder extends Seeder
{
    /**
     * Seed pack tiers: global defaults + per-activity overrides.
     */
    public function run(): void
    {
        $globalTiers = [
            [
                'label' => 'Group Kecil',
                'min_pax' => 4,
                'max_pax' => 5,
                'discount_type' => PackTier::TYPE_PERCENT,
                'discount_value' => 10,
                'sort_order' => 1,
            ],
            [
                'label' => 'Group Besar',
                'min_pax' => 6,
                'max_pax' => null,
                'discount_type' => PackTier::TYPE_PERCENT,
                'discount_value' => 15,
                'sort_order' => 2,
            ],
        ];

        foreach ($globalTiers as $tier) {
            PackTier::updateOrCreate(
                ['activity_id' => null, 'label' => $tier['label']],
                $tier
            );
        }

        $perActivity = [
            'tour' => [
                ['label' => 'Group Tour 6+', 'min_pax' => 6, 'max_pax' => null, 'discount_type' => PackTier::TYPE_PERCENT, 'discount_value' => 20, 'sort_order' => 1],
            ],
            'sharing-ride' => [
                ['label' => 'Sharing 8+', 'min_pax' => 8, 'max_pax' => null, 'discount_type' => PackTier::TYPE_FLAT, 'discount_value' => 25000, 'sort_order' => 1],
            ],
        ];

        foreach ($perActivity as $slug => $tiers) {
            $activity = Activity::where('slug', $slug)->first();

            if (! $activity) {
                continue;
            }

            foreach ($tiers as $tier) {
                PackTier::updateOrCreate(
                    ['activity_id' => $activity->id, 'label' => $tier['label']],
                    $tier
                );
            }
        }
    }
}
