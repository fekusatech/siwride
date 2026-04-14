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
        Schema::create('mobile_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->nullable()->comment('Firebase compat UUID');
            $table->string('email')->unique();
            $table->string('display_name');
            $table->string('phone_number')->nullable();
            $table->string('role')->comment('admin or driver');
            $table->string('status')->default('pending')->comment('pending, approved, rejected, disabled');
            $table->string('photo_url')->nullable();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_users');
    }
};
