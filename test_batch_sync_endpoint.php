<?php
// File: test_batch_sync_endpoint.php
// Purpose: Test the batch-sync-status endpoint for debugging

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

// Set up mock session data for testing
Session::put('batch_sync_status', 'processing');
Session::put('batch_sync_progress', 45);
Session::put('batch_sync_message', 'Test sync message');
Session::put('batch_sync_results', [
    'created_kelas' => 5,
    'updated_kelas' => 10,
    'created_siswa' => 50,
    'updated_siswa' => 25,
    'skipped' => 2,
    'errors' => []
]);
Session::put('batch_sync_data', [
    'current_batch' => 5,
    'batch_count' => 10
]);

// Get the controller
$controller = new \App\Http\Controllers\Features\Data\SiswaController(
    new \App\Services\SikeuApiService()
);

// Call the endpoint directly
$response = $controller->getBatchSyncStatus();

// Print the result
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
echo "Response Body:\n";
echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT);

// Clean up session for safety
Session::forget(['batch_sync_status', 'batch_sync_progress', 'batch_sync_message', 'batch_sync_results', 'batch_sync_data']);

echo "\n\nTest completed.\n";
