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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('vehicle_category_id')->nullable()->after('vehicle_id')->constrained('vehicle_categories')->nullOnDelete();
            $table->json('extras')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['vehicle_category_id']);
            $table->dropColumn(['vehicle_category_id', 'extras']);
        });
    }
};
