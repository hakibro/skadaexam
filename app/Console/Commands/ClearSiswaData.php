<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class ClearSiswaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siswa:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all siswa data from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Siswa::count();

        if ($count === 0) {
            $this->info('No siswa data found to delete.');
            return;
        }

        if ($this->confirm("Are you sure you want to delete all {$count} siswa records?")) {
            try {
                // Clear all siswa data
                Siswa::truncate();

                // Reset auto increment
                if (config('database.default') === 'mysql') {
                    DB::statement('ALTER TABLE siswa AUTO_INCREMENT = 1');
                }

                $this->info("Successfully deleted {$count} siswa records.");
                $this->info('Database is now ready for fresh Excel import.');
            } catch (\Exception $e) {
                $this->error('Error clearing siswa data: ' . $e->getMessage());
            }
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
