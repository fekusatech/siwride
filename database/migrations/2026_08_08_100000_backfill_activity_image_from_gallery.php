<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Activity::whereNull('image')
            ->whereNotNull('gallery')
            ->get()
            ->each(function (Activity $activity) {
                $first = $activity->gallery[0] ?? null;

                if ($first) {
                    $activity->update(['image' => $first]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data backfill only — nothing to revert.
    }
};
