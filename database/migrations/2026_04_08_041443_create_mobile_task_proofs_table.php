<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_task_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('mobile_tasks')->cascadeOnDelete();
            $table->string('type')->comment('berangkat or tiba');
            $table->string('file_url');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_task_proofs');
    }
};
