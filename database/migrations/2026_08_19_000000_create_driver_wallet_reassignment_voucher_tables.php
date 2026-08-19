<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('driver_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('driver_wallets')->cascadeOnDelete();
            $table->enum('type', [
                'dp_payment',
                'dp_reversal',
                'platform_commission',
                'withdrawal',
                'admin_adjustment',
            ]);
            $table->decimal('amount', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('booking_code')->nullable();
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
            $table->index(['booking_code', 'type']);
        });

        Schema::create('driver_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_name');
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('driver_service_reassignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('driver_service_bookings')->cascadeOnDelete();
            $table->foreignId('from_driver_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('to_driver_id')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 14, 2);
            $table->decimal('min_spend', 14, 2)->default(0);
            $table->decimal('max_discount', 14, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->json('applies_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'valid_from', 'valid_until']);
        });

        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('driver_service_bookings')->cascadeOnDelete();
            $table->string('email');
            $table->decimal('discount_amount', 14, 2);
            $table->timestamps();

            $table->unique(['voucher_id', 'booking_id']);
            $table->index(['voucher_id', 'email']);
        });

        Schema::table('driver_service_bookings', function (Blueprint $table) {
            $table->foreignId('assigned_driver_id')->nullable()->after('driver_service_id')->constrained('users')->nullOnDelete();
            $table->enum('assignment_status', ['assigned', 'accepted', 'reassign_requested', 'reassigned'])->nullable()->after('assigned_driver_id');
            $table->timestamp('accepted_at')->nullable()->after('assignment_status');
            $table->decimal('subtotal', 14, 2)->nullable()->after('total_price');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal');
            $table->string('voucher_code', 20)->nullable()->after('discount_amount');
            $table->decimal('total_amount', 14, 2)->nullable()->after('voucher_code');
            $table->decimal('dp_percent', 5, 2)->nullable()->after('total_amount');
            $table->decimal('dp_amount', 14, 2)->nullable()->after('dp_percent');
            $table->decimal('remaining_cash', 14, 2)->nullable()->after('dp_amount');
            $table->timestamp('cash_confirmed_at')->nullable()->after('remaining_cash');

            $table->index(['assigned_driver_id', 'assignment_status'], 'dsb_assigned_status_index');
        });

        Schema::table('driver_services', function (Blueprint $table) {
            $table->decimal('dp_percent', 5, 2)->nullable()->after('price_per_pax');
        });
    }

    public function down(): void
    {
        Schema::table('driver_services', function (Blueprint $table) {
            $table->dropColumn('dp_percent');
        });

        Schema::table('driver_service_bookings', function (Blueprint $table) {
            $table->dropForeign(['assigned_driver_id']);
            $table->dropIndex('dsb_assigned_status_index');
            $table->dropColumn([
                'assigned_driver_id',
                'assignment_status',
                'accepted_at',
                'subtotal',
                'discount_amount',
                'voucher_code',
                'total_amount',
                'dp_percent',
                'dp_amount',
                'remaining_cash',
                'cash_confirmed_at',
            ]);
        });

        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('driver_service_reassignments');
        Schema::dropIfExists('driver_withdrawals');
        Schema::dropIfExists('driver_wallet_transactions');
        Schema::dropIfExists('driver_wallets');
    }
};
