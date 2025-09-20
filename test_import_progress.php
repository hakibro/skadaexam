<?php
// Test script untuk verifikasi database-based import progress
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImportProgress;

echo "=== Testing Import Progress Database Functionality ===\n\n";

// Test 1: Create new import progress
echo "1. Creating new import progress...\n";
$userId = 'test_user_' . time();
$progress = ImportProgress::startImport($userId, 'siswa_import');
echo "Created import progress with ID: {$progress->id}\n";
echo "Status: {$progress->status}\n";
echo "Message: {$progress->message}\n\n";

// Test 2: Update step
echo "2. Updating step to 'kelas'...\n";
$progress->updateStep('kelas', 'Processing class data...');
echo "Current step: {$progress->current_step}\n";
echo "Message: {$progress->message}\n\n";

// Test 3: Update progress
echo "3. Updating progress to 50 of 100 items...\n";
$progress->updateProgress(50, 100, 'Processing batch 1 of 2');
echo "Current item: {$progress->current_item}\n";
echo "Total items: {$progress->total_items}\n";
echo "Progress percentage: {$progress->getProgressPercentage()}%\n";
echo "Message: {$progress->message}\n\n";

// Test 4: Complete import
echo "4. Completing import...\n";
$progress->complete('Import successfully completed!');
echo "Status: {$progress->status}\n";
echo "Message: {$progress->message}\n";
echo "Completed at: {$progress->completed_at}\n\n";

// Test 5: Retrieve progress by user ID
echo "5. Retrieving progress by user ID...\n";
$retrieved = ImportProgress::where('user_id', $userId)
    ->where('type', 'siswa_import')
    ->orderBy('created_at', 'desc')
    ->first();

if ($retrieved) {
    echo "Retrieved progress:\n";
    echo "- ID: {$retrieved->id}\n";
    echo "- Status: {$retrieved->status}\n";
    echo "- Step: {$retrieved->current_step}\n";
    echo "- Progress: {$retrieved->current_item}/{$retrieved->total_items} ({$retrieved->getProgressPercentage()}%)\n";
    echo "- Message: {$retrieved->message}\n";
} else {
    echo "No progress found for user ID: $userId\n";
}

// Clean up
echo "\n6. Cleaning up test data...\n";
ImportProgress::where('user_id', $userId)->delete();
echo "Test data cleaned up.\n";

echo "\n=== Test Complete ===\n";
