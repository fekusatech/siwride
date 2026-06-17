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
            $table->string('trip_type', 20)->default('one_way')->after('is_shared');
            $table->date('return_date')->nullable()->after('trip_type');
            $table->string('return_time', 10)->nullable()->after('return_date');
            $table->boolean('is_return_trip')->default(false)->after('return_time');
            $table->unsignedBigInteger('linked_order_id')->nullable()->after('is_return_trip');

            $table->foreign('linked_order_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['linked_order_id']);
            $table->dropColumn(['trip_type', 'return_date', 'return_time', 'is_return_trip', 'linked_order_id']);
        });
    }
};
