<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique();
            $table->integer('no')->comment('Nomor urut');
            $table->string('tanggal')->comment('DD/MM/YYYY');
            $table->string('jam')->comment('HH:MM');
            $table->string('nama_tamu');
            $table->string('phone_tamu');
            $table->string('flight')->nullable();
            $table->text('pickup');
            $table->text('dropoff');
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();
            $table->decimal('dropoff_lat', 10, 8)->nullable();
            $table->decimal('dropoff_lng', 11, 8)->nullable();
            $table->integer('pass')->default(1);
            $table->unsignedBigInteger('price')->default(0);
            $table->string('status')->default('Pending')->comment('Pending, OTW, Tiba, Selesai, Cancel');
            $table->foreignId('driver_id')->nullable()->constrained('mobile_users')->nullOnDelete();
            $table->string('driver_name')->nullable();
            $table->foreignId('admin_id')->constrained('mobile_users');
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_cash')->default(true);
            $table->boolean('is_cancelled')->default(false);
            $table->timestamps();

            $table->index(['status', 'tanggal']);
            $table->index('driver_id');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_tasks');
    }
};
