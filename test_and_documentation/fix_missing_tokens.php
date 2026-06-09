<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EnrollmentUjian;
use Illuminate\Support\Str;

echo "=== FIXING ALL ENROLLMENTS WITHOUT TOKENS ===\n";

// Function to generate unique token
function generateUniqueToken()
{
    do {
        $token = strtoupper(Str::random(6));
    } while (EnrollmentUjian::where('token_login', $token)->exists());

    return $token;
}

// Find all enrollments without tokens
$enrollmentsWithoutTokens = EnrollmentUjian::whereNull('token_login')
    ->orWhere('token_login', '')
    ->with(['siswa', 'sesiRuangan'])
    ->get();

echo "Found " . $enrollmentsWithoutTokens->count() . " enrollments without tokens\n\n";

if ($enrollmentsWithoutTokens->count() == 0) {
    echo "✅ All enrollments already have tokens!\n";
    exit;
}

$fixed = 0;

foreach ($enrollmentsWithoutTokens as $enrollment) {
    echo "Fixing Enrollment ID: " . $enrollment->id . "\n";
    echo "- Siswa: " . ($enrollment->siswa ? $enrollment->siswa->nama : 'N/A') . " (ID: " . $enrollment->siswa_id . ")\n";
    echo "- Sesi: " . ($enrollment->sesiRuangan ? $enrollment->sesiRuangan->nama_sesi : 'N/A') . "\n";
    echo "- Status: " . $enrollment->status_enrollment . "\n";

    $newToken = generateUniqueToken();
    $enrollment->token_login = $newToken;
    $enrollment->token_dibuat_pada = now();
    $enrollment->save();

    echo "✅ Generated token: " . $newToken . "\n\n";
    $fixed++;
}

echo "=== SUMMARY ===\n";
echo "Fixed " . $fixed . " enrollments\n";

// Verify all enrollments now have tokens
$stillMissing = EnrollmentUjian::whereNull('token_login')
    ->orWhere('token_login', '')
    ->count();

if ($stillMissing == 0) {
    echo "✅ All enrollments now have tokens!\n";
} else {
    echo "⚠️  Still " . $stillMissing . " enrollments without tokens\n";
}

echo "\n=== COMPLETED ===\n";
