<?php

namespace App\Models\Mobile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'mobile_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'max_job_per_day',
    ];

    protected $casts = [
        'max_job_per_day' => 'integer',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::find($key);

        return $setting?->max_job_per_day ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['max_job_per_day' => $value]
        );
    }
}
