<?php

/**
 * Batch Sync Integration Test Script
 * 
 * This script verifies that the batch sync functionality works correctly
 * with real database operations.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Start Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Http\Controllers\Features\Data\BatchSiswaProcessor;

echo "Starting Batch Sync Integration Test...\n\n";

// Create a test kelas if it doesn't exist
$kelas = Kelas::firstOrCreate(
    ['nama_kelas' => 'TEST KELAS'],
    [
        'tingkat' => 'X',
        'jurusan' => 'TEST'
    ]
);

echo "Using test kelas: {$kelas->nama_kelas} (ID: {$kelas->id})\n";

// Create test data for syncing
$testData = [
    [
        'idyayasan' => 'TEST001',
        'nama' => 'Test Student 1',
        'kelas' => 'TEST KELAS',
        'status_pembayaran' => 'Lunas'
    ],
    [
        'idyayasan' => 'TEST002',
        'nama' => 'Test Student 2',
        'kelas' => 'TEST KELAS',
        'status_pembayaran' => 'Belum Lunas'
    ]
];

// Clean up any existing test students
Siswa::where('idyayasan', 'like', 'TEST%')->delete();
echo "Cleaned up existing test students\n";

// First test: Create new students
echo "\nTEST 1: Creating new students through batch sync\n";
echo "---------------------------------------------\n";

try {
    DB::beginTransaction();

    $result = BatchSiswaProcessor::processBatchSync($testData, 10, 0);

    echo "Batch Sync Test Results:\n";
    echo "Created students: {$result['created_siswa']}\n";
    echo "Updated students: {$result['updated_siswa']}\n";
    echo "Skipped: {$result['skipped']}\n";
    echo "Errors: " . count($result['errors']) . "\n";

    if (count($result['errors']) > 0) {
        echo "\nErrors encountered:\n";
        foreach ($result['errors'] as $error) {
            echo "- " . (is_string($error) ? $error : json_encode($error)) . "\n";
        }
    }

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Verify the students were created
$students = Siswa::where('idyayasan', 'like', 'TEST%')->get();
echo "\nVerifying created students:\n";
foreach ($students as $student) {
    echo "- {$student->idyayasan}: {$student->nama} ({$student->kelas->nama_kelas})\n";
}

// Second test: Update one of the students
echo "\nTEST 2: Updating existing student through batch sync\n";
echo "------------------------------------------------\n";

// Modify one record to test update functionality
$testData[0]['nama'] = 'Test Student 1 (Updated)';

try {
    DB::beginTransaction();

    $result = BatchSiswaProcessor::processBatchSync($testData, 10, 0);

    echo "Second Batch Sync Test Results:\n";
    echo "Created students: {$result['created_siswa']}\n";
    echo "Updated students: {$result['updated_siswa']}\n";
    echo "Skipped: {$result['skipped']}\n";
    echo "Errors: " . count($result['errors']) . "\n";

    if (count($result['errors']) > 0) {
        echo "\nErrors encountered:\n";
        foreach ($result['errors'] as $error) {
            echo "- " . (is_string($error) ? $error : json_encode($error)) . "\n";
        }
    }

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Verify the student was updated
$students = Siswa::where('idyayasan', 'like', 'TEST%')->orderBy('idyayasan')->get();
echo "\nVerifying updated students:\n";
foreach ($students as $student) {
    echo "- {$student->idyayasan}: {$student->nama} ({$student->kelas->nama_kelas})\n";
}

echo "\nTest completed successfully!\n";
