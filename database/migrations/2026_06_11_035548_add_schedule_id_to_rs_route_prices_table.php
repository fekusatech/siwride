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
        // 1. First, we need to deal with the unique index that MySQL refuses to drop
        // because it thinks it's needed for the `route_id` foreign key.
        // We will drop the foreign key first, drop the unique index, and then recreate the FK.
        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->dropForeign('rs_route_prices_route_id_foreign');
            $table->dropUnique('rs_route_prices_route_id_from_city_id_to_city_id_unique');
        });

        // 2. Add the schedule_id column and drop estimated_minutes
        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->dropColumn('estimated_minutes');

            $table->foreignId('schedule_id')
                ->nullable()
                ->after('route_id')
                ->constrained('rs_schedules')
                ->cascadeOnDelete();
        });

        // 3. Re-add the route_id foreign key constraint and add the new unique constraint
        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->foreign('route_id')->references('id')->on('rs_routes')->cascadeOnDelete();
            $table->unique(['route_id', 'schedule_id', 'from_city_id', 'to_city_id'], 'rs_route_prices_schedule_unique');
        });

        // 4. Remove old orphaned data and enforce non-nullable
        DB::table('rs_route_prices')->whereNull('schedule_id')->delete();

        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rs_route_prices', function (Blueprint $table) {
            $table->dropUnique('rs_route_prices_schedule_unique');

            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');

            $table->integer('estimated_minutes')->nullable()->after('price');
            $table->unique(['route_id', 'from_city_id', 'to_city_id']);
        });
    }
};
