<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SPATIE ROLES DEBUG ===" . PHP_EOL;

// Check available roles
try {
    $roles = \Spatie\Permission\Models\Role::all();
    echo "Available roles: " . $roles->count() . PHP_EOL;
    foreach ($roles as $role) {
        echo "- {$role->name} (Guard: {$role->guard_name})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error getting roles: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// Check users with roles
try {
    $usersWithRoles = \App\Models\User::with('roles')->whereHas('roles')->get();
    echo "Users with roles: " . $usersWithRoles->count() . PHP_EOL;

    foreach ($usersWithRoles as $user) {
        $roleNames = $user->roles->pluck('name')->toArray();
        echo "- {$user->name} (ID: {$user->id}): " . implode(', ', $roleNames) . PHP_EOL;

        // Test specific role methods
        echo "  Can supervise: " . ($user->canSupervise() ? 'Yes' : 'No') . PHP_EOL;
        echo "  Is admin: " . ($user->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error getting users with roles: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// Check specifically for pengawas role
try {
    $pengawasUsers = \App\Models\User::role('pengawas')->get();
    echo "Users with pengawas role: " . $pengawasUsers->count() . PHP_EOL;
    foreach ($pengawasUsers as $user) {
        echo "- {$user->name} (ID: {$user->id})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error getting pengawas users: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// Check admin users
try {
    $adminUsers = \App\Models\User::role('admin')->get();
    echo "Users with admin role: " . $adminUsers->count() . PHP_EOL;
    foreach ($adminUsers as $user) {
        echo "- {$user->name} (ID: {$user->id})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error getting admin users: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "=== DEBUG COMPLETE ===" . PHP_EOL;
