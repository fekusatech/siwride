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
        // 1. Update Users table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'uid')) {
                $table->uuid('uid')->unique()->nullable()->after('id')->comment('Firebase compat UUID');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('phone')->comment('admin or driver');
            }
            if (! Schema::hasColumn('users', 'nid')) {
                $table->string('nid')->unique()->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->nullable()->after('nid');
            }
            if (! Schema::hasColumn('users', 'nik_image')) {
                $table->string('nik_image')->nullable()->after('nik');
            }
            if (! Schema::hasColumn('users', 'sim')) {
                $table->string('sim')->nullable()->after('nik_image');
            }
            if (! Schema::hasColumn('users', 'sim_image')) {
                $table->string('sim_image')->nullable()->after('sim');
            }
        });

        // 2. Update Orders table
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'is_cash')) {
                $table->boolean('is_cash')->default(false)->after('price');
            }
            if (! Schema::hasColumn('orders', 'is_shared')) {
                $table->boolean('is_shared')->default(false)->after('is_cash');
            }
            if (! Schema::hasColumn('orders', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('is_shared');
            }
        });

        // 3. Create Order Evidences table (replacing job_evidences from PRD)
        if (! Schema::hasTable('order_evidences')) {
            Schema::create('order_evidences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('type')->comment('berangkat or tiba');
                $table->string('photo_url');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->timestamp('captured_at')->useCurrent();
                $table->timestamps();
            });
        }

        // 4. Create Driver Locations table
        if (! Schema::hasTable('driver_locations')) {
            Schema::create('driver_locations', function (Blueprint $table) {
                $table->foreignId('driver_id')->primary()->constrained('users')->cascadeOnDelete();
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->timestamps();
            });
        }

        // 5. Data Migration: Move existing drivers to users table
        if (Schema::hasTable('drivers')) {
            $drivers = DB::table('drivers')->get();
            foreach ($drivers as $driver) {
                // Check if user already exists with same email
                $userId = DB::table('users')->where('email', $driver->email)->value('id');

                if (! $userId) {
                    // Split name into first and last
                    $nameParts = explode(' ', $driver->name, 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';

                    $userId = DB::table('users')->insertGetId([
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'email' => $driver->email,
                        'phone' => $driver->phone,
                        'password' => $driver->password ?? bcrypt('password123'),
                        'status' => $driver->status ?? 'active',
                        'role' => 'driver',
                        'nid' => $driver->nid ?? null,
                        'nik' => $driver->nik ?? null,
                        'nik_image' => $driver->nik_image ?? null,
                        'sim' => $driver->sim ?? null,
                        'sim_image' => $driver->sim_image ?? null,
                        'created_at' => $driver->created_at,
                        'updated_at' => $driver->updated_at,
                    ]);
                } else {
                    // Update existing user with driver info
                    DB::table('users')->where('id', $userId)->update([
                        'role' => 'driver',
                        'nid' => $driver->nid ?? null,
                        'nik' => $driver->nik ?? null,
                        'nik_image' => $driver->nik_image ?? null,
                        'sim' => $driver->sim ?? null,
                        'sim_image' => $driver->sim_image ?? null,
                    ]);
                }

                // Update foreign keys in orders and vehicles
                DB::table('orders')->where('driver_id', $driver->id)->update(['driver_id' => $userId]);
                DB::table('orders')->where('claimed_driver_id', $driver->id)->update(['claimed_driver_id' => $userId]);
                DB::table('vehicles')->where('driver_id', $driver->id)->update(['driver_id' => $userId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
        Schema::dropIfExists('order_evidences');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_cash', 'is_shared', 'is_cancelled']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['uid', 'role']);
        });
    }
};
