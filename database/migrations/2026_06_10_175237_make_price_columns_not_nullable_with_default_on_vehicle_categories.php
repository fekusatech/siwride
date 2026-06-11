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
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->decimal('base_price', 15, 2)->default(0)->change();
            $table->decimal('price_per_km', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            // Reverting to what they were (nullable/default depends on previous migrations)
            // Note: In 2026_05_29_135623, base_price was added with default 0.
            // In 2026_06_01_040210, price_per_km was added with default 0.
            // So they should already have defaults, but making sure they are NOT NULL.
            $table->decimal('base_price', 15, 2)->nullable()->change();
            $table->decimal('price_per_km', 10, 2)->nullable()->change();
        });
    }
};
