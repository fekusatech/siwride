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
        Schema::table('pack_tiers', function (Blueprint $table) {
            $table->foreignId('driver_service_id')
                ->nullable()
                ->after('activity_id')
                ->constrained('driver_services')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pack_tiers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('driver_service_id');
        });
    }
};
