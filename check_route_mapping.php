<?php
// File: check_route_mapping.php
// Purpose: Check route details for data/siswa/batch-sync-status

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all routes
use Illuminate\Support\Facades\Route;

$routeCollection = Route::getRoutes();
$found = false;

echo "=== CHECKING ROUTES FOR BATCH-SYNC-STATUS ===\n\n";

// Helper to format route methods
function formatMethods($methods)
{
    return implode('|', array_filter($methods, function ($method) {
        return $method != 'HEAD';
    }));
}

// Search for the specific route
foreach ($routeCollection as $route) {
    if (strpos($route->uri(), 'batch-sync-status') !== false) {
        echo "FOUND ROUTE:\n";
        echo "URI: " . $route->uri() . "\n";
        echo "Methods: " . formatMethods($route->methods()) . "\n";
        echo "Name: " . $route->getName() . "\n";
        echo "Action: " . $route->getActionName() . "\n";
        echo "Domain: " . ($route->getDomain() ?: 'Default') . "\n";
        echo "Middleware: " . implode(', ', $route->middleware()) . "\n";

        // Try to generate URL for this route
        try {
            $url = url($route->uri());
            echo "Generated URL: " . $url . "\n";
            echo "Generated Named Route URL: " . route($route->getName()) . "\n";
        } catch (\Exception $e) {
            echo "Error generating URL: " . $e->getMessage() . "\n";
        }

        echo "\n";
        $found = true;
    }
}

if (!$found) {
    echo "No routes found containing 'batch-sync-status'\n";
} else {
    // Check if route works directly
    echo "=== TESTING ENDPOINT DIRECTLY ===\n";
    try {
        // Get the controller instance
        $controller = app()->make('App\Http\Controllers\Features\Data\SiswaController');

        // Mock an authenticated user if the route requires auth
        if (in_array('auth', $route->middleware()) || in_array('auth:web', $route->middleware())) {
            echo "Route requires authentication, mocking user...\n";
            // Find a user
            $user = \App\Models\User::first();
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user);
                echo "Logged in as user ID: " . $user->id . "\n";
            } else {
                echo "WARNING: No users found to test with!\n";
            }
        }

        // Call the method
        $response = $controller->getBatchSyncStatus();
        echo "Controller response status: " . $response->getStatusCode() . "\n";
        echo "Controller response content: " . $response->getContent() . "\n";
    } catch (\Exception $e) {
        echo "Error testing endpoint: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }

    // Check route info
    echo "\n=== ENVIRONMENT & APP CONFIGURATION ===\n";
    echo "APP_URL: " . config('app.url') . "\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "Current URL scheme: " . (request()->secure() ? 'https' : 'http') . "\n";
    echo "Server hostname: " . $_SERVER['HTTP_HOST'] ?? 'Not available' . "\n";

    // Check if it's a Valet/Laragon configuration
    $domainSuffix = '';
    if (file_exists('/etc/valet')) {
        $domainSuffix = '.test';
        echo "Valet detected, domain suffix: $domainSuffix\n";
    } elseif (getenv('LARAGON_ROOT')) {
        $domainSuffix = '.test'; // or could be .local depending on setup
        echo "Laragon detected, domain suffix: $domainSuffix\n";
    }

    echo "\n=== SUGGESTION ===\n";
    echo "When accessing from JavaScript, use one of these URLs:\n";
    echo "1. Absolute URL: " . url('data/siswa/batch-sync-status') . "\n";
    echo "2. Relative URL: /data/siswa/batch-sync-status\n";
    echo "3. Named route: " . route('data.siswa.batch-sync-status') . "\n";
}
