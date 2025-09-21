<?php

require_once 'vendor/autoload.php';

use App\Models\PelanggaranUjian;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== GENERATE JAVASCRIPT TEST DATA ===" . PHP_EOL;

// Get violations with proper relations loaded
$violations = PelanggaranUjian::with([
    'siswa',
    'hasilUjian',
    'jadwalUjian.mapel',
    'sesiRuangan.ruangan'
])->where('sesi_ruangan_id', 2)
    ->orderBy('waktu_pelanggaran', 'desc')
    ->get();

echo "Found {$violations->count()} violations" . PHP_EOL;
echo PHP_EOL;

// Generate JavaScript that can be tested in browser console
echo "=== JAVASCRIPT TEST CODE ===" . PHP_EOL;
echo "// Copy and paste this code in browser console:" . PHP_EOL;
echo PHP_EOL;

echo "const testViolations = " . json_encode($violations, JSON_PRETTY_PRINT) . ";" . PHP_EOL;
echo PHP_EOL;

echo "// Test function to check if data access works:" . PHP_EOL;
echo "function testViolationData() {" . PHP_EOL;
echo "  console.log('Testing', testViolations.length, 'violations');" . PHP_EOL;
echo "  " . PHP_EOL;
echo "  testViolations.forEach((violation, index) => {" . PHP_EOL;
echo "    console.log('Violation', index + 1, ':');" . PHP_EOL;
echo "    console.log('  ID:', violation.id);" . PHP_EOL;
echo "    console.log('  Siswa:', violation.siswa ? violation.siswa.nama : 'No siswa');" . PHP_EOL;
echo "    console.log('  Mapel:', (violation.jadwal_ujian && violation.jadwal_ujian.mapel) ? violation.jadwal_ujian.mapel.nama_mapel : 'No mapel');" . PHP_EOL;
echo "    console.log('  Ruangan:', (violation.sesi_ruangan && violation.sesi_ruangan.ruangan) ? violation.sesi_ruangan.ruangan.nama_ruangan : 'No ruangan');" . PHP_EOL;
echo "    console.log('  Type:', violation.jenis_pelanggaran);" . PHP_EOL;
echo "    console.log('  Time:', violation.waktu_pelanggaran);" . PHP_EOL;
echo "    console.log('---');" . PHP_EOL;
echo "  });" . PHP_EOL;
echo "}" . PHP_EOL;
echo PHP_EOL;

echo "// Call the test function:" . PHP_EOL;
echo "testViolationData();" . PHP_EOL;
echo PHP_EOL;

echo "=== DEBUG COMPLETE ===" . PHP_EOL;
