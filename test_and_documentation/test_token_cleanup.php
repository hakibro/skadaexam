<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;

echo "=== Testing Token Cleanup Results ===\n\n";

// Test dengan data siswa ID 39
$siswa = Siswa::find(39);

if (!$siswa) {
    echo "❌ Siswa ID 39 tidak ditemukan\n";
    exit;
}

echo "✅ Siswa: {$siswa->nama}\n";
echo "   ID Yayasan: {$siswa->idyayasan}\n\n";

// Cek enrollment siswa
$enrollments = EnrollmentUjian::where('siswa_id', 39)
    ->with('sesiRuangan')
    ->get();

echo "Enrollment untuk siswa {$siswa->nama}:\n";
foreach ($enrollments as $enr) {
    echo "   - Enrollment ID: {$enr->id}\n";
    echo "     Sesi ID: {$enr->sesi_ruangan_id}\n";
    echo "     Status: {$enr->status_enrollment}\n";

    if ($enr->sesiRuangan) {
        echo "     Token Sesi: {$enr->sesiRuangan->token_ujian}\n";
        echo "     Token Expired: " . ($enr->sesiRuangan->token_expired_at ?? 'NULL') . "\n";
    } else {
        echo "     ❌ Sesi Ruangan not found!\n";
    }
    echo "\n";
}

// Test login dengan token ODQRLJ
$token = 'ODQRLJ';
echo "=== Testing Login dengan Token: {$token} ===\n";

// Find enrollment with matching sesi ruangan token
$validEnrollment = EnrollmentUjian::where('siswa_id', 39)
    ->whereHas('sesiRuangan', function ($q) use ($token) {
        $q->where('token_ujian', $token);
    })
    ->with('sesiRuangan')
    ->first();

if ($validEnrollment) {
    echo "✅ LOGIN BERHASIL!\n";
    echo "   Token: {$token}\n";
    echo "   Enrollment ID: {$validEnrollment->id}\n";
    echo "   Sesi Ruangan: {$validEnrollment->sesiRuangan->nama_sesi}\n";
    echo "   Token Sesi: {$validEnrollment->sesiRuangan->token_ujian}\n";
    echo "\n🎉 Sistem login menggunakan token sesi ruangan sudah berfungsi!\n";
} else {
    echo "❌ Token tidak ditemukan atau tidak cocok\n";
}

echo "\n=== Database Cleanup Status ===\n";

// Check if token columns are removed from enrollment_ujian
try {
    $sampleEnrollment = EnrollmentUjian::first();
    $attributes = $sampleEnrollment->getAttributes();

    if (isset($attributes['token_login'])) {
        echo "❌ Kolom token_login masih ada di database\n";
    } else {
        echo "✅ Kolom token_login berhasil dihapus dari database\n";
    }

    if (isset($attributes['token_dibuat_pada'])) {
        echo "❌ Kolom token_dibuat_pada masih ada di database\n";
    } else {
        echo "✅ Kolom token_dibuat_pada berhasil dihapus dari database\n";
    }

    if (isset($attributes['token_digunakan_pada'])) {
        echo "❌ Kolom token_digunakan_pada masih ada di database\n";
    } else {
        echo "✅ Kolom token_digunakan_pada berhasil dihapus dari database\n";
    }
} catch (\Exception $e) {
    echo "❌ Error checking database: {$e->getMessage()}\n";
}

echo "\n✅ Token cleanup selesai! Sistem login siswa sekarang menggunakan token sesi ruangan.\n";
