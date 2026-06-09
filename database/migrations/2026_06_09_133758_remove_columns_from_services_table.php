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
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['icon', 'badge', 'highlights', 'cta', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('icon')->nullable();
            $table->string('badge')->nullable();
            $table->json('highlights')->nullable();
            $table->string('cta')->nullable();
            $table->integer('sort_order')->default(0);
        });
    }
};
