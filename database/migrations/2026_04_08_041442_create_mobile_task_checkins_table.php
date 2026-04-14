<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_task_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('mobile_tasks')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('mobile_users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->index(['task_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_task_checkins');
    }
};
