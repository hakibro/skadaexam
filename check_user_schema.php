<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DATABASE SCHEMA DEBUG ===" . PHP_EOL;

// Check users table columns
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
    echo "Users table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "- {$column}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error getting users table columns: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// Check if there are any users at all
try {
    $userCount = \App\Models\User::count();
    echo "Total users in database: {$userCount}" . PHP_EOL;

    if ($userCount > 0) {
        $users = \App\Models\User::take(5)->get();
        echo "Sample users:" . PHP_EOL;
        foreach ($users as $user) {
            echo "- ID: {$user->id}, Username: " . ($user->username ?? $user->name ?? 'N/A') . PHP_EOL;

            // Try to show all attributes
            $attributes = $user->getAttributes();
            echo "  Attributes: " . implode(', ', array_keys($attributes)) . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "Error getting users: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "=== DEBUG COMPLETE ===" . PHP_EOL;
