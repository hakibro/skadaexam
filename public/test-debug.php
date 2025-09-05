<?php

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$kernel->bootstrap();

// Test database connection
try {
    $connection = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<p>Database connected successfully. Database Name is: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "</p>";
} catch (\Exception $e) {
    echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
}

// Get route information
echo "<h2>Route Information</h2>";
echo "<p>Current route name: " . \Illuminate\Support\Facades\Route::currentRouteName() . "</p>";
echo "<p>Current route action: " . \Illuminate\Support\Facades\Route::currentRouteAction() . "</p>";

// Test route existence
$testRoutes = [
    'naskah.jadwal.show',
    'naskah.jadwalujian.show',
    'naskah.hasil.index',
    'naskah.hasilujian.index',
    'naskah.dashboard'
];

echo "<h2>Route Existence Tests</h2>";
echo "<ul>";
foreach ($testRoutes as $routeName) {
    try {
        $exists = \Illuminate\Support\Facades\Route::has($routeName);
        echo "<li>Route '{$routeName}' exists: " . ($exists ? 'Yes' : 'No') . "</li>";
    } catch (\Exception $e) {
        echo "<li>Error checking route '{$routeName}': " . $e->getMessage() . "</li>";
    }
}
echo "</ul>";

// Test JadwalUjian model loading
echo "<h2>JadwalUjian Tests</h2>";
try {
    $jadwal = App\Models\JadwalUjian::find(20); // Using ID 20 as mentioned in the issue
    if ($jadwal) {
        echo "<p>Found JadwalUjian with ID 20:</p>";
        echo "<pre>";
        print_r($jadwal->toArray());
        echo "</pre>";

        // Test relationships
        echo "<h3>Testing relationships:</h3>";
        echo "<ul>";
        echo "<li>mapel: " . ($jadwal->mapel ? "Loaded (" . $jadwal->mapel->nama_mapel . ")" : "Not loaded") . "</li>";
        echo "<li>bankSoal: " . ($jadwal->bankSoal ? "Loaded (" . $jadwal->bankSoal->judul . ")" : "Not loaded") . "</li>";
        echo "<li>creator: " . ($jadwal->creator ? "Loaded (" . $jadwal->creator->name . ")" : "Not loaded") . "</li>";
        echo "<li>sesiRuangan count: " . ($jadwal->sesiRuangan ? $jadwal->sesiRuangan->count() : 0) . "</li>";
        echo "</ul>";
    } else {
        echo "<p>JadwalUjian with ID 20 not found</p>";
    }
} catch (\Exception $e) {
    echo "<p>Error loading JadwalUjian: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

// Test view rendering
echo "<h2>View Rendering Test</h2>";
try {
    // Use the actual view with mock data
    $mockJadwal = App\Models\JadwalUjian::find(20); // Using ID 20 again
    if ($mockJadwal) {
        $html = view('features.naskah.jadwal.show', ['jadwal' => $mockJadwal])->render();
        echo "<p>View rendered successfully!</p>";
    } else {
        echo "<p>Could not test view rendering - mock data not available</p>";
    }
} catch (\Exception $e) {
    echo "<p>Error rendering view: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
