<?php

/**
 * Test script to check the sync-progress route
 */

// Bootstrap Laravel application
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Make the HTTP kernel
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Make a fake request to the sync-progress route
$testRequest = Illuminate\Http\Request::create('/data/siswa/sync-progress', 'GET');

// Set AJAX headers
$testRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
$testRequest->headers->set('Accept', 'application/json');

// Set up a session for the request
$testRequest->setLaravelSession(app('session')->driver());

// Set some test session data
app('session')->put('sync_progress', 50);
app('session')->put('sync_status', 'in_progress');
app('session')->put('sync_message', 'Syncing student data...');

// Authenticate the test request as an admin user
// Find an admin user
$user = App\Models\User::whereHas('roles', function ($q) {
    $q->where('name', 'admin');
})->first();

// If no admin found, try to get any user
if (!$user) {
    $user = App\Models\User::first();
}

// Authenticate as this user
if ($user) {
    auth()->login($user);
    $testRequest->setUserResolver(function () use ($user) {
        return $user;
    });
    echo "<p>Test authenticated as: " . $user->name . " (ID: " . $user->id . ")</p>";
} else {
    echo "<p>WARNING: No user found for authentication test.</p>";
}

// Process the request
$response = $httpKernel->handle($testRequest);

// Output the response status
echo "<h2>Route Test Results</h2>";
echo "<p>Status code: " . $response->getStatusCode() . "</p>";
echo "<p>Content-Type: " . $response->headers->get('Content-Type') . "</p>";

// Output the response content
echo "<h3>Response Content:</h3>";
echo "<pre>";
echo htmlspecialchars($response->getContent());
echo "</pre>";

// Output debug info about the route
echo "<h3>Route Information:</h3>";
echo "<pre>";
try {
    $routes = app('router')->getRoutes();
    $route = $routes->match($testRequest);
    if ($route) {
        echo "Route found: " . $route->getName() . "\n";
        echo "Controller action: " . $route->getActionName() . "\n";
        echo "Middleware: " . implode(", ", $route->middleware()) . "\n";
    } else {
        echo "No matching route found!\n";
    }
} catch (Exception $e) {
    echo "Error checking route: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Output the route list
echo "<h3>All Registered Routes:</h3>";
echo "<pre>";
$routes = app('router')->getRoutes();
foreach ($routes->getRoutesByName() as $name => $route) {
    if (strpos($name, 'sync-progress') !== false) {
        echo "{$name}: {$route->uri()}\n";
        echo "   Method: " . implode('|', $route->methods()) . "\n";
        echo "   Action: {$route->getActionName()}\n";
        echo "   Middleware: " . implode(", ", $route->middleware()) . "\n\n";
    }
}
echo "</pre>";

// Check middleware groups
echo "<h3>Middleware Groups:</h3>";
echo "<pre>";
$middleware = app('router')->getMiddlewareGroups();
foreach ($middleware as $group => $middlewareList) {
    echo "$group: " . implode(", ", $middlewareList) . "\n";
}
echo "</pre>";

// Check the session
echo "<h3>Session:</h3>";
echo "<pre>";
echo "Session driver: " . config('session.driver') . "\n";
echo "Session cookie: " . config('session.cookie') . "\n";
echo "User authenticated: " . (auth()->check() ? 'Yes' : 'No') . "\n";
if (auth()->check()) {
    echo "User ID: " . auth()->id() . "\n";
    echo "User roles: " . implode(", ", auth()->user()->roles->pluck('name')->toArray()) . "\n";
}
echo "</pre>";
