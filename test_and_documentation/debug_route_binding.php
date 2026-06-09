<?php

/**
 * Test with authenticated session
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== AUTHENTICATED TEST ===\n\n";

use Illuminate\Support\Facades\Route;

// Test route generation
echo "1. Testing route generation:\n";
try {
    $url = route('ujian.exam', ['jadwal_id' => 4]);
    echo "   Generated URL: {$url}\n";
    echo "   ✓ Route generation successful\n";
} catch (Exception $e) {
    echo "   ✗ Route generation failed: " . $e->getMessage() . "\n";
}

echo "\n2. Testing route matching:\n";
$routeCollection = Route::getRoutes();
$route = $routeCollection->match(
    \Illuminate\Http\Request::create('ujian/exam/4', 'GET')
);

if ($route) {
    echo "   ✓ Route matched\n";
    echo "   Route name: " . $route->getName() . "\n";
    echo "   Route URI: " . $route->uri() . "\n";
    echo "   Controller: " . $route->getAction('controller') . "\n";
    echo "   Parameters: " . json_encode($route->parameters()) . "\n";
} else {
    echo "   ✗ No route matched\n";
}

echo "\n3. Testing parameter extraction:\n";
$testRequest = \Illuminate\Http\Request::create('http://skadaexam.test/ujian/exam/4', 'GET');
echo "   Request URI: " . $testRequest->getRequestUri() . "\n";
echo "   Request method: " . $testRequest->getMethod() . "\n";

// Test Laravel's route parameter binding
if ($route) {
    $route->bind($testRequest);
    $params = $route->parameters();
    echo "   Bound parameters: " . json_encode($params) . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
