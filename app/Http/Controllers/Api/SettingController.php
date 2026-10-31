<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::query()->whereNotIn('setting_key', ['recaptcha_secret_key', 'xendit_secret_key', 'xendit_webhook_token'])->pluck('setting_value', 'setting_key');

        return response()->json([
            'status' => 'success',
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.recaptcha_secret_key' => ['prohibited'],
            'settings.xendit_secret_key' => ['prohibited'],
            'settings.xendit_webhook_token' => ['prohibited'],
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.',
        ]);
    }
}
