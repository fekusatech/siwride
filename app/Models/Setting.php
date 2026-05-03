<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()
            ->where('setting_key', $key)
            ->value('setting_value') ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    public static function values(array $defaults = []): array
    {
        return array_merge(
            $defaults,
            static::query()->pluck('setting_value', 'setting_key')->toArray()
        );
    }
}
