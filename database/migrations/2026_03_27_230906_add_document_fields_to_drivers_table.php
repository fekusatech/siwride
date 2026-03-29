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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('phone');
            $table->string('nik_image', 500)->nullable()->after('nik');
            $table->string('sim', 20)->nullable()->after('nik_image');
            $table->string('sim_image', 500)->nullable()->after('sim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['nik', 'nik_image', 'sim', 'sim_image']);
        });
    }
};
