<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_referrals', function (Blueprint $table) {
            $table->renameColumn('driver_article_id', 'driver_service_id');
        });

        Schema::table('driver_referrals', function (Blueprint $table) {
            $table->foreignId('driver_service_booking_id')->nullable()->after('activity_booking_id')
                ->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('driver_referrals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('driver_service_booking_id');
        });

        Schema::table('driver_referrals', function (Blueprint $table) {
            $table->renameColumn('driver_service_id', 'driver_article_id');
        });
    }
};
