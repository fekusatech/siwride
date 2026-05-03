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
        $value = static::query()
            ->where('setting_key', $key)
            ->value('setting_value');

        if (in_array($value, ['0', 0, 'false', false], true) && in_array($key, ['logo', 'favicon'], true)) {
            return $default;
        }

        return $value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        if (in_array($key, ['logo', 'favicon'], true) && (blank($value) || $value === false)) {
            $value = null;
        }

        static::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    public static function values(array $defaults = []): array
    {
        $values = array_merge(
            $defaults,
            static::query()->pluck('setting_value', 'setting_key')->toArray()
        );

        foreach (['logo', 'favicon'] as $key) {
            if (in_array($values[$key] ?? null, ['0', 0, 'false', false], true)) {
                $values[$key] = null;
            }
        }

        return $values;
    }
}
