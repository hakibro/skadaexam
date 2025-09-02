<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RefreshSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-seeder {--fresh} {--model=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database with updated seeders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('fresh') && !$this->option('force')) {
            if (!$this->confirm('This will drop all your tables and reseed the database. Are you sure?')) {
                $this->error('Operation cancelled.');
                return 1;
            }
        }

        $start = microtime(true);
        $this->info('Starting database refresh...');

        try {
            // Migrate
            if ($this->option('fresh')) {
                $this->info('Running fresh migrations...');
                Artisan::call('migrate:fresh', ['--force' => true]);
                $this->info(Artisan::output());
            } else {
                $this->info('Running migrations...');
                Artisan::call('migrate', ['--force' => true]);
                $this->info(Artisan::output());
            }

            // Seed specific model if provided
            if ($model = $this->option('model')) {
                $seederClass = "Database\\Seeders\\{$model}Seeder";
                if (class_exists($seederClass)) {
                    $this->info("Seeding {$model} data...");
                    Artisan::call('db:seed', [
                        '--class' => $seederClass,
                        '--force' => true,
                    ]);
                    $this->info(Artisan::output());
                } else {
                    $this->error("Seeder class {$seederClass} not found");
                    return 1;
                }
            } else {
                // Seed everything
                $this->info('Seeding database...');
                Artisan::call('db:seed', ['--force' => true]);
                $this->info(Artisan::output());
            }

            $time = round(microtime(true) - $start, 2);
            $this->info("Database refresh completed in {$time} seconds.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during database refresh: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
