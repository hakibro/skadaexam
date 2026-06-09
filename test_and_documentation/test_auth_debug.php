<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "======================================\n";
echo "Authentication Debug Tool\n";
echo "======================================\n\n";

// Get current auth configuration
echo "Auth Guards Configuration:\n";
$guards = config('auth.guards');
foreach ($guards as $guardName => $guardConfig) {
    echo "- {$guardName}: Provider = {$guardConfig['provider']}, Driver = {$guardConfig['driver']}\n";
}

echo "\nAuth Providers Configuration:\n";
$providers = config('auth.providers');
foreach ($providers as $providerName => $providerConfig) {
    echo "- {$providerName}: Model = {$providerConfig['model']}\n";
}

// Check existing routes for login
echo "\nLogin Routes:\n";
$routes = app('router')->getRoutes();
foreach ($routes->getRoutesByName() as $name => $route) {
    if (strpos($name, 'login') !== false) {
        echo "- {$name}: {$route->uri()}, Method: {$route->methods()[0]}, Action: {$route->getActionName()}\n";
    }
}

// Check dashboard routes
echo "\nDashboard Routes:\n";
foreach ($routes->getRoutesByName() as $name => $route) {
    if (strpos($name, 'dashboard') !== false) {
        $middleware = implode(', ', $route->middleware());
        echo "- {$name}: {$route->uri()}, Method: {$route->methods()[0]}, Action: {$route->getActionName()}\n";
        echo "  Middleware: {$middleware}\n";
    }
}

// Check logout routes
echo "\nLogout Routes:\n";
foreach ($routes->getRoutesByName() as $name => $route) {
    if (strpos($name, 'logout') !== false) {
        $middleware = implode(', ', $route->middleware());
        echo "- {$name}: {$route->uri()}, Method: {$route->methods()[0]}, Action: {$route->getActionName()}\n";
        echo "  Middleware: {$middleware}\n";
    }
}

// Test the login paths
echo "\nTest Redirect Paths:\n";
try {
    echo "- Siswa login path: " . route('login.siswa') . "\n";
    echo "- Siswa dashboard path: " . route('siswa.dashboard') . "\n";

    // Check intended URL for auth:siswa guard
    echo "\nChecking auth:siswa intended URL:\n";
    if (auth()->guard('siswa')->check()) {
        echo "Siswa is authenticated\n";
    } else {
        echo "Siswa is not authenticated\n";
    }
} catch (\Exception $e) {
    echo "Error testing paths: " . $e->getMessage() . "\n";
}

echo "\n======================================\n";
