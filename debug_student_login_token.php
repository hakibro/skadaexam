<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;

echo "🔍 Debug: Student Login Token Validation Issue\n";
echo "===============================================\n\n";

// Get sample data for debugging
echo "=== 1. Check Available Students with ID Yayasan ===\n";
try {
    $siswaList = Siswa::whereNotNull('idyayasan')
        ->select('id', 'idyayasan', 'nama', 'status_pembayaran')
        ->limit(5)
        ->get();

    if ($siswaList->isEmpty()) {
        echo "❌ No students with idyayasan found!\n";
        echo "Creating sample student...\n";

        $siswa = Siswa::create([
            'nis' => '2024' . rand(100, 999),
            'idyayasan' => 'SISWA' . rand(100, 999),
            'nama' => 'Debug Test Student',
            'email' => 'debug@test.com',
            'password' => 'password',
            'status_pembayaran' => 'Lunas',
            'rekomendasi' => 'ya',
        ]);
        echo "✅ Created: {$siswa->nama} - {$siswa->idyayasan}\n";
        $siswaList = collect([$siswa]);
    } else {
        echo "Found " . $siswaList->count() . " students:\n";
        foreach ($siswaList as $s) {
            echo "  - {$s->nama} ({$s->idyayasan}) - {$s->status_pembayaran}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

echo "\n=== 2. Check Available Enrollments ===\n";
$enrollments = EnrollmentUjian::with(['siswa', 'sesiRuangan'])
    ->whereNotNull('token_login')
    ->limit(10)
    ->get();

if ($enrollments->isEmpty()) {
    echo "❌ No enrollments with tokens found!\n";

    // Check for active sessions
    echo "Checking for active sessions...\n";
    $activeSessions = SesiRuangan::whereIn('status', ['belum_mulai', 'berlangsung'])
        ->where('tanggal', '>=', now()->toDateString())
        ->limit(5)
        ->get();

    if ($activeSessions->isEmpty()) {
        echo "❌ No active sessions found!\n";
        echo "Creating sample session...\n";

        $session = SesiRuangan::create([
            'kode_sesi' => 'DEBUG-' . rand(100, 999),
            'nama_sesi' => 'Debug Test Session',
            'tanggal' => now()->toDateString(),
            'waktu_mulai' => now()->format('H:i:s'),
            'waktu_selesai' => now()->addHours(2)->format('H:i:s'),
            'status' => 'berlangsung',
            'token_ujian' => strtoupper(\Illuminate\Support\Str::random(8)),
        ]);
        echo "✅ Created session: {$session->nama_sesi}\n";
        $activeSessions = collect([$session]);
    } else {
        echo "Found " . $activeSessions->count() . " active sessions:\n";
        foreach ($activeSessions as $s) {
            echo "  - {$s->nama_sesi} ({$s->status}) - {$s->tanggal}\n";
        }
    }

    // Create sample enrollment
    if ($siswaList->isNotEmpty() && $activeSessions->isNotEmpty()) {
        $siswa = $siswaList->first();
        $session = $activeSessions->first();

        echo "Creating enrollment for {$siswa->nama}...\n";
        $enrollment = EnrollmentUjian::create([
            'siswa_id' => $siswa->id,
            'sesi_ruangan_id' => $session->id,
            'status_enrollment' => 'enrolled',
            'token_login' => strtoupper(\Illuminate\Support\Str::random(6)),
            'token_dibuat_pada' => now(),
        ]);

        echo "✅ Created enrollment with token: {$enrollment->token_login}\n";
        $enrollments = collect([$enrollment->load(['siswa', 'sesiRuangan'])]);
    }
} else {
    echo "Found " . $enrollments->count() . " enrollments:\n";
    foreach ($enrollments as $e) {
        $siswaName = $e->siswa ? $e->siswa->nama : 'N/A';
        $sessionName = $e->sesiRuangan ? $e->sesiRuangan->nama_sesi : 'N/A';
        echo "  - Token: {$e->token_login} | Student: {$siswaName} | Session: {$sessionName} | Status: {$e->status_enrollment}\n";
    }
}

echo "\n=== 3. Test Login Validation Logic ===\n";
if ($enrollments->isNotEmpty()) {
    $testEnrollment = $enrollments->first();
    $testSiswa = $testEnrollment->siswa;
    $testToken = $testEnrollment->token_login;

    if ($testSiswa && $testToken) {
        echo "Testing with:\n";
        echo "  ID Yayasan: {$testSiswa->idyayasan}\n";
        echo "  Token: {$testToken}\n";
        echo "  Payment Status: {$testSiswa->status_pembayaran}\n";
        echo "  Enrollment Status: {$testEnrollment->status_enrollment}\n";

        echo "\nStep-by-step validation:\n";

        // Step 1: Find student
        $foundSiswa = Siswa::where('idyayasan', $testSiswa->idyayasan)->first();
        echo "1. Student found: " . ($foundSiswa ? "✅ Yes" : "❌ No") . "\n";

        if ($foundSiswa) {
            // Step 2: Payment status
            $paymentOk = $foundSiswa->status_pembayaran === 'Lunas';
            echo "2. Payment status OK: " . ($paymentOk ? "✅ Yes" : "❌ No - {$foundSiswa->status_pembayaran}") . "\n";

            // Step 3: Find enrollment
            $foundEnrollment = EnrollmentUjian::with(['sesiRuangan', 'siswa'])
                ->where('siswa_id', $foundSiswa->id)
                ->where('token_login', $testToken)
                ->whereIn('status_enrollment', ['enrolled', 'active'])
                ->first();

            echo "3. Enrollment found: " . ($foundEnrollment ? "✅ Yes" : "❌ No") . "\n";

            if (!$foundEnrollment) {
                // Debug why enrollment not found
                echo "   Debug - checking enrollments for this student:\n";
                $allEnrollments = EnrollmentUjian::where('siswa_id', $foundSiswa->id)->get();
                foreach ($allEnrollments as $e) {
                    echo "     - Token: {$e->token_login} | Status: {$e->status_enrollment}\n";
                }

                echo "   Debug - checking enrollments with this token:\n";
                $tokenEnrollments = EnrollmentUjian::where('token_login', $testToken)->get();
                foreach ($tokenEnrollments as $e) {
                    echo "     - Student ID: {$e->siswa_id} | Status: {$e->status_enrollment}\n";
                }
            } else {
                // Step 4: Token validation
                $tokenValid = $foundEnrollment->validateToken($testToken);
                echo "4. Token validation: " . ($tokenValid ? "✅ Valid" : "❌ Invalid") . "\n";

                // Step 5: Session status
                if ($foundEnrollment->sesiRuangan) {
                    $sessionOk = in_array($foundEnrollment->sesiRuangan->status, ['berlangsung', 'belum_mulai']);
                    echo "5. Session status OK: " . ($sessionOk ? "✅ Yes - {$foundEnrollment->sesiRuangan->status}" : "❌ No - {$foundEnrollment->sesiRuangan->status}") . "\n";

                    // Step 6: Time validation
                    $sesiRuangan = $foundEnrollment->sesiRuangan;
                    $now = now();
                    $sessionDate = $sesiRuangan->tanggal;
                    $startTime = $sessionDate . ' ' . $sesiRuangan->waktu_mulai;
                    $endTime = $sessionDate . ' ' . $sesiRuangan->waktu_selesai;

                    $timeOk = $now->gte($startTime) && $now->lte($endTime);
                    echo "6. Time validation: " . ($timeOk ? "✅ Within time" : "❌ Outside time") . "\n";
                    echo "   Current time: {$now}\n";
                    echo "   Session time: {$startTime} - {$endTime}\n";
                } else {
                    echo "5. Session: ❌ No session found\n";
                }
            }
        }
    }
}

echo "\n=== 4. Quick Fix Suggestions ===\n";
echo "If you're seeing 'Token tidak valid atau sudah tidak aktif':\n";
echo "1. Check if EnrollmentUjian record exists with the exact token\n";
echo "2. Verify status_enrollment is 'enrolled' or 'active'\n";
echo "3. Ensure siswa_id matches the student's ID\n";
echo "4. Check if token is exactly 6 characters and uppercase\n";
echo "5. Verify payment status is 'Lunas'\n";

echo "\n=== 5. Sample Valid Credentials (if available) ===\n";
if ($enrollments->isNotEmpty()) {
    $validEnrollment = $enrollments->first();
    if ($validEnrollment && $validEnrollment->siswa) {
        echo "ID Yayasan: {$validEnrollment->siswa->idyayasan}\n";
        echo "Token: {$validEnrollment->token_login}\n";
        echo "Status: {$validEnrollment->status_enrollment}\n";
    }
}

echo "\n✅ Debug complete!\n";
