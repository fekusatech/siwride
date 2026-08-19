<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->string('booking_type', 50)->default('service')->after('booking_id');
        });

        // Drop the driver_service_bookings FK so activity bookings can be stored too
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->dropForeign('voucher_redemptions_booking_id_foreign');
            $table->dropUnique('voucher_redemptions_voucher_id_booking_id_unique');
        });

        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->unique(['voucher_id', 'booking_id', 'booking_type'], 'voucher_redemptions_voucher_booking_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->dropUnique('voucher_redemptions_voucher_booking_type_unique');
            $table->dropColumn('booking_type');
        });

        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->foreign('booking_id', 'voucher_redemptions_booking_id_foreign')
                ->references('id')
                ->on('driver_service_bookings')
                ->onDelete('cascade');
            $table->unique(['voucher_id', 'booking_id'], 'voucher_redemptions_voucher_id_booking_id_unique');
        });
    }
};
