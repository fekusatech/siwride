<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'platform',
        'version_name',
        'version_code',
        'apk_url',
        'whats_new',
        'is_force_update',
        'is_active',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'is_force_update' => 'boolean',
        'is_active' => 'boolean',
    ];
}
