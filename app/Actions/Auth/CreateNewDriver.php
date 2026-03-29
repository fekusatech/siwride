<?php

namespace App\Actions\Auth;

use App\Models\Driver;
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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Driver::class)],
            'phone' => ['required', 'string', 'max:50', Rule::unique(Driver::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'vehicle_registration_number' => ['required', 'string', 'max:100'],
        ])->validate();

        return Driver::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'password' => $input['password'],
            'vehicle_type' => $input['vehicle_type'],
            'vehicle_registration_number' => $input['vehicle_registration_number'],
            'status' => 'inactive', // Default status for new drivers
        ]);
    }
}
