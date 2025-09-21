<?php

require_once 'vendor/autoload.php';

use App\Models\PelanggaranUjian;
use Carbon\Carbon;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREATE TEST VIOLATION ===" . PHP_EOL;

// Create a new test violation
$violation = new PelanggaranUjian([
    'siswa_id' => 122,
    'hasil_ujian_id' => 12,
    'jadwal_ujian_id' => 4,
    'sesi_ruangan_id' => 2,
    'jenis_pelanggaran' => 'test_violation',
    'deskripsi' => 'Test pelanggaran untuk debugging dashboard',
    'waktu_pelanggaran' => Carbon::now(),
    'is_dismissed' => false,
    'is_finalized' => false
]);

$violation->save();

echo "Created test violation with ID: {$violation->id}" . PHP_EOL;
echo "Time: {$violation->waktu_pelanggaran}" . PHP_EOL;

// Check total violations now
$totalViolations = PelanggaranUjian::count();
$todayViolations = PelanggaranUjian::whereDate('waktu_pelanggaran', Carbon::today())->count();

echo "Total violations in database: {$totalViolations}" . PHP_EOL;
echo "Today's violations: {$todayViolations}" . PHP_EOL;

echo PHP_EOL;
echo "Now test the pengawas dashboard to see if this new violation appears." . PHP_EOL;
echo "=== TEST COMPLETE ===" . PHP_EOL;
