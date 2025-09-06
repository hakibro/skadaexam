<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Features\Data\BatchSiswaProcessor;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;

// Log that we're running the test
Log::info('Running batch sync test script');

// Setup test data (2 fake student records)
$testData = [
    [
        'idyayasan' => 'TEST001',
        'nama' => 'Test Student 1',
        'kelas' => 'X IPA 1',
        'status_pembayaran' => 'Lunas'
    ],
    [
        'idyayasan' => 'TEST002',
        'nama' => 'Test Student 2',
        'kelas' => 'X IPA 2',
        'status_pembayaran' => 'Belum Lunas'
    ]
];

// Delete test students if they exist
Siswa::where('idyayasan', 'TEST001')->delete();
Siswa::where('idyayasan', 'TEST002')->delete();

// Process a batch with our test data
$result = BatchSiswaProcessor::processBatchSync($testData, 2, 0);

// Output the result
echo "Batch Sync Test Results:\n";
echo "Created students: " . $result['created_siswa'] . "\n";
echo "Updated students: " . $result['updated_siswa'] . "\n";
echo "Skipped: " . $result['skipped'] . "\n";
echo "Errors: " . count($result['errors']) . "\n";

if (!empty($result['errors'])) {
    echo "\nErrors encountered:\n";
    print_r($result['errors']);
}

// Now run again to test update functionality
$testData[0]['nama'] = 'Test Student 1 Updated';
$result = BatchSiswaProcessor::processBatchSync($testData, 2, 0);

echo "\nSecond Batch Sync Test Results:\n";
echo "Created students: " . $result['created_siswa'] . "\n";
echo "Updated students: " . $result['updated_siswa'] . "\n";
echo "Skipped: " . $result['skipped'] . "\n";
echo "Errors: " . count($result['errors']) . "\n";

// Cleanup test data
Siswa::where('idyayasan', 'TEST001')->delete();
Siswa::where('idyayasan', 'TEST002')->delete();

echo "\nTest completed successfully!\n";
