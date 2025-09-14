<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================\n";
echo "FIXING SISWA LOGIN REDIRECT ISSUE\n";
echo "======================================\n\n";

// 1. Check if there are duplicate route names
echo "Checking for duplicate route names...\n";
$routes = Route::getRoutes();
$routeNames = [];
$duplicates = [];

foreach ($routes->getRoutesByName() as $name => $route) {
    if (isset($routeNames[$name])) {
        $duplicates[$name] = [
            'first' => $routeNames[$name],
            'second' => [
                'uri' => $route->uri(),
                'method' => $route->methods()[0],
                'action' => $route->getActionName(),
                'middleware' => implode(', ', $route->middleware())
            ]
        ];
    }
    $routeNames[$name] = [
        'uri' => $route->uri(),
        'method' => $route->methods()[0],
        'action' => $route->getActionName(),
        'middleware' => implode(', ', $route->middleware())
    ];
}

if (count($duplicates) > 0) {
    echo "Found duplicate route names:\n";
    foreach ($duplicates as $name => $info) {
        echo "  Route: {$name}\n";
        echo "    First definition: {$info['first']['uri']} ({$info['first']['method']}) - {$info['first']['action']}\n";
        echo "      Middleware: {$info['first']['middleware']}\n";
        echo "    Second definition: {$info['second']['uri']} ({$info['second']['method']}) - {$info['second']['action']}\n";
        echo "      Middleware: {$info['second']['middleware']}\n";
    }
} else {
    echo "No duplicate route names found.\n";
}

// 2. Check specifically for siswa.dashboard route
echo "\nChecking siswa.dashboard route:\n";
$siswaRoute = $routes->getByName('siswa.dashboard');
if ($siswaRoute) {
    echo "  URI: {$siswaRoute->uri()}\n";
    echo "  Method: {$siswaRoute->methods()[0]}\n";
    echo "  Action: {$siswaRoute->getActionName()}\n";
    echo "  Middleware: " . implode(', ', $siswaRoute->middleware()) . "\n";

    // Check if the route has auth:siswa middleware
    if (in_array('auth:siswa', $siswaRoute->middleware())) {
        echo "  ✓ Route has correct auth:siswa middleware\n";
    } else {
        echo "  ✗ Route does NOT have auth:siswa middleware\n";
        echo "  ISSUE: This is likely causing the login redirect problem\n";
    }
} else {
    echo "  ✗ siswa.dashboard route not found!\n";
}

// 3. Check for the loading order of routes files
echo "\nRoute files loading order (from web.php):\n";
echo "  1. routes/web.php (base routes)\n";
echo "  2. routes/auth_extended.php (auth routes)\n";
echo "  3. routes/admin.php\n";
echo "  4. routes/data.php\n";
echo "  5. routes/naskah.php\n";
echo "  6. routes/pengawas.php\n";
echo "  7. routes/guru_pengawas.php\n";
echo "  8. routes/koordinator.php\n";
echo "  9. routes/ruangan.php\n";
echo "  10. routes/ujian.php\n";
echo "  11. routes/enrollment.php\n";
echo "  12. routes/api_internal.php\n";
echo "  13. routes/fallback.php\n";
echo "  14. routes/debug.php and others\n";

// 4. Suggested fix
echo "\nSUGGESTED FIX:\n";
echo "1. Remove the siswa route group from data.php to avoid conflict with auth_extended.php\n";
echo "2. Ensure auth_extended.php has the correct middleware for siswa.dashboard route (auth:siswa)\n";
echo "3. Check homesplit.blade.php to ensure it uses @auth('siswa') correctly\n";
echo "4. Make sure SiswaLoginController redirects to route('siswa.dashboard')\n";

echo "\nIMPORTANT: After making these changes, clear the route cache:\n";
echo "php artisan route:clear\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";

echo "\n======================================\n";
