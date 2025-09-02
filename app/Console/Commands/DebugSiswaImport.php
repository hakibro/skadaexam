<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SikeuApiService;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DebugSiswaImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-siswa-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting Siswa Import Debug...');
        $this->newLine();

        // Step 1: Check Database Connection
        $this->info('1. Checking database connection...');
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return 1;
        }

        // Step 2: Check Table Structure
        $this->info('2. Checking siswa table structure...');
        try {
            $columns = DB::select("SHOW COLUMNS FROM siswa");
            $this->info('✅ Siswa table exists with columns:');
            foreach ($columns as $column) {
                $this->line("   - {$column->Field} ({$column->Type})");
            }
        } catch (\Exception $e) {
            $this->error('❌ Siswa table error: ' . $e->getMessage());
            $this->warn('Run: php artisan migrate');
            return 1;
        }

        // Step 3: Check API Service
        $this->info('3. Testing API Service...');
        try {
            $service = app(SikeuApiService::class);
            $connectionTest = $service->testConnection();

            if ($connectionTest['success']) {
                $this->info('✅ API connection: OK');
                $this->info("   Response time: {$connectionTest['response_time']}ms");
            } else {
                $this->error('❌ API connection failed: ' . $connectionTest['error']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ API Service error: ' . $e->getMessage());
            return 1;
        }

        // Step 4: Test API Data Fetch
        $this->info('4. Testing API data fetch...');
        try {
            $apiResult = $service->fetchSiswaData();

            if ($apiResult['success']) {
                $this->info('✅ API data fetch: OK');
                $this->info("   Total records: " . count($apiResult['data']));

                if (!empty($apiResult['data'])) {
                    $sample = $apiResult['data'][0];
                    $this->info('   Sample data:');
                    $this->line('   ' . json_encode($sample, JSON_PRETTY_PRINT));
                }
            } else {
                $this->error('❌ API data fetch failed: ' . $apiResult['error']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ API fetch error: ' . $e->getMessage());
            return 1;
        }

        // Step 5: Test Single Record Creation
        $this->info('5. Testing single record creation...');
        try {
            $testData = [
                'idyayasan' => 'TEST001',
                'nama' => 'Test Student',
                'email' => 'TEST001@smkdata.sch.id',
                'password' => bcrypt('password'),
                'kelas' => 'XII IPA 1',
                'status_pembayaran' => 'Lunas',
                'rekomendasi' => 'tidak'
            ];

            // Delete if exists
            Siswa::where('idyayasan', 'TEST001')->delete();

            $siswa = Siswa::create($testData);
            $this->info('✅ Test record creation: OK');
            $this->info("   Created siswa ID: {$siswa->id}");

            // Clean up
            $siswa->delete();
            $this->info('✅ Test record cleanup: OK');
        } catch (\Exception $e) {
            $this->error('❌ Record creation failed: ' . $e->getMessage());
            $this->error('   Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        // Step 6: Check Routes
        $this->info('6. Checking import routes...');
        try {
            $routes = [
                'data.siswa.import-from-api',
                'data.siswa.import-from-api-ajax',
                'data.siswa.test-api-connection'
            ];

            foreach ($routes as $routeName) {
                try {
                    $url = route($routeName);
                    $this->info("✅ Route {$routeName}: {$url}");
                } catch (\Exception $e) {
                    $this->error("❌ Route {$routeName} not found");
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Route check error: ' . $e->getMessage());
        }

        // Step 7: Check Permissions & Middleware
        $this->info('7. Testing authentication and permissions...');
        try {
            // Simulate user authentication for testing
            $this->info('   Note: Make sure you are logged in as admin or data role user when testing via web');
        } catch (\Exception $e) {
            $this->error('❌ Permission check error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 All checks completed!');
        $this->info('If all tests pass, try the import via web interface.');

        return 0;
    }
}
