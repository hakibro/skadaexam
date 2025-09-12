<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;

// Cek siswa ID 39
$siswa = Siswa::find(39);

if (!$siswa) {
    echo "❌ Siswa ID 39 tidak ditemukan\n";
    exit;
}

echo "✅ Siswa ID 39 ditemukan:\n";
foreach ($siswa->getAttributes() as $key => $value) {
    echo "   - $key: $value\n";
}

echo "\n";

// Cek enrollment siswa ID 39
$enrollments = EnrollmentUjian::where('siswa_id', 39)->with('sesiRuangan')->get();

echo "Enrollment untuk siswa ID 39:\n";
foreach ($enrollments as $enr) {
    echo "   - Enrollment ID: {$enr->id}\n";
    echo "     Sesi ID: {$enr->sesi_ruangan_id}\n";
    echo "     Status: {$enr->status_enrollment}\n";
    if ($enr->sesiRuangan) {
        echo "     Token Sesi: {$enr->sesiRuangan->token_ujian}\n";
        echo "     Token Expired: " . ($enr->sesiRuangan->token_expired_at ?? 'NULL') . "\n";
    }
    echo "\n";
}

// Test login dengan token ODQRLJ
$token = 'ODQRLJ';
echo "Testing login dengan token: $token\n";

$validEnrollment = EnrollmentUjian::where('siswa_id', 39)
    ->whereHas('sesiRuangan', function ($q) use ($token) {
        $q->where('token_ujian', $token);
    })
    ->with('sesiRuangan')
    ->first();

if ($validEnrollment) {
    echo "✅ LOGIN BERHASIL!\n";
    echo "Token {$token} cocok dengan enrollment ID {$validEnrollment->id}\n";
} else {
    echo "❌ Token {$token} tidak cocok dengan enrollment siswa ID 39\n";
}
