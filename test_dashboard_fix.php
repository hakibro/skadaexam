<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EnrollmentUjian;
use Carbon\Carbon;

echo "=== Testing Dashboard Fix for Siswa ID 1 (ID Yayasan 255092) ===\n\n";

// Simulate what dashboard controller would do
$siswaId = 1; // Actual siswa.id, not id_yayasan

// Step 1: Check if enrollment exists with proper conditions
$currentEnrollment = EnrollmentUjian::with([
    'sesiRuangan.ruangan',
    'sesiRuangan.jadwalUjians.mapel'
])
    ->where('siswa_id', $siswaId)
    ->whereIn('status_enrollment', ['enrolled', 'active'])
    ->whereHas('sesiRuangan', function ($query) {
        $query->whereIn('status', ['berlangsung', 'belum_mulai'])
            ->where('token_expired_at', '>', now());
    })
    ->latest()
    ->first();

if ($currentEnrollment) {
    echo "✅ Found active enrollment:\n";
    echo "   - Enrollment ID: {$currentEnrollment->id}\n";
    echo "   - Status: {$currentEnrollment->status_enrollment}\n";
    echo "   - Sesi Ruangan ID: {$currentEnrollment->sesi_ruangan_id}\n";
    echo "   - Ruangan: {$currentEnrollment->sesiRuangan->ruangan->nama_ruangan}\n";
    echo "   - Token: {$currentEnrollment->sesiRuangan->token}\n";
    echo "   - Status Sesi: {$currentEnrollment->sesiRuangan->status}\n";

    $expiredAt = Carbon::parse($currentEnrollment->sesiRuangan->token_expired_at);
    $now = now();
    echo "   - Token Valid Until: {$expiredAt->format('Y-m-d H:i:s')}\n";
    echo "   - Current Time: {$now->format('Y-m-d H:i:s')}\n";
    echo "   - Token Still Valid: " . ($expiredAt > $now ? "YES" : "NO") . "\n";

    echo "\n   Jadwal Ujians in this session:\n";
    foreach ($currentEnrollment->sesiRuangan->jadwalUjians as $jadwal) {
        echo "   - {$jadwal->mapel->nama_mapel}\n";
    }

    echo "\n🎯 Dashboard should now show active exam session!\n";
} else {
    echo "❌ No active enrollment found\n";

    // Debug why not found
    echo "\n=== Debug Analysis ===\n";

    $allEnrollments = EnrollmentUjian::with('sesiRuangan')
        ->where('siswa_id', $siswaId)
        ->get();

    echo "Found {$allEnrollments->count()} enrollments for siswa ID {$siswaId}\n";

    foreach ($allEnrollments as $enrollment) {
        echo "\nEnrollment {$enrollment->id}:\n";
        echo "  - Status: {$enrollment->status_enrollment}\n";
        echo "  - Sesi Status: {$enrollment->sesiRuangan->status}\n";
        echo "  - Token Expired: {$enrollment->sesiRuangan->token_expired_at}\n";

        if ($enrollment->sesiRuangan->token_expired_at) {
            $expiry = Carbon::parse($enrollment->sesiRuangan->token_expired_at);
            echo "  - Valid? " . ($expiry > now() ? "YES" : "NO") . "\n";
            echo "  - Current Time: " . now()->format('Y-m-d H:i:s') . "\n";
            echo "  - Expiry Time: " . $expiry->format('Y-m-d H:i:s') . "\n";
        } else {
            echo "  - No expiry time set\n";
        }

        // Check individual conditions
        echo "  - Status match: " . (in_array($enrollment->status_enrollment, ['enrolled', 'active']) ? "YES" : "NO") . "\n";
        echo "  - Sesi status match: " . (in_array($enrollment->sesiRuangan->status, ['berlangsung', 'belum_mulai']) ? "YES" : "NO") . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
