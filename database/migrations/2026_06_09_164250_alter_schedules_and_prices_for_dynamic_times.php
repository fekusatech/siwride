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
        Schema::table('rs_schedules', function (Blueprint $table) {
            $table->dropColumn('departure_time');
            $table->json('departure_times')->nullable()->after('days');
        });

        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->integer('estimated_minutes')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rs_schedules', function (Blueprint $table) {
            $table->dropColumn('departure_times');
            $table->string('departure_time')->nullable()->after('days');
        });

        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->dropColumn('estimated_minutes');
        });
    }
};
