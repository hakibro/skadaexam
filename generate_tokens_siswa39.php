<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EnrollmentUjian;
use Illuminate\Support\Str;

echo "=== GENERATING TOKENS FOR SISWA ID 39 ===\n";

// Function to generate unique token
function generateUniqueToken()
{
    do {
        $token = strtoupper(Str::random(6));
    } while (EnrollmentUjian::where('token_login', $token)->exists());

    return $token;
}

$siswaId = 39;

// Get enrollments for siswa ID 39
$enrollments = EnrollmentUjian::where('siswa_id', $siswaId)->get();

echo "Found " . $enrollments->count() . " enrollments for siswa ID $siswaId:\n\n";

foreach ($enrollments as $enrollment) {
    echo "Enrollment ID: " . $enrollment->id . "\n";
    echo "Current token: " . ($enrollment->token_login ?? 'NULL') . "\n";
    echo "Status: " . $enrollment->status_enrollment . "\n";
    echo "Sesi Ruangan ID: " . $enrollment->sesi_ruangan_id . "\n";

    if ($enrollment->sesiRuangan) {
        echo "Sesi: " . $enrollment->sesiRuangan->nama_sesi . " (Status: " . $enrollment->sesiRuangan->status . ")\n";
    }

    // Generate token if not exists
    if (!$enrollment->token_login) {
        $newToken = generateUniqueToken();
        $enrollment->token_login = $newToken;
        $enrollment->token_dibuat_pada = now();
        $enrollment->save();

        echo "✅ NEW TOKEN GENERATED: " . $newToken . "\n";
    } else {
        echo "Token already exists\n";
    }

    echo "----\n";
}

// Verify the tokens were created
echo "\n=== VERIFICATION ===\n";
$updatedEnrollments = EnrollmentUjian::where('siswa_id', $siswaId)->get();

foreach ($updatedEnrollments as $enrollment) {
    echo "Enrollment ID " . $enrollment->id . ": " . $enrollment->token_login . "\n";
}

echo "\n=== COMPLETED ===\n";
