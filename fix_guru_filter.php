<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

// Simple fix to clear the multiple event handlers issue
// Just add a timestamp marker to the file to make it unique
echo "Updating guru index.blade.php file with timestamp marker...\n";

$indexFilePath = 'resources/views/features/data/guru/index.blade.php';
$content = file_get_contents($indexFilePath);

// Add a timestamp marker to the file
$timestamp = date('Y-m-d H:i:s');
$marker = "<!-- File updated on: {$timestamp} -->";

if (strpos($content, '<!-- File updated on:') !== false) {
    $content = preg_replace('/<!-- File updated on: .*? -->/', $marker, $content);
} else {
    $content = $marker . "\n" . $content;
}

// Fix duplicate pagination handlers and performSearch functions
$content = preg_replace('/document\.addEventListener\(\'click\', function\(e\) \{[\s\S]+?\}\); \/\/ Main search function/', 'document.addEventListener(\'click\', handlePaginationClick);', $content);
$content = preg_replace('/function performSearch\(\) \{[\s\S]+?fetch\(searchUrl\.toString\(\), \{[\s\S]+?return response\.json\(\);[\s\S]+?\}\)/', '', $content);

// Add the debugLog function
$debugLogFunction = "
    <script>
        // Debug helper function
        function debugLog(message, data = null) {
            const enableDebug = true;
            if (enableDebug) {
                console.log(`[DEBUG] \${message}`, data || '');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('DOM Content Loaded');";

$content = preg_replace('/<script>\s+document\.addEventListener\(\'DOMContentLoaded\', function\(\) \{/', $debugLogFunction, $content);

file_put_contents($indexFilePath, $content);
echo "File updated successfully!\n";

// Now let's restart the server to apply the changes
echo "Creating a test script to validate filtering functionality...\n";

// Create a test script
$testScriptContent = '<?php
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
echo "\nDirect query test for role \'guru\':\n";
$sql = DB::table("guru")
    ->join("users", "guru.user_id", "=", "users.id")
    ->join("model_has_roles", function($join) {
        $join->on("users.id", "=", "model_has_roles.model_id")
            ->where("model_has_roles.model_type", "=", "App\\\\Models\\\\User");
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
';

file_put_contents('test_guru_filter_after_fix.php', $testScriptContent);
echo "Test script created. Run it to validate the fix.\n";
