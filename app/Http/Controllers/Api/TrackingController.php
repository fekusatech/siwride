<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $request->user()->driverLocation()->updateOrCreate(
            ['driver_id' => $request->user()->id],
            ['latitude' => $request->latitude, 'longitude' => $request->longitude]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated.',
        ]);
    }

    public function indexActive()
    {
        $locations = User::where('role', 'driver')
            ->whereHas('driverLocation', function ($query) {
                $query->where('updated_at', '>=', now()->subMinutes(5));
            })
            ->with('driverLocation')
            ->get()
            ->map(function ($user) {
                return [
                    'driver_id' => $user->id,
                    'driver_name' => "{$user->firstname} {$user->lastname}",
                    'latitude' => $user->driverLocation->latitude,
                    'longitude' => $user->driverLocation->longitude,
                    'last_updated' => $user->driverLocation->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $locations,
        ]);
    }
}
