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
        Schema::create('driver_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('driver_article_id')->nullable()->constrained()->nullOnDelete();
            // Exactly one of these two is set, depending on which booking
            // flow was referred — enforced in DriverReferralAttribution,
            // not at the DB level.
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('activity_booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('commission_amount', 10, 2);
            $table->string('status')->default('pending'); // pending|paid|void
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_referrals');
    }
};
