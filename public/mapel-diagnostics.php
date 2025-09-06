<?php
// This script shows information about soft deleted mapel records

// Load Laravel environment
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Mapel;

echo '<h1>Mapel Database Diagnostics</h1>';

// Check if deleted_at column exists
echo '<h2>Table Structure</h2>';
echo '<pre>';
$columns = Schema::getColumnListing('mapel');
echo "Columns in mapel table:\n";
print_r($columns);
echo '</pre>';

// Count all records including deleted ones
echo '<h2>Record Counts</h2>';
$totalCount = DB::table('mapel')->count();
$activeCount = DB::table('mapel')->whereNull('deleted_at')->count();
$deletedCount = DB::table('mapel')->whereNotNull('deleted_at')->count();

echo "<p>Total records: $totalCount</p>";
echo "<p>Active records: $activeCount</p>";
echo "<p>Soft-deleted records: $deletedCount</p>";

// Show sample of deleted records
if ($deletedCount > 0) {
    echo '<h2>Soft-Deleted Records</h2>';
    echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <th>ID</th>
        <th>Kode Mapel</th>
        <th>Nama Mapel</th>
        <th>Deleted At</th>
    </tr>';

    $deletedRecords = DB::table('mapel')
        ->whereNotNull('deleted_at')
        ->select('id', 'kode_mapel', 'nama_mapel', 'deleted_at')
        ->get();

    foreach ($deletedRecords as $record) {
        echo "<tr>
            <td>{$record->id}</td>
            <td>{$record->kode_mapel}</td>
            <td>{$record->nama_mapel}</td>
            <td>{$record->deleted_at}</td>
        </tr>";
    }

    echo '</table>';
}

// How to permanently delete records
echo '<h2>How to Permanently Delete Records</h2>';
echo '<p>To permanently delete these records, you can use one of the following methods:</p>';
echo '<h3>Method 1: Using Artisan Tinker</h3>';
echo '<pre>
// Force delete a specific record
php artisan tinker --execute="\\App\\Models\\Mapel::withTrashed()->find(ID)->forceDelete()"

// Force delete all trashed records
php artisan tinker --execute="\\App\\Models\\Mapel::onlyTrashed()->forceDelete()"
</pre>';

echo '<h3>Method 2: Direct SQL (use with caution)</h3>';
echo '<pre>
DELETE FROM mapel WHERE deleted_at IS NOT NULL;
</pre>';

echo '<h3>Method 3: Custom PHP Script</h3>';
echo '<p>Create a custom artisan command or add an admin function to permanently remove these records.</p>';
