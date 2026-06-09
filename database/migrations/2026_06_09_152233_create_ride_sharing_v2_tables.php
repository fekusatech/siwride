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
        // Drop the old tables
        Schema::dropIfExists('ride_sharing_schedules');
        Schema::dropIfExists('ride_sharing_locations');

        // Create the new structure
        Schema::create('rs_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('rs_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rs_route_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('rs_routes')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('rs_cities')->cascadeOnDelete();
            $table->integer('sequence');
            $table->timestamps();

            $table->unique(['route_id', 'sequence']);
            $table->unique(['route_id', 'city_id']); // City cannot appear twice in the same route
        });

        Schema::create('rs_route_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('rs_routes')->cascadeOnDelete();
            $table->foreignId('from_city_id')->constrained('rs_cities')->cascadeOnDelete();
            $table->foreignId('to_city_id')->constrained('rs_cities')->cascadeOnDelete();
            $table->integer('price');
            $table->timestamps();

            $table->unique(['route_id', 'from_city_id', 'to_city_id']);
        });

        Schema::create('rs_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('rs_routes')->cascadeOnDelete();
            $table->string('days', 100); // e.g., '1,2,3,4,5,6,7' for daily
            $table->time('departure_time');
            $table->integer('quota')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rs_schedules');
        Schema::dropIfExists('rs_route_prices');
        Schema::dropIfExists('rs_route_paths');
        Schema::dropIfExists('rs_routes');
        Schema::dropIfExists('rs_cities');

        // Revert to old tables
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
            $table->string('departure_time');
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
