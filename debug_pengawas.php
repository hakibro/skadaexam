<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JadwalUjianSesiRuangan;
use App\Models\SesiRuangan;
use Carbon\Carbon;

echo "=== Debugging Pengawas Dashboard Filtering ===" . PHP_EOL;

$today = Carbon::today();
echo "Today: " . $today->format('Y-m-d') . PHP_EOL . PHP_EOL;

// Check all pivot table data
echo "=== Pivot Table Data ===" . PHP_EOL;
$pivots = JadwalUjianSesiRuangan::with(['jadwalUjian', 'sesiRuangan', 'pengawas'])
    ->get();

foreach ($pivots as $pivot) {
    $tanggal = $pivot->jadwalUjian ? $pivot->jadwalUjian->tanggal->format('Y-m-d') : 'No date';
    $sesi = $pivot->sesiRuangan ? $pivot->sesiRuangan->nama_sesi : 'No sesi';
    $pengawas = $pivot->pengawas ? $pivot->pengawas->nama_guru : 'No pengawas';

    echo "Jadwal ID: {$pivot->jadwal_ujian_id}, Sesi ID: {$pivot->sesi_ruangan_id}, " .
        "Pengawas ID: {$pivot->pengawas_id} ({$pengawas}), Tanggal: {$tanggal}, Sesi: {$sesi}" . PHP_EOL;
}

echo PHP_EOL . "=== Testing Query for All Guru IDs in Pivot ===" . PHP_EOL;

// Get all unique guru IDs from pivot table
$guruIds = JadwalUjianSesiRuangan::whereNotNull('pengawas_id')
    ->distinct()
    ->pluck('pengawas_id')
    ->toArray();

echo "Guru IDs found in pivot table: " . implode(', ', $guruIds) . PHP_EOL . PHP_EOL;

foreach ($guruIds as $guru_id) {
    echo "--- Testing for Guru ID: {$guru_id} ---" . PHP_EOL;

    // Test upcoming assignments
    $upcomingAssignments = SesiRuangan::query()
        ->with(['jadwalUjians' => function ($q) use ($today, $guru_id) {
            $q->whereDate('tanggal', '>', $today)
                ->with('mapel')
                ->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru_id);
        }, 'ruangan', 'sesiRuanganSiswa'])
        ->whereHas('jadwalUjians', function ($q) use ($today, $guru_id) {
            $q->whereDate('tanggal', '>', $today)
                ->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru_id);
        })
        ->get();

    echo "Upcoming assignments for guru ID {$guru_id}: " . $upcomingAssignments->count() . " sesi ruangan" . PHP_EOL;

    foreach ($upcomingAssignments as $assignment) {
        echo "- Sesi: {$assignment->nama_sesi}, Jadwal Count: " . $assignment->jadwalUjians->count() . PHP_EOL;

        foreach ($assignment->jadwalUjians as $jadwal) {
            $mapel = $jadwal->mapel ? $jadwal->mapel->nama_mapel : 'No mapel';
            echo "  * Jadwal: {$jadwal->judul}, Mapel: {$mapel}, Tanggal: " . $jadwal->tanggal->format('Y-m-d') . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo PHP_EOL . "=== Raw SQL Debug ===" . PHP_EOL;

// Enable query logging
\Illuminate\Support\Facades\DB::enableQueryLog();

// Run the same query again
$test = SesiRuangan::query()
    ->whereHas('jadwalUjians', function ($q) use ($today, $guru_id) {
        $q->whereDate('tanggal', $today)
            ->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru_id);
    })
    ->get();

// Print the executed queries
$queries = \Illuminate\Support\Facades\DB::getQueryLog();
foreach ($queries as $query) {
    echo "SQL: " . $query['query'] . PHP_EOL;
    echo "Bindings: " . json_encode($query['bindings']) . PHP_EOL . PHP_EOL;
}
