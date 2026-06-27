<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppVersionController extends Controller
{
    public function index()
    {
        $versions = AppVersion::latest()->paginate(10);

        return Inertia::render('Admin/AppVersions/Index', [
            'versions' => $versions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AppVersions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:android,ios',
            'version_name' => 'required|string|max:50',
            'version_code' => 'required|integer|min:1',
            'apk_url' => 'nullable|url|max:2000',
            'whats_new' => 'nullable|string|max:2000',
            'is_force_update' => 'boolean',
            'is_active' => 'boolean',
        ]);

        AppVersion::create($validated);

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'App version created successfully.');
    }

    public function edit(AppVersion $appVersion)
    {
        return Inertia::render('Admin/AppVersions/Create', [
            'version' => $appVersion,
        ]);
    }

    public function update(Request $request, AppVersion $appVersion)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:android,ios',
            'version_name' => 'required|string|max:50',
            'version_code' => 'required|integer|min:1',
            'apk_url' => 'nullable|url|max:2000',
            'whats_new' => 'nullable|string|max:2000',
            'is_force_update' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $appVersion->update($validated);

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'App version updated successfully.');
    }

    public function destroy(AppVersion $appVersion)
    {
        $appVersion->delete();

        return redirect()->route('admin.app-versions.index')
            ->with('success', 'App version deleted.');
    }
}
