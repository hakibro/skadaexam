<?php
// Test script untuk debug proses import dengan progress tracking real-time
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImportProgress;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

echo "=== Testing Import Progress Real-time Updates ===\n\n";

// Clean up any existing test progress
$testUserId = 'test_debug_' . time();
ImportProgress::where('user_id', 'LIKE', 'test_debug_%')->delete();

echo "1. Starting simulated import with progress tracking...\n";

// Create import progress
$progress = ImportProgress::startImport($testUserId, 'siswa_import');
echo "Created progress record ID: {$progress->id}\n";

// Simulate API step
echo "\n2. Simulating API connection...\n";
$progress->updateStep('api', 'Connecting to SIKEU API...');
echo "Updated step to: {$progress->current_step}\n";
sleep(1);

// Simulate kelas processing
echo "\n3. Simulating kelas processing...\n";
$progress->updateStep('kelas', 'Processing class data...');
$progress->updateProgress(0, 5, 'Processing class batch 1 of 5');
echo "Processing kelas batches...\n";

for ($i = 1; $i <= 5; $i++) {
    $progress->updateProgress($i, 5, "Processing class batch {$i} of 5");
    echo "  - Batch {$i}/5: {$progress->getProgressPercentage()}%\n";
    sleep(1); // Simulate processing time
}

// Simulate siswa processing
echo "\n4. Simulating siswa processing...\n";
$progress->updateStep('siswa', 'Processing student data...');
echo "Processing siswa batches...\n";

for ($i = 1; $i <= 10; $i++) {
    $batchStart = ($i - 1) * 100 + 1;
    $batchEnd = $i * 100;
    $progress->updateProgress($i, 10, "Processing batch {$i} of 10 (students {$batchStart}-{$batchEnd})");
    echo "  - Batch {$i}/10: {$progress->getProgressPercentage()}% - {$progress->message}\n";
    sleep(1); // Simulate processing time

    // Test progress retrieval during processing
    $retrieved = ImportProgress::find($progress->id);
    echo "    Database state: Step={$retrieved->current_step}, Progress={$retrieved->getProgressPercentage()}%\n";
}

// Complete the import
echo "\n5. Completing import...\n";
$progress->complete('Import successfully completed with 50 classes and 1000 students');
echo "Final status: {$progress->status}\n";
echo "Final message: {$progress->message}\n";

// Test the getImportProgress method functionality
echo "\n6. Testing progress retrieval method...\n";

// Simulate how the controller retrieves progress
$retrievedProgress = ImportProgress::where('user_id', $testUserId)
    ->where('type', 'siswa_import')
    ->orderBy('created_at', 'desc')
    ->first();

if ($retrievedProgress) {
    // Calculate step progress like in controller
    $stepProgress = 0;
    if ($retrievedProgress->current_step === 'api') {
        $stepProgress = 10;
    } elseif ($retrievedProgress->current_step === 'kelas') {
        $stepProgress = 30 + ($retrievedProgress->getProgressPercentage() * 0.2);
    } elseif ($retrievedProgress->current_step === 'siswa') {
        $stepProgress = 50 + ($retrievedProgress->getProgressPercentage() * 0.5);
    }

    echo "Retrieved progress data:\n";
    echo "- Status: {$retrievedProgress->status}\n";
    echo "- Step: {$retrievedProgress->current_step}\n";
    echo "- Item Progress: {$retrievedProgress->current_item}/{$retrievedProgress->total_items}\n";
    echo "- Progress Percentage: {$retrievedProgress->getProgressPercentage()}%\n";
    echo "- Calculated Step Progress: " . round($stepProgress, 1) . "%\n";
    echo "- Message: {$retrievedProgress->message}\n";
}

// Test polling scenario
echo "\n7. Testing polling scenario during active import...\n";

// Start a new import to test polling
$pollingProgress = ImportProgress::startImport($testUserId . '_polling', 'siswa_import');
echo "Started polling test import ID: {$pollingProgress->id}\n";

// Simulate background processing while testing polling
for ($step = 1; $step <= 3; $step++) {
    $stepNames = ['api', 'kelas', 'siswa'];
    $currentStep = $stepNames[$step - 1];

    $pollingProgress->updateStep($currentStep, "Processing step {$step}: {$currentStep}");

    for ($i = 1; $i <= 5; $i++) {
        $pollingProgress->updateProgress($i, 5, "Step {$step} - Processing item {$i} of 5");

        // Test polling retrieval
        $polledData = ImportProgress::where('user_id', $testUserId . '_polling')
            ->where('type', 'siswa_import')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($polledData) {
            $polledStepProgress = 0;
            if ($polledData->current_step === 'api') {
                $polledStepProgress = 10;
            } elseif ($polledData->current_step === 'kelas') {
                $polledStepProgress = 30 + ($polledData->getProgressPercentage() * 0.2);
            } elseif ($polledData->current_step === 'siswa') {
                $polledStepProgress = 50 + ($polledData->getProgressPercentage() * 0.5);
            }

            echo "  Poll {$step}.{$i}: Step={$polledData->current_step}, Progress=" . round($polledStepProgress, 1) . "%, Message={$polledData->message}\n";
        }

        usleep(500000); // 0.5 second delay
    }
}

$pollingProgress->complete('Polling test completed');

// Clean up test data
echo "\n8. Cleaning up test data...\n";
ImportProgress::where('user_id', 'LIKE', $testUserId . '%')->delete();
echo "Test data cleaned up.\n";

echo "\n=== Debug Test Complete ===\n";
echo "\nKey findings to check:\n";
echo "1. Progress records are created and updated correctly\n";
echo "2. Database queries return latest progress data\n";
echo "3. Step progress calculation works as expected\n";
echo "4. Polling frequency allows real-time updates\n";
echo "\nIf progress bar still doesn't show:\n";
echo "- Check browser console for JavaScript errors\n";
echo "- Verify AJAX requests are being made to /data/siswa/import-progress\n";
echo "- Check that user_id matching works correctly in actual import\n";
