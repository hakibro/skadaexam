<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================\n";
echo "SISWA ROUTE AND LOGIN DIAGNOSTIC TOOL\n";
echo "======================================\n\n";

echo "ROUTE CONFIGURATION CHECK:\n";
echo "-------------------------\n";

// 1. Check for duplicate route names
$routeNames = [];
$duplicates = [];

foreach (Route::getRoutes()->getRoutesByName() as $name => $route) {
    if (isset($routeNames[$name])) {
        $duplicates[$name] = true;
    }
    $routeNames[$name] = true;
}

if (count($duplicates) > 0) {
    echo "❌ FOUND DUPLICATE ROUTE NAMES:\n";
    foreach (array_keys($duplicates) as $name) {
        echo "   - {$name}\n";
    }
} else {
    echo "✅ No duplicate route names found\n";
}

// 2. Check for siswa routes with proper middleware
$siswaRoutes = [];
$routesWithoutAuth = [];

foreach (Route::getRoutes()->getRoutesByName() as $name => $route) {
    if (strpos($name, 'siswa.') === 0) {
        $siswaRoutes[$name] = $route;

        $hasAuthMiddleware = false;
        foreach ($route->middleware() as $middleware) {
            if ($middleware === 'auth:siswa') {
                $hasAuthMiddleware = true;
                break;
            }
        }

        if (!$hasAuthMiddleware) {
            $routesWithoutAuth[$name] = $route;
        }
    }
}

echo "\nFOUND " . count($siswaRoutes) . " SISWA ROUTES\n";

if (count($routesWithoutAuth) > 0) {
    echo "❌ SISWA ROUTES WITHOUT AUTH:SISWA MIDDLEWARE:\n";
    foreach ($routesWithoutAuth as $name => $route) {
        echo "   - {$name}: " . implode(', ', $route->middleware()) . "\n";
    }
} else {
    echo "✅ All siswa routes have proper auth:siswa middleware\n";
}

// 3. Check login.siswa route
$loginSiswaRoute = Route::getRoutes()->getByName('login.siswa');
if ($loginSiswaRoute) {
    echo "\n✅ login.siswa route exists: " . $loginSiswaRoute->uri() . "\n";
} else {
    echo "\n❌ login.siswa route does not exist!\n";
}

// 4. Check siswa.dashboard route
$dashboardRoute = Route::getRoutes()->getByName('siswa.dashboard');
if ($dashboardRoute) {
    echo "✅ siswa.dashboard route exists: " . $dashboardRoute->uri() . "\n";
    echo "   Controller: " . $dashboardRoute->getActionName() . "\n";
} else {
    echo "❌ siswa.dashboard route does not exist!\n";
}

// 5. Check for conflicting URL patterns
$urlPatterns = [];
$conflictingUrls = [];

foreach (Route::getRoutes() as $route) {
    $uri = $route->uri();
    if (isset($urlPatterns[$uri])) {
        if (!in_array($uri, $conflictingUrls)) {
            $conflictingUrls[] = $uri;
        }
    } else {
        $urlPatterns[$uri] = true;
    }
}

echo "\nURL PATTERN CHECK:\n";
if (count($conflictingUrls) > 0) {
    echo "❌ FOUND " . count($conflictingUrls) . " CONFLICTING URL PATTERNS:\n";
    foreach ($conflictingUrls as $uri) {
        echo "   - {$uri}\n";

        // List all routes with this URI
        foreach (Route::getRoutes() as $route) {
            if ($route->uri() === $uri) {
                $methods = implode('|', $route->methods());
                $name = $route->getName() ?: '(unnamed)';
                echo "     * {$methods} -> {$name} ({$route->getActionName()})\n";
            }
        }
    }
} else {
    echo "✅ No conflicting URL patterns found (different methods for same URL are OK)\n";
}

// 6. Check logout route
$logoutRoute = Route::getRoutes()->getByName('siswa.logout');
if ($logoutRoute) {
    $methods = implode('|', $logoutRoute->methods());
    echo "\n✅ siswa.logout route exists: {$methods} {$logoutRoute->uri()}\n";
    if (in_array('POST', $logoutRoute->methods())) {
        echo "   ✅ siswa.logout accepts POST method\n";
    } else {
        echo "   ❌ WARNING: siswa.logout does not accept POST method\n";
    }
} else {
    echo "\n❌ siswa.logout route does not exist!\n";
}

// 7. Check exam.logout route
$examLogoutRoute = Route::getRoutes()->getByName('siswa.exam.logout');
if ($examLogoutRoute) {
    $methods = implode('|', $examLogoutRoute->methods());
    echo "✅ siswa.exam.logout route exists: {$methods} {$examLogoutRoute->uri()}\n";
    if (in_array('POST', $examLogoutRoute->methods())) {
        echo "   ✅ siswa.exam.logout accepts POST method\n";
    } else {
        echo "   ❌ WARNING: siswa.exam.logout does not accept POST method\n";
    }
} else {
    echo "❌ siswa.exam.logout route does not exist!\n";
}

echo "\n======================================\n";
echo "FINAL DIAGNOSIS:\n";
if (
    count($duplicates) == 0 &&
    count($routesWithoutAuth) == 0 &&
    $loginSiswaRoute &&
    $dashboardRoute &&
    $logoutRoute &&
    $examLogoutRoute &&
    in_array('POST', $logoutRoute->methods()) &&
    in_array('POST', $examLogoutRoute->methods())
) {
    echo "✅ ALL CHECKS PASSED: The routing system appears to be correctly configured.\n";
} else {
    echo "⚠️ SOME ISSUES FOUND: Please review the issues above.\n";
}
echo "======================================\n";
