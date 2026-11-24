<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doku_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('enabled')->default(false);
            $table->string('environment')->default('sandbox');
            $table->string('sandbox_client_id')->nullable();
            $table->text('sandbox_secret_key')->nullable();
            $table->string('production_client_id')->nullable();
            $table->text('production_secret_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doku_settings');
    }
};
