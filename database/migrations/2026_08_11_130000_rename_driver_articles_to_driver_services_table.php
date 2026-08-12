<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_articles') && ! Schema::hasTable('driver_services')) {
            Schema::rename('driver_articles', 'driver_services');
        }

        if (Schema::hasColumn('driver_services', 'activity_id')) {
            Schema::table('driver_services', function (Blueprint $table) {
                $table->dropForeign('driver_articles_activity_id_foreign');
                $table->dropColumn(['activity_id', 'excerpt', 'content']);
            });
        }

        if (! Schema::hasColumn('driver_services', 'price_per_pax')) {
            Schema::table('driver_services', function (Blueprint $table) {
                $table->decimal('price_per_pax', 10, 2)->nullable()->after('title');
                $table->unsignedInteger('min_pax')->default(1)->after('price_per_pax');
                $table->unsignedInteger('max_pax')->nullable()->after('min_pax');
                $table->string('duration_label')->nullable()->after('max_pax');
                $table->string('meeting_point')->nullable()->after('duration_label');
                $table->text('description')->nullable()->after('meeting_point');
                $table->json('includes')->nullable()->after('description');
                $table->json('excludes')->nullable()->after('includes');
                $table->json('highlights')->nullable()->after('excludes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('driver_services', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_pax', 'min_pax', 'max_pax', 'duration_label',
                'meeting_point', 'description', 'includes', 'excludes', 'highlights',
            ]);
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('excerpt')->nullable();
            $table->longText('content')->default('');
        });

        Schema::rename('driver_services', 'driver_articles');
    }
};
