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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->string('period_label'); // e.g. "2026-03-A" (1-15) or "2026-03-B" (16-31)
            $table->integer('total_jobs')->default(0);
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('admin_fee')->default(0);
            $table->bigInteger('net_amount')->default(0);
            $table->string('status')->default('draft'); // draft, paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
