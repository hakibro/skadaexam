<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\EnrollmentUjian;

echo "=== DEBUG TOKEN VALIDATION ISSUE ===\n";

$siswaId = 39;
$token = "ODQRLJ";

echo "Debugging Siswa ID: $siswaId, Token: $token\n\n";

// Find the student
$siswa = Siswa::find($siswaId);
if (!$siswa) {
    echo "❌ Siswa ID $siswaId not found\n";
    exit;
}

echo "✅ Siswa found:\n";
echo "- ID: " . $siswa->id . "\n";
echo "- Nama: " . $siswa->nama . "\n";
echo "- ID Yayasan: " . ($siswa->idyayasan ?? 'N/A') . "\n";
echo "- Status Pembayaran: " . $siswa->status_pembayaran . "\n\n";

// Check all enrollments for this student
echo "=== ALL ENROLLMENTS FOR THIS STUDENT ===\n";
$allEnrollments = EnrollmentUjian::where('siswa_id', $siswaId)->get();

if ($allEnrollments->count() == 0) {
    echo "❌ No enrollments found for this student\n";
} else {
    echo "Found " . $allEnrollments->count() . " enrollments:\n";
    foreach ($allEnrollments as $enrollment) {
        echo "- ID: " . $enrollment->id . "\n";
        echo "  Token: " . ($enrollment->token_login ?? 'NULL') . "\n";
        echo "  Status: " . $enrollment->status_enrollment . "\n";
        echo "  Created: " . ($enrollment->token_dibuat_pada ? $enrollment->token_dibuat_pada->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "  Used: " . ($enrollment->token_digunakan_pada ? $enrollment->token_digunakan_pada->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "  Sesi Ruangan ID: " . $enrollment->sesi_ruangan_id . "\n";

        if ($enrollment->sesiRuangan) {
            echo "  Sesi: " . $enrollment->sesiRuangan->nama_sesi . "\n";
            echo "  Sesi Status: " . $enrollment->sesiRuangan->status . "\n";
        } else {
            echo "  Sesi: NULL\n";
        }
        echo "\n";
    }
}

// Check enrollments with the specific token
echo "=== ENROLLMENTS WITH TOKEN '$token' ===\n";
$tokenEnrollments = EnrollmentUjian::where('token_login', $token)->get();

if ($tokenEnrollments->count() == 0) {
    echo "❌ No enrollments found with token '$token'\n";
} else {
    echo "Found " . $tokenEnrollments->count() . " enrollments with this token:\n";
    foreach ($tokenEnrollments as $enrollment) {
        echo "- ID: " . $enrollment->id . "\n";
        echo "  Siswa ID: " . $enrollment->siswa_id . "\n";
        echo "  Siswa Nama: " . ($enrollment->siswa ? $enrollment->siswa->nama : 'N/A') . "\n";
        echo "  Status: " . $enrollment->status_enrollment . "\n";
        echo "  Created: " . ($enrollment->token_dibuat_pada ? $enrollment->token_dibuat_pada->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "  Used: " . ($enrollment->token_digunakan_pada ? $enrollment->token_digunakan_pada->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "\n";
    }
}

// Check exact match
echo "=== EXACT MATCH CHECK ===\n";
$exactMatch = EnrollmentUjian::where('siswa_id', $siswaId)
    ->where('token_login', $token)
    ->first();

if ($exactMatch) {
    echo "✅ Found exact match!\n";
    echo "- Enrollment ID: " . $exactMatch->id . "\n";
    echo "- Status: " . $exactMatch->status_enrollment . "\n";
    echo "- Token validation: " . ($exactMatch->validateToken($token) ? 'VALID' : 'INVALID') . "\n";

    if ($exactMatch->token_digunakan_pada) {
        $hoursSinceUsed = now()->diffInHours($exactMatch->token_digunakan_pada);
        echo "- Hours since token used: " . $hoursSinceUsed . "\n";
        echo "- Is expired (>2h): " . ($hoursSinceUsed > 2 ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "❌ No exact match found\n";
}

echo "\n=== DEBUG COMPLETED ===\n";
