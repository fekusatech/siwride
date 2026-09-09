<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distinguishes which mobile app a version row belongs to. The table
 * originally assumed a single app (see the "driver mobile app" copy still
 * in the admin UI), so `platform` alone can't tell a customer-app Android
 * build apart from a driver-app Android build once both exist side by side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->string('app', 20)->default('customer')->after('id')->comment('customer or driver');
        });

        Schema::table('app_versions', function (Blueprint $table) {
            $table->dropUnique(['platform', 'version_code']);
            $table->unique(['app', 'platform', 'version_code']);
        });
    }

    public function down(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->dropUnique(['app', 'platform', 'version_code']);
            $table->unique(['platform', 'version_code']);
            $table->dropColumn('app');
        });
    }
};
