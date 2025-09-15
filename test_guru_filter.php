<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Testing Guru Filter Functionality\n";
echo "--------------------------------\n";

// Get counts for each role
$roles = ['guru', 'data', 'naskah', 'pengawas', 'koordinator', 'ruangan'];
echo "Role Counts:\n";

foreach ($roles as $role) {
    // Count gurus with users having this role
    $count = Guru::whereHas('user.roles', function ($q) use ($role) {
        $q->where('name', $role);
    })->count();

    echo "- {$role}: {$count} gurus\n";
}

echo "\n";

// Test a specific role filter
$testRole = 'guru';
echo "Testing filter for role '{$testRole}':\n";

$gurus = Guru::whereHas('user.roles', function ($q) use ($testRole) {
    $q->where('name', $testRole);
})->take(5)->get();

if ($gurus->isEmpty()) {
    echo "No gurus found with role '{$testRole}'.\n";
} else {
    echo "Found " . $gurus->count() . " gurus with role '{$testRole}':\n";
    foreach ($gurus as $guru) {
        $roleName = $guru->user ? implode(', ', $guru->user->roles->pluck('name')->toArray()) : 'no role';
        echo "- ID: {$guru->id}, Name: {$guru->nama}, Role: {$roleName}\n";
    }
}

// Check for gurus without users
$noUserCount = Guru::whereNull('user_id')->count();
echo "\nGurus without associated users: {$noUserCount}\n";

// Check for database query issues
echo "\nChecking the structure of the roles table:\n";
$roleColumns = DB::select('SHOW COLUMNS FROM roles');
foreach ($roleColumns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\nChecking the structure of the model_has_roles table:\n";
$mhrColumns = DB::select('SHOW COLUMNS FROM model_has_roles');
foreach ($mhrColumns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}
