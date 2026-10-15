<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('travel or guide');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 50)->unique();
            $table->string('password');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('nik_image')->nullable();
            $table->string('status', 50)->default('pending')->comment('pending, active, or rejected');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
