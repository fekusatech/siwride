<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\Mobile\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'uid' => (string) str()->uuid(),
            'email' => $request->email,
            'display_name' => $request->display_name,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'status' => 'pending',
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json(
            [
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token,
            ],
            Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(
                ['message' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if (! in_array($user->status, ['approved'])) {
            return response()->json(
                [
                    'message' => 'Account is not approved. Current status: '.$user->status,
                    'status' => $user->status,
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $token = $user->createToken('mobile-app', ['*'], now()->addMonth())->plainTextToken;

        return response()->json(
            [
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ]
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(
            ['message' => 'Logged out successfully']
        );
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(
            [
                'user' => $request->user(),
            ]
        );
    }
}
