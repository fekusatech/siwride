<?php

namespace App\Services;

use App\Models\DokuSetting;

class DokuConfiguration
{
    /** @return array{enabled: mixed, environment: mixed, client_id: mixed, secret_key: mixed} */
    public function active(): array
    {
        $settings = DokuSetting::query()->find(1);

        if ($settings === null) {
            return array_intersect_key(config('services.doku'), array_flip(['enabled', 'environment', 'client_id', 'secret_key']));
        }

        return [
            'enabled' => $settings->enabled,
            'environment' => $settings->environment,
            'client_id' => $settings->getAttribute($settings->environment.'_client_id'),
            'secret_key' => $settings->getAttribute($settings->environment.'_secret_key'),
        ];
    }
}
