<?php

/**
 * Test script to check the import-progress route
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Make the HTTP kernel
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Make a fake request to the import-progress route
$testRequest = Illuminate\Http\Request::create('/data/siswa/import-progress', 'GET');

// Set AJAX headers
$testRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
$testRequest->headers->set('Accept', 'application/json');

// Set up a session for the request
$testRequest->setLaravelSession(app('session')->driver());

echo "<h2>Testing Import Progress Session</h2>";

// First let's set some test session values to verify session handling
echo "<h3>Setting test session values...</h3>";
session(['import_progress' => 42]);
session(['import_status' => 'test_in_progress']);
session(['import_message' => 'This is a test message']);

// Verify session values were set
echo "<p>Session values directly after setting:</p>";
echo "<ul>";
echo "<li>import_progress: " . session('import_progress') . "</li>";
echo "<li>import_status: " . session('import_status') . "</li>";
echo "<li>import_message: " . session('import_message') . "</li>";
echo "</ul>";

// Authenticate as an admin user
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
echo "<h3>Calling import-progress route...</h3>";
$response = $httpKernel->handle($testRequest);

// Output the results
echo "<p>Status code: " . $response->getStatusCode() . "</p>";
echo "<p>Content-Type: " . $response->headers->get('Content-Type') . "</p>";

echo "<h3>Response Content:</h3>";
echo "<pre>";
echo htmlspecialchars($response->getContent());
echo "</pre>";

// Test route information
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

// Get session driver details
echo "<h3>Session Configuration:</h3>";
echo "<pre>";
echo "Session driver: " . config('session.driver') . "\n";
echo "Session cookie name: " . config('session.cookie') . "\n";
echo "Session lifetime: " . config('session.lifetime') . " minutes\n";
echo "Session path: " . config('session.path') . "\n";
echo "Session domain: " . config('session.domain') . "\n";
echo "</pre>";

// Check session file storage if using file driver
if (config('session.driver') === 'file') {
    echo "<h3>Session File Storage:</h3>";
    echo "<pre>";
    $path = config('session.files');
    echo "Session files path: " . $path . "\n";

    if (is_dir($path)) {
        echo "Session directory exists and is readable: " . (is_readable($path) ? "Yes" : "No") . "\n";
        echo "Session directory is writable: " . (is_writable($path) ? "Yes" : "No") . "\n";
    } else {
        echo "WARNING: Session directory does not exist!\n";
    }
    echo "</pre>";
}

// Check database session table if using database driver
if (config('session.driver') === 'database') {
    echo "<h3>Database Session Storage:</h3>";
    echo "<pre>";
    try {
        $table = config('session.table', 'sessions');
        $count = \Illuminate\Support\Facades\DB::table($table)->count();
        $recent = \Illuminate\Support\Facades\DB::table($table)->orderBy('last_activity', 'desc')->first();

        echo "Session table: {$table}\n";
        echo "Total session records: {$count}\n";

        if ($recent) {
            echo "\nMost recent session:\n";
            echo "- ID: " . $recent->id . "\n";
            echo "- User ID: " . ($recent->user_id ?? 'none') . "\n";
            echo "- IP Address: " . ($recent->ip_address ?? 'none') . "\n";
            echo "- Last Activity: " . date('Y-m-d H:i:s', $recent->last_activity) . "\n";

            // Find our test data if it exists
            $session = \Illuminate\Support\Facades\DB::table($table)->where('id', session()->getId())->first();
            if ($session) {
                echo "\nCurrent session found in database:\n";
                echo "- ID: " . $session->id . "\n";
                echo "- User ID: " . ($session->user_id ?? 'none') . "\n";
                echo "- Last Activity: " . date('Y-m-d H:i:s', $session->last_activity) . "\n";

                // Try to decode payload to find our test values
                if (isset($session->payload)) {
                    echo "- Payload exists: Yes\n";
                    // Laravel encrypts and serializes session data, so direct examination is difficult
                }
            } else {
                echo "\nWARNING: Current session not found in database!\n";
            }
        }
    } catch (Exception $e) {
        echo "Error checking database sessions: " . $e->getMessage() . "\n";
    }
    echo "</pre>";
}

echo "<h3>Test Complete</h3>";
