<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SesiRuangan;
use App\Models\Siswa;

echo "=== Testing SesiRuangan relationship with Siswa ===\n\n";

// Test dengan sesi ruangan ID 115
$sesiRuangan = SesiRuangan::find(115);

if (!$sesiRuangan) {
    echo "❌ SesiRuangan ID 115 tidak ditemukan\n";
    exit;
}

echo "✅ SesiRuangan found:\n";
echo "   - ID: {$sesiRuangan->id}\n";
echo "   - Nama: {$sesiRuangan->nama_sesi}\n";
echo "   - Token: {$sesiRuangan->token_ujian}\n\n";

try {
    echo "Testing siswa relationship...\n";
    $siswaList = $sesiRuangan->siswa;

    echo "✅ Relationship working!\n";
    echo "   - Total siswa: " . $siswaList->count() . "\n";

    if ($siswaList->count() > 0) {
        echo "\nFirst few siswa:\n";
        foreach ($siswaList->take(3) as $siswa) {
            echo "   - {$siswa->nama} (ID: {$siswa->id})\n";
            echo "     Pivot Status: " . ($siswa->pivot->status_kehadiran ?? 'NULL') . "\n";
            echo "     Pivot Keterangan: " . ($siswa->pivot->keterangan ?? 'NULL') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error in relationship: {$e->getMessage()}\n";
    echo "Error trace: {$e->getTraceAsString()}\n";
}

echo "\n=== Testing direct query ===\n";
try {
    $directQuery = Siswa::join('sesi_ruangan_siswa', 'siswa.id', '=', 'sesi_ruangan_siswa.siswa_id')
        ->where('sesi_ruangan_siswa.sesi_ruangan_id', 115)
        ->select('siswa.*', 'sesi_ruangan_siswa.status_kehadiran', 'sesi_ruangan_siswa.keterangan')
        ->get();

    echo "✅ Direct query working!\n";
    echo "   - Count: " . $directQuery->count() . "\n";
} catch (\Exception $e) {
    echo "❌ Error in direct query: {$e->getMessage()}\n";
}

echo "\n✅ Test complete!\n";
