<?php

require_once 'vendor/autoload.php';

use App\Models\PelanggaranUjian;
use Carbon\Carbon;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VIOLATION SYSTEM DEBUG ===" . PHP_EOL;
echo "Current time: " . Carbon::now() . PHP_EOL;
echo "Today's date: " . Carbon::today()->format('Y-m-d') . PHP_EOL;
echo PHP_EOL;

// Check total violations
$totalViolations = PelanggaranUjian::count();
echo "Total violations in database: " . $totalViolations . PHP_EOL;

// Check today's violations
$todayViolations = PelanggaranUjian::whereDate('waktu_pelanggaran', Carbon::today())->count();
echo "Today's violations: " . $todayViolations . PHP_EOL;

// Check recent violations (last 7 days)
$recentViolations = PelanggaranUjian::where('waktu_pelanggaran', '>=', Carbon::now()->subDays(7))->count();
echo "Recent violations (last 7 days): " . $recentViolations . PHP_EOL;
echo PHP_EOL;

// Show latest 5 violations with details
echo "Latest 5 violations:" . PHP_EOL;
$latestViolations = PelanggaranUjian::with(['siswa', 'jadwalUjian.mapel'])
    ->latest('waktu_pelanggaran')
    ->take(5)
    ->get();

foreach ($latestViolations as $violation) {
    $siswaName = $violation->siswa ? $violation->siswa->nama : 'N/A';
    $mapelName = $violation->jadwalUjian && $violation->jadwalUjian->mapel ? $violation->jadwalUjian->mapel->nama : 'N/A';

    echo "ID: {$violation->id} | Siswa: {$siswaName} | Mapel: {$mapelName}" . PHP_EOL;
    echo "  Type: {$violation->jenis_pelanggaran} | Time: {$violation->waktu_pelanggaran}" . PHP_EOL;
    echo "  Sesi: {$violation->sesi_ruangan_id} | Dismissed: " . ($violation->is_dismissed ? 'Yes' : 'No') . PHP_EOL;
    echo "  Finalized: " . ($violation->is_finalized ? 'Yes' : 'No') . " | Action: {$violation->tindakan}" . PHP_EOL;
    echo "  Notes: " . ($violation->catatan_pengawas ?: 'None') . PHP_EOL;
    echo PHP_EOL;
}

// Check session assignments for pengawas
echo "=== SESSION ASSIGNMENTS ===" . PHP_EOL;
$assignments = \App\Models\JadwalUjianSesiRuangan::with(['jadwalUjian', 'sesiRuangan', 'pengawas'])
    ->whereHas('jadwalUjian', function ($q) {
        $q->whereDate('tanggal', Carbon::today());
    })
    ->get();

echo "Today's session assignments: " . $assignments->count() . PHP_EOL;
foreach ($assignments as $assignment) {
    $pengawasName = $assignment->pengawas ? $assignment->pengawas->nama : 'N/A';
    $mapelName = $assignment->jadwalUjian && $assignment->jadwalUjian->mapel ? $assignment->jadwalUjian->mapel->nama : 'N/A';

    echo "Session ID: {$assignment->sesi_ruangan_id} | Pengawas: {$pengawasName} | Mapel: {$mapelName}" . PHP_EOL;
}

echo PHP_EOL;
echo "=== DEBUG COMPLETE ===" . PHP_EOL;
