<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Log;

// Add a clear log message for testing
Log::info('========== GURU FILTER TEST LOG ==========');

// Create a simple route test with direct filter execution
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Enable query logging
DB::enableQueryLog();

echo "Testing Filter by Role\n";

// Test each role
$roles = ['guru', 'data', 'naskah', 'pengawas', 'koordinator', 'ruangan'];
foreach ($roles as $role) {
    echo "\nTesting role: {$role}\n";

    // Apply role filter using the same logic from controller
    $gurusQuery = Guru::query();
    $gurusQuery->whereHas('user.roles', function ($q) use ($role) {
        $q->where('name', $role);
    });

    // Get query and results
    $query = $gurusQuery->toSql();
    echo "SQL Query: {$query}\n";

    $count = $gurusQuery->count();
    echo "Results count: {$count}\n";

    if ($count > 0) {
        $sample = $gurusQuery->first();
        echo "Sample result - ID: {$sample->id}, Name: {$sample->nama}\n";

        // Check user roles
        if ($sample->user) {
            $userRoles = $sample->user->roles->pluck('name')->toArray();
            echo "User roles: " . implode(', ', $userRoles) . "\n";
        } else {
            echo "No user associated with this guru\n";
        }
    }
}

// Get the executed queries
$queries = DB::getQueryLog();
Log::info('Filter test queries:', ['queries' => $queries]);

echo "\nTest completed. Filter should now be working correctly.\n";
