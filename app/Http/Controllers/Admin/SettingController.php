<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function general(): Response
    {
        return Inertia::render('Admin/Settings/General', [
            'setting' => Setting::values([
                'business_name' => config('app.name', 'Siwride'),
                'logo' => null,
                'recaptcha_enabled' => '0',
                'recaptcha_site_key' => null,
                'recaptcha_secret_key' => null,
                'xendit_enabled' => '0',
                'xendit_secret_key' => null,
                'xendit_webhook_token' => null,
                'updated_at' => Setting::query()->max('updated_at'),
            ]),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'recaptcha_enabled' => ['required', 'boolean'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
            'xendit_enabled' => ['required', 'boolean'],
            'xendit_secret_key' => ['nullable', 'string', 'max:500'],
            'xendit_webhook_token' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['recaptcha_enabled']) {
            $request->validate([
                'recaptcha_site_key' => ['required', 'string', 'max:255'],
                'recaptcha_secret_key' => ['required', 'string', 'max:255'],
            ]);
        }

        if ($validated['xendit_enabled']) {
            $request->validate([
                'xendit_secret_key' => ['required', 'string', 'max:500'],
            ]);
        }

        Setting::setValue('business_name', $validated['business_name']);
        Setting::setValue('recaptcha_enabled', $validated['recaptcha_enabled'] ? '1' : '0');
        Setting::setValue('recaptcha_site_key', $validated['recaptcha_site_key'] ?? '');
        Setting::setValue('recaptcha_secret_key', $validated['recaptcha_secret_key'] ?? '');
        Setting::setValue('xendit_enabled', $validated['xendit_enabled'] ? '1' : '0');
        Setting::setValue('xendit_secret_key', $validated['xendit_secret_key'] ?? '');
        Setting::setValue('xendit_webhook_token', $validated['xendit_webhook_token'] ?? '');

        $currentLogo = Setting::getValue('logo');
        if ($request->hasFile('logo')) {
            if ($currentLogo && $currentLogo !== '0') {
                Storage::disk('public')->delete($currentLogo);
            }

            $logoPath = $request->file('logo')->store('settings', 'public');

            if ($logoPath === false) {
                return redirect()->back()->with('error', 'Failed to store business logo. Please check directory permissions.');
            }

            Setting::setValue('logo', $logoPath);
            Log::info('New logo stored: '.$logoPath);
        }

        Log::info('Settings updated for business: '.$validated['business_name']);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
