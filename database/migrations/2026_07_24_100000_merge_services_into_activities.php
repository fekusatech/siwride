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
        Schema::table('activities', function (Blueprint $table) {
            $table->string('href')->nullable()->after('image');
            $table->decimal('price_per_pax', 10, 2)->nullable()->change();
            $table->integer('min_pax')->default(1)->nullable()->change();
        });

        Schema::dropIfExists('services');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('href')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('href');
            $table->decimal('price_per_pax', 10, 2)->nullable(false)->change();
        });
    }
};
