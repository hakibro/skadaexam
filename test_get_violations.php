<?php

require_once 'vendor/autoload.php';

use App\Models\PelanggaranUjian;
use App\Models\JadwalUjianSesiRuangan;
use Carbon\Carbon;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PENGAWAS GET-VIOLATIONS DEBUG ===" . PHP_EOL;
echo PHP_EOL;

// Test the logic from PelanggaranController->getViolations()
echo "Testing get-violations logic for sesi_ruangan_id = 2" . PHP_EOL;

// Base query to get violations
$query = PelanggaranUjian::with([
    'siswa',
    'hasilUjian',
    'jadwalUjian.mapel',
    'sesiRuangan.ruangan'
])->orderBy('waktu_pelanggaran', 'desc');

// Filter by specific session (sesi_ruangan_id = 2)
$query->where('sesi_ruangan_id', 2);

$violations = $query->get();

echo "Violations found for session 2: " . $violations->count() . PHP_EOL;
echo PHP_EOL;

foreach ($violations as $violation) {
    echo "ID: {$violation->id}" . PHP_EOL;
    echo "  Siswa: " . ($violation->siswa ? $violation->siswa->nama : 'N/A') . PHP_EOL;
    echo "  Type: {$violation->jenis_pelanggaran}" . PHP_EOL;
    echo "  Time: {$violation->waktu_pelanggaran}" . PHP_EOL;
    echo "  Session ID: {$violation->sesi_ruangan_id}" . PHP_EOL;
    echo "  Jadwal ID: {$violation->jadwal_ujian_id}" . PHP_EOL;
    echo "  Hasil Ujian ID: {$violation->hasil_ujian_id}" . PHP_EOL;
    echo "  Dismissed: " . ($violation->is_dismissed ? 'Yes' : 'No') . PHP_EOL;
    echo "  Finalized: " . ($violation->is_finalized ? 'Yes' : 'No') . PHP_EOL;
    echo "  Relations loaded: " . PHP_EOL;
    echo "    - siswa: " . ($violation->relationLoaded('siswa') ? 'Yes' : 'No') . PHP_EOL;
    echo "    - hasilUjian: " . ($violation->relationLoaded('hasilUjian') ? 'Yes' : 'No') . PHP_EOL;
    echo "    - jadwalUjian: " . ($violation->relationLoaded('jadwalUjian') ? 'Yes' : 'No') . PHP_EOL;
    echo "    - sesiRuangan: " . ($violation->relationLoaded('sesiRuangan') ? 'Yes' : 'No') . PHP_EOL;

    if ($violation->jadwalUjian && $violation->jadwalUjian->mapel) {
        echo "    - mapel: " . $violation->jadwalUjian->mapel->nama . PHP_EOL;
    } else {
        echo "    - mapel: N/A" . PHP_EOL;
    }

    if ($violation->sesiRuangan && $violation->sesiRuangan->ruangan) {
        echo "    - ruangan: " . $violation->sesiRuangan->ruangan->nama . PHP_EOL;
    } else {
        echo "    - ruangan: N/A" . PHP_EOL;
    }

    echo PHP_EOL;
}

echo "=== Testing violations for all sessions (admin view) ===" . PHP_EOL;
$today = now()->format('Y-m-d');
$allViolationsQuery = PelanggaranUjian::with([
    'siswa',
    'hasilUjian',
    'jadwalUjian.mapel',
    'sesiRuangan.ruangan'
])->whereDate('waktu_pelanggaran', $today)
    ->orderBy('waktu_pelanggaran', 'desc');

$allViolations = $allViolationsQuery->get();
echo "All today's violations: " . $allViolations->count() . PHP_EOL;

echo PHP_EOL;
echo "=== DEBUG COMPLETE ===" . PHP_EOL;
