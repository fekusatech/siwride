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
        $setting = Setting::firstOrCreate([], [
            'business_name' => config('app.name', 'Siwride'),
        ]);

        return Inertia::render('Admin/Settings/General', [
            'setting' => $setting,
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $setting = Setting::firstOrCreate([], [
            'business_name' => config('app.name', 'Siwride'),
        ]);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB Max
            'favicon' => ['nullable', 'image', 'mimes:ico,png,jpg,jpeg,svg', 'max:1024'], // 1MB Max
        ]);

        $data = [
            'business_name' => $validated['business_name'],
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
            Log::info('New logo stored: '.$data['logo']);
        }

        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
            Log::info('New favicon stored: '.$data['favicon']);
        }

        $setting->update($data);

        Log::info('Settings updated for business: '.$setting->business_name);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
