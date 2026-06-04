<?php

namespace App\Console\Commands;

use App\Models\SchoolSetting;
use App\Services\SiswaQuickSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SiswaQuickSync extends Command
{
    protected $signature = 'siswa:quick-sync {--force : Abaikan gate setting, range, dan interval untuk test manual}';

    protected $description = 'Quick sync siswa dari API SIKEU berdasarkan setting sinkronisasi admin';

    public function handle(SiswaQuickSyncService $syncService): int
    {
        $force = (bool) $this->option('force');
        $settings = SchoolSetting::allAsArray();
        $now = now();

        if (!$force) {
            $skipReason = $this->skipReason($settings, $now);
            if ($skipReason !== null) {
                $this->line('Skipped: ' . $skipReason);
                return self::SUCCESS;
            }
        }

        $this->info('Running siswa quick sync...');

        $result = $syncService->sync(function (array $progress) {
            if (!empty($progress['message'])) {
                $this->line($progress['message']);
            }
        });

        SchoolSetting::setMany([
            'sync_siswa_last_run_at' => now()->toDateTimeString(),
            'sync_siswa_last_status' => $result['success'] ? 'success' : 'failed',
            'sync_siswa_last_message' => $result['message']
                ?? $result['error']
                ?? $result['warning']
                ?? 'Quick sync selesai.',
        ]);

        if (!$result['success']) {
            $this->error($result['error'] ?? $result['warning'] ?? 'Quick sync gagal.');
            return self::FAILURE;
        }

        $this->info($result['message'] ?? 'Quick sync selesai.');
        return self::SUCCESS;
    }

    private function skipReason(array $settings, Carbon $now): ?string
    {
        if (($settings['sync_siswa_enabled'] ?? '0') !== '1') {
            return 'sync siswa otomatis nonaktif.';
        }

        if (!$this->withinDateRange($settings, $now)) {
            return 'di luar range tanggal sinkronisasi.';
        }

        if (!$this->withinTimeRange($settings, $now)) {
            return 'di luar range waktu sinkronisasi.';
        }

        $intervalMinutes = max(1, (int) ($settings['sync_siswa_interval_minutes'] ?? 15));
        $lastRun = !empty($settings['sync_siswa_last_run_at'])
            ? Carbon::parse($settings['sync_siswa_last_run_at'])
            : null;

        if ($lastRun && $lastRun->copy()->addMinutes($intervalMinutes)->greaterThan($now)) {
            return "interval {$intervalMinutes} menit belum tercapai.";
        }

        return null;
    }

    private function withinDateRange(array $settings, Carbon $now): bool
    {
        $dateStart = $settings['sync_siswa_date_start'] ?? null;
        $dateEnd = $settings['sync_siswa_date_end'] ?? null;

        if ($dateStart && $now->toDateString() < $dateStart) {
            return false;
        }

        if ($dateEnd && $now->toDateString() > $dateEnd) {
            return false;
        }

        return true;
    }

    private function withinTimeRange(array $settings, Carbon $now): bool
    {
        $timeStart = $settings['sync_siswa_time_start'] ?? null;
        $timeEnd = $settings['sync_siswa_time_end'] ?? null;

        if (!$timeStart && !$timeEnd) {
            return true;
        }

        $current = $now->format('H:i');
        $start = $timeStart ?: '00:00';
        $end = $timeEnd ?: '23:59';

        if ($start <= $end) {
            return $current >= $start && $current <= $end;
        }

        return $current >= $start || $current <= $end;
    }
}
