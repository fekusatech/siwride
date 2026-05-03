<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewDriver
{
    /**
     * Validate and create a newly registered driver.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Driver
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Driver::class), Rule::unique(User::class)],
            'phone' => ['required', 'string', 'max:50', Rule::unique(Driver::class), Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'vehicle_registration_number' => ['required', 'string', 'max:100'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Split name for User model
            $nameParts = explode(' ', $input['name'], 2);
            $firstname = $nameParts[0];
            $lastname = $nameParts[1] ?? '';

            // Create User record for Authentication
            User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
                'role' => 'driver',
                'status' => 'inactive',
            ]);

            // Create Driver record for Order Management
            return Driver::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => Hash::make($input['password']),
                'status' => 'inactive',
                // These columns might not exist in drivers table but were in the action
                // 'vehicle_type' => $input['vehicle_type'],
                // 'vehicle_registration_number' => $input['vehicle_registration_number'],
            ]);
        });
    }
}
