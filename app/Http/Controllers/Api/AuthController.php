<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'mobile_app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'driver_id' => $user->driver_id,
                    'name' => "{$user->firstname} {$user->lastname}",
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'vehicle' => $user->vehicle,
                ],
            ],
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|unique:drivers',
            'phone' => 'required|string|max:50|unique:users|unique:drivers',
            'password' => 'required|string|min:8',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'driver',
                'status' => 'inactive', // Default to inactive/pending
            ]);

            Driver::create([
                'name' => "{$request->firstname} {$request->lastname}",
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $user->password,
                'status' => 'inactive',
                'nid' => 'DRV-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. Please wait for admin approval.',
                'data' => [
                    'user' => $user,
                ],
            ], 201);
        });
    }
}
