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
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('highlights')->nullable();
            $table->unsignedInteger('price_per_pax');
            $table->unsignedInteger('duration_hours');
            $table->unsignedInteger('max_pax')->default(8);
            $table->unsignedInteger('min_pax')->default(1);
            $table->json('destinations')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
