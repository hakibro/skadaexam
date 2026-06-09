<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;

// Test data
$idYayasan = '12345678910';
$token = 'ODQRLJ';

echo "Testing login for ID Yayasan: $idYayasan with token: $token\n\n";

// Find siswa
$siswa = Siswa::where('id_yayasan', $idYayasan)->first();
if (!$siswa) {
    echo "❌ Siswa not found\n";
    exit;
}

echo "✅ Siswa found: {$siswa->nama} (ID: {$siswa->id})\n\n";

// Find enrollment with sesi ruangan token
$enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
    ->whereHas('sesiRuangan', function ($q) use ($token) {
        $q->where('token_ujian', $token);
    })
    ->with('sesiRuangan')
    ->first();

if ($enrollment) {
    echo "✅ Enrollment found with token {$token}!\n";
    echo "   - Enrollment ID: {$enrollment->id}\n";
    echo "   - Sesi Ruangan ID: {$enrollment->sesi_ruangan_id}\n";
    echo "   - Token Ujian: {$enrollment->sesiRuangan->token_ujian}\n";
    echo "   - Status: {$enrollment->status_enrollment}\n";
    echo "\n🎉 LOGIN SUCCESS!\n";
} else {
    echo "❌ No enrollment found with token {$token}\n\n";

    // Show available enrollments
    $allEnrollments = EnrollmentUjian::where('siswa_id', $siswa->id)->with('sesiRuangan')->get();
    echo "Available enrollments for this student:\n";
    foreach ($allEnrollments as $enr) {
        $tokenSesi = $enr->sesiRuangan ? $enr->sesiRuangan->token_ujian : 'NULL';
        echo "   - Enrollment ID: {$enr->id}, Sesi ID: {$enr->sesi_ruangan_id}, Token: $tokenSesi\n";
    }
}
