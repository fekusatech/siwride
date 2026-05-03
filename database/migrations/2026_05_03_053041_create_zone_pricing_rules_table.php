<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('zone_pricing_rules')) {
            return;
        }

        Schema::create('zone_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('dropoff_zone_id')->constrained('zones')->cascadeOnDelete();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('price_per_km', 12, 2)->default(0);
            $table->decimal('minimum_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['pickup_zone_id', 'dropoff_zone_id'], 'zpr_pickup_dropoff_unique');
            $table->index(['is_active', 'pickup_zone_id', 'dropoff_zone_id'], 'zpr_active_route_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_pricing_rules');
    }
};
