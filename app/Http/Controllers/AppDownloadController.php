<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AppDownloadController extends Controller
{
    public function show(): Response
    {
        $version = AppVersion::where('app', 'customer')
            ->where('platform', 'android')
            ->where('is_active', true)
            ->orderByDesc('version_code')
            ->first();

        return Inertia::render('Public/AppDownload', [
            'version' => $version ? [
                'version_name' => $version->version_name,
                'version_code' => $version->version_code,
                'apk_url' => $version->apk_url,
                'whats_new' => $version->whats_new,
                'size_mb' => $this->apkSizeMb($version->apk_url),
                'updated_at' => $version->updated_at?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Reads the real file size off local storage when apk_url points at our
     * own /storage/apk/... disk, so it stays correct after the admin
     * re-uploads a build without anyone touching this page.
     */
    private function apkSizeMb(?string $apkUrl): ?float
    {
        if (! $apkUrl) {
            return null;
        }

        $marker = '/storage/apk/';
        $position = strpos($apkUrl, $marker);
        if ($position === false) {
            return null;
        }

        $relativePath = 'apk/'.substr($apkUrl, $position + strlen($marker));
        if (! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return round(Storage::disk('public')->size($relativePath) / 1048576, 1);
    }
}
