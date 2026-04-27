<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'Siwride',
            'email' => 'admin@siwride.com',
            'phone' => '08123456789',
            'password' => Hash::make('admin123'),
        ]);

        User::factory()->create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            DriverSeeder::class,
            OrderSeeder::class,
            ZoneSeeder::class,
        ]);
    }
}
