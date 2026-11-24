<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doku_payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->morphs('booking');
            $table->string('environment');
            $table->string('client_id');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->string('status')->default('creating');
            $table->text('payment_url')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doku_payments');
    }
};
