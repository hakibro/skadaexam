<?php
// Test script untuk debug frontend progress bar dengan AJAX requests
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImportProgress;
use Illuminate\Support\Facades\Log;

// Simulate frontend AJAX polling
function simulateFrontendPolling($userId)
{
    // This simulates the getImportProgress controller method
    $importProgress = ImportProgress::where('user_id', $userId)
        ->where('type', 'siswa_import')
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$importProgress) {
        return [
            'progress' => 0,
            'status' => 'idle',
            'message' => 'Ready to import',
            'current_step' => 'ready',
            'current_item' => 0,
            'total_items' => 0,
            'percentage' => 0,
            'timestamp' => now()->timestamp
        ];
    }

    // Calculate progress percentage based on current step and items
    $stepProgress = 0;
    if ($importProgress->current_step === 'api') {
        $stepProgress = 10;
    } elseif ($importProgress->current_step === 'kelas') {
        $stepProgress = 30 + ($importProgress->getProgressPercentage() * 0.2); // 30-50%
    } elseif ($importProgress->current_step === 'siswa') {
        $stepProgress = 50 + ($importProgress->getProgressPercentage() * 0.5); // 50-100%
    }

    return [
        'progress' => round($stepProgress, 1),
        'status' => $importProgress->status,
        'message' => $importProgress->message,
        'current_step' => $importProgress->current_step,
        'current_item' => $importProgress->current_item,
        'total_items' => $importProgress->total_items,
        'percentage' => $importProgress->getProgressPercentage(),
        'batch_info' => $importProgress->batch_info,
        'timestamp' => now()->timestamp
    ];
}

echo "=== Testing Frontend Progress Bar Display Issue ===\n\n";

// Clean up any existing test data
$testUserId = 'test_frontend_' . time();
ImportProgress::where('user_id', 'LIKE', 'test_frontend_%')->delete();

// Test 1: Verify initial state
echo "1. Testing initial progress state...\n";
$initialProgress = simulateFrontendPolling($testUserId);
echo "Initial state: " . json_encode($initialProgress, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Start import and test polling during different phases
echo "2. Starting import with slow progress updates...\n";
$progress = ImportProgress::startImport($testUserId, 'siswa_import');
echo "Started import with ID: {$progress->id}\n";

// Test API phase
echo "\n3. Testing API phase...\n";
$progress->updateStep('api', 'Connecting to SIKEU API...');
for ($i = 0; $i < 3; $i++) {
    $apiProgress = simulateFrontendPolling($testUserId);
    echo "API Polling {$i}: Progress={$apiProgress['progress']}%, Message='{$apiProgress['message']}'\n";
    sleep(1);
}

// Test Kelas phase
echo "\n4. Testing Kelas phase...\n";
$progress->updateStep('kelas', 'Processing class data...');
for ($batch = 1; $batch <= 5; $batch++) {
    $progress->updateProgress($batch, 5, "Processing class batch {$batch} of 5");
    $kelasProgress = simulateFrontendPolling($testUserId);
    echo "Kelas Batch {$batch}: Progress={$kelasProgress['progress']}%, Message='{$kelasProgress['message']}'\n";
    sleep(2); // Longer delay to simulate real processing
}

// Test Siswa phase
echo "\n5. Testing Siswa phase...\n";
$progress->updateStep('siswa', 'Processing student data...');
for ($batch = 1; $batch <= 10; $batch++) {
    $batchStart = ($batch - 1) * 100 + 1;
    $batchEnd = $batch * 100;
    $progress->updateProgress($batch, 10, "Processing batch {$batch} of 10 (students {$batchStart}-{$batchEnd})");
    $siswaProgress = simulateFrontendPolling($testUserId);
    echo "Siswa Batch {$batch}: Progress={$siswaProgress['progress']}%, Message='{$siswaProgress['message']}'\n";
    sleep(1);
}

// Test completion
echo "\n6. Testing completion...\n";
$progress->complete('Import completed successfully!');
$finalProgress = simulateFrontendPolling($testUserId);
echo "Final state: " . json_encode($finalProgress, JSON_PRETTY_PRINT) . "\n";

// Test 3: Check for user_id issues
echo "\n7. Testing user_id consistency...\n";
$authUserId = null; // Simulating no authenticated user
$sessionId = 'test_session_123';

$userId1 = $authUserId ?? 'guest_' . $sessionId;
echo "Generated user_id: {$userId1}\n";

// Create progress with this user_id
$testProgress = ImportProgress::startImport($userId1, 'siswa_import');
echo "Created progress for user_id: {$userId1}\n";

// Test retrieval with same logic
$retrieved = ImportProgress::where('user_id', $userId1)
    ->where('type', 'siswa_import')
    ->orderBy('created_at', 'desc')
    ->first();

if ($retrieved) {
    echo "Successfully retrieved progress for user_id: {$userId1}\n";
} else {
    echo "ERROR: Could not retrieve progress for user_id: {$userId1}\n";
}

// Clean up
ImportProgress::where('user_id', 'LIKE', 'test_frontend_%')->delete();
$testProgress->delete();

echo "\n8. Recommendations for fixing progress bar issue:\n";
echo "- Ensure JavaScript polling interval is not too fast (current: 1000ms is good)\n";
echo "- Check browser developer tools Network tab for AJAX requests to import-progress\n";
echo "- Verify import process is not completing too quickly for polling to catch updates\n";
echo "- Add console.log statements in JavaScript to track polling responses\n";
echo "- Check that session()->getId() returns consistent values\n";

echo "\n=== Frontend Debug Test Complete ===\n";
