<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function indexPending()
    {
        $drivers = User::where('role', 'driver')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $drivers,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $user = User::where('role', 'driver')
            ->where('id', $id)
            ->firstOrFail();

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Status driver berhasil diperbarui menjadi {$request->status}.",
        ]);
    }
}
