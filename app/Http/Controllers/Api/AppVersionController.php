<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:android,ios',
            'current_version_code' => 'required|integer|min:0',
        ]);

        $latest = AppVersion::where('platform', $request->platform)
            ->where('is_active', true)
            ->orderBy('version_code', 'desc')
            ->first();

        if (! $latest) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'update_available' => false,
                    'is_force_update' => false,
                    'latest_version' => null,
                ],
            ]);
        }

        $updateAvailable = $request->current_version_code < $latest->version_code;

        return response()->json([
            'status' => 'success',
            'data' => [
                'update_available' => $updateAvailable,
                'is_force_update' => $updateAvailable && $latest->is_force_update,
                'latest_version' => $updateAvailable ? [
                    'version_name' => $latest->version_name,
                    'version_code' => $latest->version_code,
                    'whats_new' => $latest->whats_new,
                    'apk_url' => $latest->apk_url,
                ] : null,
            ],
        ]);
    }
}
