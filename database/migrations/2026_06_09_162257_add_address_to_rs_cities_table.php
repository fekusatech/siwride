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
        Schema::table('rs_cities', function (Blueprint $table) {
            $table->text('address')->nullable()->after('name');
            $table->dropUnique('rs_cities_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rs_cities', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->unique('name', 'rs_cities_name_unique');
        });
    }
};
