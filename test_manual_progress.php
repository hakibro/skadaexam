<?php
// Quick test untuk manual testing progress endpoint
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ImportProgress;

echo "=== Creating Test Progress for Manual Testing ===\n\n";

$testUserId = 'guest_manual_test';

// Clean up any existing test data
ImportProgress::where('user_id', $testUserId)->delete();

// Create test progress in siswa step with realistic data
$progress = ImportProgress::startImport($testUserId, 'siswa_import');
$progress->updateStep('siswa', 'Processing student data...');
$progress->updateProgress(7, 15, 'Processing batch 7 of 15 (students 601-700)');

echo "Created test progress record:\n";
echo "- ID: {$progress->id}\n";
echo "- User ID: {$progress->user_id}\n";
echo "- Step: {$progress->current_step}\n";
echo "- Progress: {$progress->current_item}/{$progress->total_items} ({$progress->getProgressPercentage()}%)\n";
echo "- Message: {$progress->message}\n";

// Calculate step progress as controller does
$stepProgress = 50 + ($progress->getProgressPercentage() * 0.5);
echo "- Calculated Step Progress: " . round($stepProgress, 1) . "%\n";

echo "\nNow you can test the progress endpoint at:\n";
echo "http://localhost:8000/features/data/siswa/import-progress\n";
echo "\nOr manually change session ID in browser dev tools to 'manual_test' and test polling.\n";

echo "\nTo clean up, run this command when done:\n";
echo "ImportProgress::where('user_id', '{$testUserId}')->delete();\n";
