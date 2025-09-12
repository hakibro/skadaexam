<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking sesi_ruangan_siswa table structure ===" . PHP_EOL;

try {
    $columns = DB::select('SHOW COLUMNS FROM sesi_ruangan_siswa');

    echo "Columns in sesi_ruangan_siswa table:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }

    // Check if there are any records with logout status
    echo PHP_EOL . "Checking for logout records..." . PHP_EOL;
    $logoutCount = DB::table('sesi_ruangan_siswa')
        ->where('status_kehadiran', 'logout')
        ->count();

    echo "Records with status_kehadiran = 'logout': $logoutCount" . PHP_EOL;

    // Check for records with waktu_keluar (if column exists)
    try {
        $logoutByTimeCount = DB::table('sesi_ruangan_siswa')
            ->whereNotNull('waktu_keluar')
            ->count();
        echo "Records with waktu_keluar set: $logoutByTimeCount" . PHP_EOL;
    } catch (\Exception $e) {
        echo "waktu_keluar column doesn't exist" . PHP_EOL;
    }

    // Check unique values in status_kehadiran
    $statusValues = DB::table('sesi_ruangan_siswa')
        ->select('status_kehadiran')
        ->distinct()
        ->get();

    echo "Unique status_kehadiran values:" . PHP_EOL;
    foreach ($statusValues as $status) {
        $count = DB::table('sesi_ruangan_siswa')
            ->where('status_kehadiran', $status->status_kehadiran)
            ->count();
        echo "  - '{$status->status_kehadiran}': $count records" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
