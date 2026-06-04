<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SchoolSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const KEYS = [
        'nama_sekolah',
        'alamat',
        'npsn',
        'nss',
        'kode_pos',
        'telepon',
        'email',
        'website',
        'kepala_sekolah',
        'info_lain',
        'logo_path',
        'sync_siswa_enabled',
        'sync_siswa_interval_minutes',
        'sync_siswa_date_start',
        'sync_siswa_date_end',
        'sync_siswa_time_start',
        'sync_siswa_time_end',
        'sync_siswa_last_run_at',
        'sync_siswa_last_status',
        'sync_siswa_last_message',
    ];

    public static function allAsArray(): array
    {
        return Cache::rememberForever('school_settings', function () {
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

        Cache::forget('school_settings');
    }
}
