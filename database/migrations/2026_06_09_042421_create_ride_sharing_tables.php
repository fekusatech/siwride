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
        Schema::create('ride_sharing_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('area')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ride_sharing_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('departure_time'); // e.g. "08:00"
            $table->string('label');          // e.g. "Morning – 08:00"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_sharing_schedules');
        Schema::dropIfExists('ride_sharing_locations');
    }
};
