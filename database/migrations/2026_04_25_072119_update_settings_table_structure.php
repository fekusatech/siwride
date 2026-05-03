<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'logo', 'favicon']);
            $table->string('setting_key')->unique()->after('id');
            $table->text('setting_value')->nullable()->after('setting_key');
        });

        // Insert default limit
        DB::table('settings')->insert([
            'setting_key' => 'daily_job_limit',
            'setting_value' => '5',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['setting_key', 'setting_value']);
            $table->string('business_name')->default('Siwride');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
        });
    }
};
