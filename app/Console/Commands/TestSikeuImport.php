<?php
// php artisan make:command TestSikeuImport



namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SikeuApiService;
use App\Models\Siswa;

class TestSikeuImport extends Command
{
    protected $signature = 'sikeu:test-import';
    protected $description = 'Test SIKEU API import process';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Testing SIKEU API Import...');

        $service = new SikeuApiService();

        // Test connection
        $this->info('1. Testing connection...');
        $connectionResult = $service->testConnection();

        if (!$connectionResult['success']) {
            $this->error('Connection failed: ' . $connectionResult['error']);
            return 1;
        }

        $this->info('✓ Connection successful');

        // Test single student
        $this->info('2. Testing single student fetch...');
        $singleResult = $service->testFetchSingleStudent();

        if (!$singleResult['success']) {
            $this->error('Single student test failed: ' . $singleResult['error']);
            return 1;
        }

        $this->info('✓ Single student fetch successful');
        $this->info('Total students available: ' . $singleResult['total_students']);

        if ($singleResult['first_student_transformed']) {
            $this->info('Sample transformed data:');
            $this->line(json_encode($singleResult['first_student_transformed'], JSON_PRETTY_PRINT));
        }

        // Test full import
        $this->info('3. Testing full import...');
        $this->warn('This will import all students. Continue? (y/n)');

        $confirm = $this->ask('Proceed with full import?', 'n');

        if (strtolower($confirm) !== 'y') {
            $this->info('Import cancelled.');
            return 0;
        }

        $importResult = $service->fetchSiswaData();

        if (!$importResult['success']) {
            $this->error('Import failed: ' . $importResult['error']);
            return 1;
        }

        $this->info('✓ Import data ready');
        $this->info('Total records to process: ' . count($importResult['data']));

        // Process import
        $created = 0;
        $updated = 0;
        $errors = 0;

        foreach ($importResult['data'] as $studentData) {
            try {
                $existing = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                if ($existing) {
                    $existing->update($studentData);
                    $updated++;
                } else {
                    Siswa::create($studentData);
                    $created++;
                }
            } catch (\Exception $e) {
                $this->error('Error processing: ' . $studentData['idyayasan'] . ' - ' . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Import completed!");
        $this->info("Created: {$created}");
        $this->info("Updated: {$updated}");
        $this->info("Errors: {$errors}");

        return 0;
    }
}
