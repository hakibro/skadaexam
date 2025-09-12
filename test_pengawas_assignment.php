<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SesiRuangan;

echo "=== Testing pengawas assignment access ===\n\n";

// Test dengan sesi ruangan ID 115 seperti di URL yang error
$sesiRuangan = SesiRuangan::with([
    'ruangan',
    'sesiRuanganSiswa',
    'sesiRuanganSiswa.siswa',
    'sesiRuanganSiswa.siswa.kelas'
])->find(115);

if (!$sesiRuangan) {
    echo "❌ SesiRuangan ID 115 tidak ditemukan\n";
    exit;
}

echo "✅ SesiRuangan berhasil dimuat:\n";
echo "   - ID: {$sesiRuangan->id}\n";
echo "   - Nama: {$sesiRuangan->nama_sesi}\n";
echo "   - Ruangan: " . ($sesiRuangan->ruangan->nama_ruangan ?? 'NULL') . "\n";

echo "\n✅ SesiRuanganSiswa data:\n";
echo "   - Total records: " . $sesiRuangan->sesiRuanganSiswa->count() . "\n";

if ($sesiRuangan->sesiRuanganSiswa->count() > 0) {
    echo "\nFirst few siswa:\n";
    foreach ($sesiRuangan->sesiRuanganSiswa->take(3) as $siswaRecord) {
        echo "   - Siswa: " . ($siswaRecord->siswa->nama ?? 'NULL') . "\n";
        echo "     Status: {$siswaRecord->status_kehadiran}\n";
        echo "     Kelas: " . ($siswaRecord->siswa->kelas->nama ?? 'NULL') . "\n";
    }
}

echo "\n🎉 Controller method should work now!\n";
echo "URL http://skadaexam.test/features/pengawas/assignment/115 should be accessible.\n";
