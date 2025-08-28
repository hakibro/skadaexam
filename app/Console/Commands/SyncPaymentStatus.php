<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use Illuminate\Console\Command;

class SyncPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siswa:sync-payment {--tanggal=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payment status from API for all students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggal = $this->option('tanggal') ?? now()->format('Y-m-d');
        $force = $this->option('force');

        $query = Siswa::whereNotNull('idyayasan');

        if (!$force) {
            // Only sync students that need refresh
            $query->where(function ($q) {
                $q->whereNull('payment_last_check')
                    ->orWhere('payment_last_check', '<', now()->subHour());
            });
        }

        $students = $query->get();
        $total = $students->count();

        if ($total === 0) {
            $this->info('No students need payment sync.');
            return;
        }

        $this->info("Syncing payment status for {$total} students on {$tanggal}...");

        $bar = $this->output->createProgressBar($total);
        $success = 0;
        $failed = 0;

        foreach ($students as $siswa) {
            if ($siswa->fetchPaymentFromApi($tanggal)) {
                $success++;
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Sync completed: {$success} success, {$failed} failed");
    }
}
