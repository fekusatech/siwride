<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds per-km pricing to vehicle_categories and typical route distance
     * to zone_pricing_rules so the booking price can be calculated as:
     *
     *   price = max(zone.minimum_price, zone.base_price + zone.distance_km × vehicle.price_per_km)
     */
    public function up(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->decimal('price_per_km', 10, 2)->default(0)->after('base_price')
                ->comment('IDR per km for this vehicle type');
        });

        Schema::table('zone_pricing_rules', function (Blueprint $table) {
            $table->decimal('distance_km', 8, 2)->default(0)->after('minimum_price')
                ->comment('Typical route distance in km for this zone pair');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->dropColumn('price_per_km');
        });

        Schema::table('zone_pricing_rules', function (Blueprint $table) {
            $table->dropColumn('distance_km');
        });
    }
};
