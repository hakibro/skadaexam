<?php

/**
 * Test script for batch sync functionality
 * 
 * This standalone script simulates the batch sync API endpoints
 * for testing the batch-siswa-fixed.js functionality
 */

// Bootstrap Laravel application
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Make the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();

// Use Laravel's facades
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Carbon;

// Parse the request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Function to handle JSON responses
function jsonResponse($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Function to get request data
function getRequestData()
{
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

// Simulate routing
if ($method === 'GET' && (strpos($path, '/test_batch_sync_js.php') === 0 || $path === '/')) {
    // Main test page route
    echo View::make('test_batch_sync')->render();
    exit;
} else if ($method === 'GET' && strpos($path, '/data/siswa/batch-sync-status') !== false) {
    // Get batch sync status
    $batchInfo = Session::get('batch_sync_info');

    // If no batch info is found, return error
    if (!$batchInfo) {
        jsonResponse([
            'status' => 'error',
            'message' => 'No active batch sync found',
        ]);
    }

    // Return batch status
    jsonResponse([
        'status' => $batchInfo['current_batch'] >= $batchInfo['total_batches'] ? 'completed' : 'processing',
        'progress' => $batchInfo['progress'],
        'message' => "Processing batch {$batchInfo['current_batch']}/{$batchInfo['total_batches']}",
        'results' => $batchInfo['results'],
    ]);
} else if ($method === 'POST' && strpos($path, '/data/siswa/batch-sync') !== false && strpos($path, '/process/') === false) {
    // Log the request
    $requestData = getRequestData();
    Log::info('Batch sync initiated', $requestData);

    // Store batch info in session
    Session::put('batch_sync_info', [
        'total_batches' => 5,
        'current_batch' => 0,
        'progress' => 0,
        'results' => [
            'created_kelas' => 0,
            'updated_kelas' => 0,
            'created_siswa' => 0,
            'updated_siswa' => 0,
            'skipped' => 0,
            'errors' => [],
        ],
        'start_time' => Carbon::now(),
    ]);

    // Return response
    jsonResponse([
        'success' => true,
        'status' => 'processing',
        'message' => 'Batch sync initiated',
        'progress' => 0,
        'current_batch' => 1,
        'total_batches' => 5,
        'next_batch_url' => '/data/siswa/batch-sync/process/1',
    ]);
} else if ($method === 'POST' && strpos($path, '/data/siswa/batch-sync/process/') !== false) {
    // Extract batch number from URL
    preg_match('/\/process\/(\d+)/', $path, $matches);
    $batch = isset($matches[1]) ? (int)$matches[1] : 1;

    // Log the request
    $requestData = getRequestData();
    Log::info("Processing batch {$batch}", $requestData);

    // Get batch info from session
    $batchInfo = Session::get('batch_sync_info', [
        'total_batches' => 5,
        'current_batch' => 0,
        'progress' => 0,
        'results' => [
            'created_kelas' => 0,
            'updated_kelas' => 0,
            'created_siswa' => 0,
            'updated_siswa' => 0,
            'skipped' => 0,
            'errors' => [],
        ],
        'start_time' => Carbon::now(),
    ]);

    // Increment batch counter
    $batchInfo['current_batch'] = $batch;

    // Calculate progress
    $batchInfo['progress'] = min(100, (int)($batch / $batchInfo['total_batches'] * 100));

    // Generate random batch results
    $batchResults = [
        'created_kelas' => rand(0, 3),
        'updated_kelas' => rand(0, 5),
        'created_siswa' => rand(5, 15),
        'updated_siswa' => rand(5, 20),
        'skipped' => rand(0, 3),
        'errors' => [],
    ];

    // Random chance to add an error
    if (rand(0, 10) > 7) {
        $batchResults['errors'] = ["Error processing student with NISN " . rand(10000, 99999)];
    }

    // Update cumulative results
    $batchInfo['results']['created_kelas'] += $batchResults['created_kelas'];
    $batchInfo['results']['updated_kelas'] += $batchResults['updated_kelas'];
    $batchInfo['results']['created_siswa'] += $batchResults['created_siswa'];
    $batchInfo['results']['updated_siswa'] += $batchResults['updated_siswa'];
    $batchInfo['results']['skipped'] += $batchResults['skipped'];

    if (!empty($batchResults['errors'])) {
        $batchInfo['results']['errors'] = array_merge(
            $batchInfo['results']['errors'],
            $batchResults['errors']
        );
    }

    // Update session
    Session::put('batch_sync_info', $batchInfo);

    // Determine if this is the last batch
    $isLastBatch = $batch >= $batchInfo['total_batches'];
    $nextBatch = $isLastBatch ? null : $batch + 1;

    // Return response
    jsonResponse([
        'success' => true,
        'status' => $isLastBatch ? 'completed' : 'processing',
        'message' => $isLastBatch ? 'All batches processed successfully' : "Processing batch {$batch}/{$batchInfo['total_batches']}",
        'progress' => $batchInfo['progress'],
        'current_batch' => (int) $batch,
        'total_batches' => $batchInfo['total_batches'],
        'next_batch_url' => $nextBatch ? "/data/siswa/batch-sync/process/{$nextBatch}" : null,
        'batch_results' => $batchResults,
        'results' => $isLastBatch ? $batchInfo['results'] : null,
    ]);
} else if ($method === 'POST' && strpos($path, '/data/siswa/batch-sync-error') !== false) {
    // Log the error
    $requestData = getRequestData();
    Log::error('Batch sync error', $requestData);

    // Return response
    jsonResponse([
        'success' => true,
    ]);
} else {
    // Unknown route
    jsonResponse([
        'error' => 'Unknown route',
        'path' => $path,
        'method' => $method
    ], 404);
}
