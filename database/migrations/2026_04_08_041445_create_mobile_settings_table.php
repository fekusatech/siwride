<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_settings', function (Blueprint $table) {
            $table->string('key')->primary()->comment('e.g., general');
            $table->unsignedInteger('max_job_per_day')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_settings');
    }
};
