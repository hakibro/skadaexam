<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KioskSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const KEYS = [
        'exit_password',
        'password_expires_at',
    ];

    public static function allAsArray(): array
    {
        return Cache::rememberForever('kiosk_settings', function () {
            $values = static::query()->pluck('value', 'key')->all();

            return collect(static::KEYS)
                ->mapWithKeys(fn($key) => [$key => $values[$key] ?? null])
                ->all();
        });
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::allAsArray()[$key] ?? $default;
    }

    public static function setMany(array $values): void
    {
        foreach (static::KEYS as $key) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            static::query()->updateOrCreate(['key' => $key], ['value' => $values[$key]]);
        }

        Cache::forget('kiosk_settings');
    }
}
