<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokuSetting extends Model
{
    public $incrementing = false;

    protected $fillable = ['enabled', 'environment', 'sandbox_client_id', 'sandbox_secret_key', 'production_client_id', 'production_secret_key'];

    protected $hidden = ['sandbox_secret_key', 'production_secret_key'];

    protected $attributes = ['id' => 1, 'enabled' => false, 'environment' => 'sandbox'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'sandbox_secret_key' => 'encrypted', 'production_secret_key' => 'encrypted'];
    }
}
