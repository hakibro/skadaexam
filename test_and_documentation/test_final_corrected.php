<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;

// Test dengan data siswa ID 39 yang benar
$idyayasan = '220138';  // ID Yayasan siswa 39 yang benar
$token = 'ODQRLJ';

echo "=== Testing Siswa Login Corrected ===\n";
echo "ID Yayasan: $idyayasan\n";
echo "Token: $token\n\n";

// 1. Find siswa by idyayasan (bukan id_yayasan)
$siswa = Siswa::where('idyayasan', $idyayasan)->first();

if (!$siswa) {
    echo "❌ Siswa tidak ditemukan dengan idyayasan: $idyayasan\n";
    exit;
}

echo "✅ Siswa ditemukan:\n";
echo "   - ID: {$siswa->id}\n";
echo "   - Nama: {$siswa->nama}\n";
echo "   - ID Yayasan: {$siswa->idyayasan}\n";
echo "   - Status Pembayaran: {$siswa->status_pembayaran}\n\n";

// 2. Find enrollment with sesi ruangan token
$enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
    ->whereHas('sesiRuangan', function ($q) use ($token) {
        $q->where('token_ujian', $token);
    })
    ->with(['sesiRuangan.jadwalUjians', 'jadwalUjian'])
    ->first();

if (!$enrollment) {
    echo "❌ Enrollment dengan token $token tidak ditemukan\n";
    exit;
}

echo "✅ Enrollment ditemukan:\n";
echo "   - Enrollment ID: {$enrollment->id}\n";
echo "   - Sesi ID: {$enrollment->sesi_ruangan_id}\n";
echo "   - Status: {$enrollment->status_enrollment}\n";
echo "   - Token: {$enrollment->sesiRuangan->token_ujian}\n";
echo "   - Token Expired: " . ($enrollment->sesiRuangan->token_expired_at ?? 'NULL') . "\n\n";

// 3. Test logic sama seperti controller
$idyayasan = trim($idyayasan);
$token = strtoupper(trim($token));

echo "✅ Validation passed:\n";
echo "   - ID Yayasan found: {$siswa->nama}\n";
echo "   - Payment status: {$siswa->status_pembayaran}\n";
echo "   - Token found in sesi ruangan: {$enrollment->sesiRuangan->token_ujian}\n";

echo "\n🎉 LOGIN SUCCESS - All validation passed!\n";
echo "Siswa {$siswa->nama} dapat login dengan token sesi ruangan {$token}\n";
