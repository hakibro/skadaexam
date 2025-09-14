<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Siswa;

echo "======================================\n";
echo "Authentication Redirect Test Tool\n";
echo "======================================\n\n";

// Test function for siswa login and redirect
function testSiswaLoginRedirect()
{
    // First attempt to clear any existing authentication
    Auth::guard('web')->logout();
    Auth::guard('siswa')->logout();

    echo "Current auth status before test:\n";
    echo "- Web guard authenticated: " . (Auth::guard('web')->check() ? "Yes" : "No") . "\n";
    echo "- Siswa guard authenticated: " . (Auth::guard('siswa')->check() ? "Yes" : "No") . "\n\n";

    // Find a siswa to log in
    $siswa = Siswa::first();
    if (!$siswa) {
        echo "No siswa found in database for test login\n";
        return;
    }

    echo "Attempting to login siswa: {$siswa->nama} (ID: {$siswa->id})\n";
    Auth::guard('siswa')->login($siswa);

    echo "Auth status after login attempt:\n";
    echo "- Web guard authenticated: " . (Auth::guard('web')->check() ? "Yes" : "No") . "\n";
    echo "- Siswa guard authenticated: " . (Auth::guard('siswa')->check() ? "Yes" : "No") . "\n";

    if (Auth::guard('siswa')->check()) {
        $loggedInSiswa = Auth::guard('siswa')->user();
        echo "- Logged in as: {$loggedInSiswa->nama}\n\n";

        // Now test the redirect URL
        echo "Testing redirect URL:\n";
        $targetRoute = 'siswa.dashboard';

        if (Route::has($targetRoute)) {
            $url = route($targetRoute);
            echo "- Route '{$targetRoute}' exists\n";
            echo "- Target URL: {$url}\n";

            // Check middleware on the route
            $route = Route::getRoutes()->getByName($targetRoute);
            if ($route) {
                echo "- Route middleware: " . implode(', ', $route->middleware()) . "\n";

                // Check if auth:siswa middleware is present
                if (in_array('auth:siswa', $route->middleware())) {
                    echo "- ✓ Route has auth:siswa middleware\n";
                } else {
                    echo "- ✗ Route does NOT have auth:siswa middleware!\n";
                }
            }
        } else {
            echo "- ✗ Route '{$targetRoute}' does NOT exist!\n";
        }
    } else {
        echo "Failed to login siswa\n";
    }
}

// Run the test
testSiswaLoginRedirect();

echo "\n======================================\n";
