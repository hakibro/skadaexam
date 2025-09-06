<?php

namespace App\Console\Commands;

use App\Models\Mapel;
use Illuminate\Console\Command;

class CleanupSoftDeletedMapel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapel:cleanup {--force : Force delete all soft deleted mapel records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists and optionally permanently deletes soft-deleted mapel records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trashedMapels = Mapel::onlyTrashed()->get();
        $count = $trashedMapels->count();

        if ($count === 0) {
            $this->info('No soft-deleted mapel records found.');
            return 0;
        }

        $this->info("Found {$count} soft-deleted mapel records:");

        $headers = ['ID', 'Kode', 'Nama Mapel', 'Deleted At'];
        $rows = [];

        foreach ($trashedMapels as $mapel) {
            $rows[] = [
                'id' => $mapel->id,
                'kode' => $mapel->kode_mapel,
                'nama' => $mapel->nama_mapel,
                'deleted_at' => $mapel->deleted_at
            ];
        }

        $this->table($headers, $rows);

        if ($this->option('force')) {
            if ($this->confirm("Are you sure you want to permanently delete these {$count} mapel records?", false)) {
                $deleted = Mapel::onlyTrashed()->forceDelete();
                $this->info("Successfully permanently deleted {$count} mapel records.");
            } else {
                $this->warn('Operation cancelled.');
            }
        } else {
            $this->info('To permanently delete these records, run this command with the --force option:');
            $this->line('php artisan mapel:cleanup --force');
        }

        return 0;
    }
}
