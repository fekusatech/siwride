<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'activity_bookings', 'driver_service_bookings'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->json('display_quote')->nullable());
        }
    }

    public function down(): void
    {
        foreach (['orders', 'activity_bookings', 'driver_service_bookings'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('display_quote'));
        }
    }
};
