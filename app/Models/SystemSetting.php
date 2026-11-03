<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected static ?array $cache = null;

    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = static::getAllKeyed();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $label = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'group' => $group,
                'label' => $label ?? ucwords(str_replace('_', ' ', $key)),
            ]
        );
        static::$cache = null;
    }

    public static function getAllKeyed(): array
    {
        if (static::$cache === null) {
            static::$cache = static::pluck('value', 'key')->toArray();
        }
        return static::$cache;
    }
}
