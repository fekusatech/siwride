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
        Schema::create('driver_articles', function (Blueprint $table) {
            $table->id();
            // FKs to users.id (not drivers.id) — drivers authenticate as
            // User rows (role=driver) for the web dashboard, see the
            // `driver` guard in config/auth.php.
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('content');
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_articles');
    }
};
