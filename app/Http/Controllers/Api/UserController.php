<?php

namespace App\Http\Controllers\Api;

use App\Models\Mobile\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,disabled'],
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user,
        ]);
    }
}
