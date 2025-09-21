<?php

/**
 * Trigger route error test
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== ROUTE ERROR TRIGGER TEST ===\n\n";

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Test different scenarios that might trigger "Missing required parameter"
$testCases = [
    [
        'name' => 'URL without parameter',
        'url' => '/ujian/exam',
        'method' => 'GET'
    ],
    [
        'name' => 'URL with empty parameter',
        'url' => '/ujian/exam/',
        'method' => 'GET'
    ],
    [
        'name' => 'URL with invalid parameter',
        'url' => '/ujian/exam/abc',
        'method' => 'GET'
    ],
    [
        'name' => 'Correct URL',
        'url' => '/ujian/exam/4',
        'method' => 'GET'
    ]
];

foreach ($testCases as $test) {
    echo "Testing: {$test['name']}\n";
    echo "   URL: {$test['url']}\n";

    try {
        $request = Request::create($test['url'], $test['method']);
        $routeCollection = Route::getRoutes();
        $route = $routeCollection->match($request);

        if ($route) {
            echo "   ✓ Route matched: {$route->getName()}\n";
            echo "   Action: {$route->getActionName()}\n";

            // Try to bind parameters
            $route->bind($request);
            $params = $route->parameters();
            echo "   Parameters: " . json_encode($params) . "\n";
        } else {
            echo "   ✗ No route matched\n";
        }
    } catch (Exception $e) {
        echo "   ✗ ERROR: " . $e->getMessage() . "\n";
        echo "   Class: " . get_class($e) . "\n";
    }

    echo "\n";
}

echo "=== TEST COMPLETE ===\n";
