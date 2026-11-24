<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDokuSettingRequest;
use App\Models\DokuSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DokuSettingController extends Controller
{
    public function edit(): Response
    {
        $settings = DokuSetting::query()->find(1) ?? new DokuSetting;

        return Inertia::render('Admin/Settings/Doku', [
            'doku' => [
                'enabled' => $settings->enabled,
                'environment' => $settings->environment,
                'sandbox_client_id' => $settings->sandbox_client_id,
                'production_client_id' => $settings->production_client_id,
                'sandbox_has_secret' => filled($settings->getRawOriginal('sandbox_secret_key')),
                'production_has_secret' => filled($settings->getRawOriginal('production_secret_key')),
            ],
        ]);
    }

    public function update(UpdateDokuSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data): void {
            DokuSetting::query()->insertOrIgnore(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $settings = DokuSetting::query()->lockForUpdate()->findOrFail(1);
            foreach (['sandbox_secret_key', 'production_secret_key'] as $secret) {
                if (! filled($data[$secret] ?? null)) {
                    unset($data[$secret]);
                }
            }
            $settings->fill($data)->save();
        });

        return to_route('admin.settings.doku.edit')->with('success', 'Pengaturan DOKU berhasil disimpan.');
    }
}
