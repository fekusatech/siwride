<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            $table->string('voucher_code', 50)->nullable()->after('total_price');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('voucher_code');
            $table->decimal('subtotal', 14, 2)->nullable()->after('discount_amount');
            $table->decimal('dp_percent', 5, 2)->default(0)->after('subtotal');
            $table->decimal('dp_amount', 14, 2)->default(0)->after('dp_percent');
            $table->decimal('remaining_cash', 14, 2)->default(0)->after('dp_amount');
        });
    }

    public function down(): void
    {
        Schema::table('activity_bookings', function (Blueprint $table) {
            $table->dropColumn(['voucher_code', 'discount_amount', 'subtotal', 'dp_percent', 'dp_amount', 'remaining_cash']);
        });
    }
};
