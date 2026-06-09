<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Http\Controllers\Features\Data\SiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Check if routes exist
$routes = Route::getRoutes();
$batchRoutes = 0;
$normalRoutes = 0;

echo "=== CHECKING ROUTES ===\n";
foreach ($routes as $route) {
    if (strpos($route->uri(), "siswa/batch-") !== false) {
        echo "Found batch route: " . $route->uri() . "\n";
        $batchRoutes++;
    }
    
    if ($route->uri() == "data/siswa/import-progress" || 
        $route->uri() == "data/siswa/import-from-api-ajax" ||
        $route->uri() == "data/siswa/sync-progress") {
        echo "Found required route: " . $route->uri() . "\n";
        $normalRoutes++;
    }
}

echo "\nBatch routes found: " . $batchRoutes . " (should be 0)\n";
echo "Normal routes found: " . $normalRoutes . " (should be 3)\n";

// Check SiswaController
echo "\n=== CHECKING CONTROLLER ===\n";
$controller = new SiswaController(app()->make("App\Services\SikeuApiService"));
$methods = get_class_methods($controller);
$batchMethods = 0;

foreach ($methods as $method) {
    if (strpos($method, "batch") !== false || strpos($method, "Batch") !== false) {
        echo "Found batch method: " . $method . "\n";
        $batchMethods++;
    }
}

echo "\nBatch methods found: " . $batchMethods . " (should be 0)\n";
echo "Controller methods total: " . count($methods) . "\n";

echo "\nTest completed.\n";

