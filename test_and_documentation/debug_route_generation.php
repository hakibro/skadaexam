<?php

/**
 * Test route generation with various parameters
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== ROUTE GENERATION TEST ===\n\n";

// Simulate the route generation in template context
$testParams = [
    ['jadwal_id' => 4],        // Normal
    ['jadwal_id' => '4'],      // String
    ['jadwal_id' => null],     // Null
    ['jadwal_id' => ''],       // Empty string
    ['jadwal_id' => 0],        // Zero
    []                         // No parameter
];

foreach ($testParams as $i => $params) {
    echo "Test " . ($i + 1) . ": " . json_encode($params) . "\n";

    try {
        $url = route('ujian.exam', $params);
        echo "   ✓ Generated URL: {$url}\n";
    } catch (Exception $e) {
        echo "   ✗ ERROR: " . $e->getMessage() . "\n";
        echo "   Class: " . get_class($e) . "\n";
    }
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
