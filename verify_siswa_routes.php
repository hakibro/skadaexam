<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================\n";
echo "SISWA LOGIN ROUTE VERIFICATION\n";
echo "======================================\n\n";

// Verify all siswa routes are properly defined
echo "Checking siswa routes...\n";
$routes = Route::getRoutes();

// Get all siswa routes
$siswaRoutes = [];
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && strpos($name, 'siswa.') === 0) {
        $siswaRoutes[$name] = [
            'uri' => $route->uri(),
            'method' => implode('|', $route->methods()),
            'action' => $route->getActionName(),
            'middleware' => implode(', ', $route->middleware())
        ];
    }
}

// Print out siswa routes in order
ksort($siswaRoutes);
foreach ($siswaRoutes as $name => $info) {
    echo "  {$name}:\n";
    echo "    URI: {$info['uri']} ({$info['method']})\n";
    echo "    Action: {$info['action']}\n";
    echo "    Middleware: {$info['middleware']}\n";

    // Check if route has auth:siswa middleware
    if (strpos($info['middleware'], 'auth:siswa') !== false) {
        echo "    ✓ Has auth:siswa middleware\n";
    } else {
        echo "    ✗ Missing auth:siswa middleware\n";
    }
    echo "\n";
}

// Verify key routes exist
echo "Checking critical routes...\n";
$criticalRoutes = [
    'siswa.dashboard',
    'siswa.exam',
    'siswa.exam.save-answer',
    'siswa.exam.flag-question',
    'siswa.exam.submit',
    'siswa.exam.logout',
    'siswa.logout'
];

foreach ($criticalRoutes as $route) {
    if (isset($siswaRoutes[$route])) {
        echo "  ✓ {$route} is defined\n";
    } else {
        echo "  ✗ {$route} is NOT defined\n";
    }
}

echo "\nSUMMARY:\n";
echo "Total siswa routes: " . count($siswaRoutes) . "\n";
echo "Critical routes found: " . count(array_intersect(array_keys($siswaRoutes), $criticalRoutes)) . " of " . count($criticalRoutes) . "\n";

echo "\n======================================\n";
