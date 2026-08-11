<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('driver_referrals', 'booking_key')) {
            Schema::table('driver_referrals', function (Blueprint $table) {
                $table->string('booking_key')->nullable()->after('driver_article_id');
            });
        }

        DB::table('driver_referrals')
            ->whereNull('booking_key')
            ->get(['id', 'order_id', 'activity_booking_id'])
            ->each(function (object $referral): void {
                $key = $referral->order_id
                    ? 'order:'.$referral->order_id
                    : ($referral->activity_booking_id ? 'activity:'.$referral->activity_booking_id : null);

                if ($key) {
                    DB::table('driver_referrals')
                        ->where('id', $referral->id)
                        ->update(['booking_key' => $key]);
                }
            });

        if (! Schema::hasIndex('driver_referrals', 'driver_referrals_booking_key_unique')) {
            Schema::table('driver_referrals', function (Blueprint $table) {
                $table->unique('booking_key');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('driver_referrals', 'booking_key')) {
            Schema::table('driver_referrals', function (Blueprint $table) {
                $table->dropUnique('driver_referrals_booking_key_unique');
                $table->dropColumn('booking_key');
            });
        }
    }
};
