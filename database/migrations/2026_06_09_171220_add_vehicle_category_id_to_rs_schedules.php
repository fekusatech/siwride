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
            $table->foreignId('vehicle_category_id')->nullable()->after('route_id')->constrained('vehicle_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rs_schedules', function (Blueprint $table) {
            $table->dropForeign(['vehicle_category_id']);
            $table->dropColumn('vehicle_category_id');
        });
    }
};
