<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settings = [
            'sync_siswa_enabled' => '0',
            'sync_siswa_interval_minutes' => '15',
            'sync_siswa_date_start' => null,
            'sync_siswa_date_end' => null,
            'sync_siswa_time_start' => null,
            'sync_siswa_time_end' => null,
            'sync_siswa_last_run_at' => null,
            'sync_siswa_last_status' => null,
            'sync_siswa_last_message' => null,
        ];

        foreach ($settings as $key => $value) {
            DB::table('school_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('school_settings')
            ->whereIn('key', [
                'sync_siswa_enabled',
                'sync_siswa_interval_minutes',
                'sync_siswa_date_start',
                'sync_siswa_date_end',
                'sync_siswa_time_start',
                'sync_siswa_time_end',
                'sync_siswa_last_run_at',
                'sync_siswa_last_status',
                'sync_siswa_last_message',
            ])
            ->delete();
    }
};
