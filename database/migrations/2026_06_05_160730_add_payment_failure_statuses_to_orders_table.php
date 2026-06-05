<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: payment_status column already exists from previous migration.
     * This migration adds documentation that payment_status supports:
     * - pending (default)
     * - paid
     * - expired
     * - failed
     * - cancelled
     * - refunded
     */
    public function up(): void
    {
        // Column already exists, no changes needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to revert
    }
};
