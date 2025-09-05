<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SesiAssignmentService;
use App\Models\JadwalUjian;

class AutoAssignSesiRuangan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sesi:auto-assign {--jadwal-id= : Specific jadwal ujian ID to assign} {--dry-run : Show what would be assigned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assign sesi ruangan to jadwal ujian based on matching dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sesiAssignmentService = new SesiAssignmentService();
        $dryRun = $this->option('dry-run');
        $jadwalId = $this->option('jadwal-id');

        $this->info('Starting auto assignment of sesi ruangan...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No actual changes will be made');
        }

        try {
            if ($jadwalId) {
                // Assign for specific jadwal
                $jadwal = JadwalUjian::find($jadwalId);

                if (!$jadwal) {
                    $this->error("Jadwal ujian with ID {$jadwalId} not found");
                    return 1;
                }

                $this->info("Processing jadwal: {$jadwal->judul}");

                if (!$dryRun) {
                    $cleanedCount = $sesiAssignmentService->cleanupAssignments($jadwal);
                    $assignedCount = $sesiAssignmentService->autoAssignSesiByDate($jadwal);

                    if ($cleanedCount > 0) {
                        $this->info("Cleaned up {$cleanedCount} outdated assignments");
                    }

                    if ($assignedCount > 0) {
                        $this->info("Assigned {$assignedCount} new sesi ruangan connections");
                    }

                    if ($cleanedCount === 0 && $assignedCount === 0) {
                        $this->info("No changes needed for this jadwal");
                    }
                } else {
                    // Simulate what would happen
                    $this->info("Would check and update assignments for this jadwal");
                }
            } else {
                // Assign for all eligible jadwal
                if (!$dryRun) {
                    $totalAssigned = $sesiAssignmentService->autoAssignForAllEligibleJadwal();

                    if ($totalAssigned > 0) {
                        $this->info("Successfully assigned {$totalAssigned} sesi ruangan connections");
                    } else {
                        $this->info("No new assignments were needed");
                    }
                } else {
                    $eligibleJadwal = JadwalUjian::where('auto_assign_sesi', true)
                        ->where('scheduling_mode', 'flexible')
                        ->whereIn('status', ['draft', 'aktif'])
                        ->get();

                    $this->info("Found {$eligibleJadwal->count()} eligible jadwal ujian for auto assignment");

                    foreach ($eligibleJadwal as $jadwal) {
                        $this->line("- {$jadwal->judul} (Date: {$jadwal->tanggal->format('Y-m-d')})");
                    }
                }
            }

            $this->info('Auto assignment completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during auto assignment: ' . $e->getMessage());

            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return 1;
        }
    }
}
