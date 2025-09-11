<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateGuruToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-guru-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates all guru users to the main users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of guru users to users table...');

        // Run the migration to add user_id column
        $this->info('Running migration to add user_id column to guru table...');
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_09_10_123145_add_user_id_to_guru_table.php']);
        $this->info('Migration completed.');

        // Run the seeder to migrate users
        $this->info('Running seeder to migrate guru users...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\MigrateGuruToUserSeeder']);
        $this->info('Seeder completed.');

        $this->info('Migration process completed successfully!');

        return Command::SUCCESS;
    }
}
