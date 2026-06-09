<?php

/**
 * Direct route parameter test
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Bootstrap minimal Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

// Simulate a simple request to test URL matching
echo "=== ROUTE PARAMETER DEBUG ===\n\n";

echo "1. Testing URL pattern matching...\n";

$testUrl = '/ujian/exam/4';
echo "   Test URL: {$testUrl}\n";

$pattern = 'ujian/exam/{jadwal_id}';
echo "   Route Pattern: {$pattern}\n";

// Check if URL matches pattern
if (preg_match('#^ujian/exam/(\d+)$#', trim($testUrl, '/'), $matches)) {
    echo "   ✓ Pattern matches!\n";
    echo "   Extracted jadwal_id: {$matches[1]}\n";
} else {
    echo "   ✗ Pattern doesn't match!\n";
}

echo "\n2. Current registered routes:\n";
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    $routes = Route::getRoutes();
    $ujianRoutes = [];

    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && str_contains($name, 'ujian')) {
            $ujianRoutes[] = [
                'name' => $name,
                'uri' => $route->uri(),
                'methods' => implode('|', $route->methods())
            ];
        }
    }

    foreach ($ujianRoutes as $route) {
        echo "   {$route['methods']} {$route['uri']} -> {$route['name']}\n";
    }
} catch (Exception $e) {
    echo "   Error loading routes: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
