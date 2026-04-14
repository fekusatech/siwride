<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('type')->nullable()->comment('Sedan, SUV, etc.');
            $table->string('registration_number')->unique();
            $table->string('color')->nullable();
            $table->string('status')->default('active')->comment('active, inactive');
            $table->timestamps();

            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_vehicles');
    }
};
