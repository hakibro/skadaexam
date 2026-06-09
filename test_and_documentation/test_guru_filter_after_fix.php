<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Testing Guru Filter Functionality After Fix\n";
echo "--------------------------------\n";

// Get counts for each role
$roles = ["guru", "data", "naskah", "pengawas", "koordinator", "ruangan"];
echo "Role Counts:\n";

foreach ($roles as $role) {
    // Count gurus with users having this role using the same logic as controller
    $count = Guru::whereHas("user.roles", function ($q) use ($role) {
        $q->where("name", $role);
    })->count();
    
    echo "- {$role}: {$count} gurus\n";
}

// Test the query directly to confirm it works
echo "\nDirect query test for role 'guru':\n";
$sql = DB::table("guru")
    ->join("users", "guru.user_id", "=", "users.id")
    ->join("model_has_roles", function($join) {
        $join->on("users.id", "=", "model_has_roles.model_id")
            ->where("model_has_roles.model_type", "=", "App\\Models\\User");
    })
    ->join("roles", "model_has_roles.role_id", "=", "roles.id")
    ->where("roles.name", "=", "guru")
    ->select("guru.id", "guru.nama", "roles.name as role_name")
    ->take(5)
    ->get();

if ($sql->isEmpty()) {
    echo "No results found with direct SQL query.\n";
} else {
    echo "Found " . $sql->count() . " results with direct SQL query:\n";
    foreach ($sql as $row) {
        echo "- ID: {$row->id}, Name: {$row->nama}, Role: {$row->role_name}\n";
    }
}

echo "\nFiltering functionality should now be working correctly.\n";
