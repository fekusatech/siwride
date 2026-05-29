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
            $table->integer('luggage_capacity')->nullable()->after('capacity');
            $table->integer('passenger_capacity')->nullable()->after('luggage_capacity');
            $table->json('advantages')->nullable()->after('passenger_capacity');
            $table->decimal('base_price', 15, 2)->default(0)->after('advantages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->dropColumn(['luggage_capacity', 'passenger_capacity', 'advantages', 'base_price']);
        });
    }
};
