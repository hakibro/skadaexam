<?php

use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/debug/student-login', function () {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    echo "<h1>🔍 Debug: Student Login Token Validation</h1>";
    echo "<style>body { font-family: monospace; margin: 20px; } h1,h2,h3 { color: #333; } .success { color: green; } .error { color: red; } .warning { color: orange; } pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }</style>";

    echo "<h2>1. Available Students with ID Yayasan</h2>";
    $siswaList = Siswa::whereNotNull('idyayasan')
        ->select('id', 'idyayasan', 'nama', 'status_pembayaran')
        ->limit(10)
        ->get();

    if ($siswaList->isEmpty()) {
        echo "<p class='error'>❌ No students with idyayasan found!</p>";

        // Create sample student
        $siswa = Siswa::create([
            'nis' => '2024' . rand(100, 999),
            'idyayasan' => 'DEBUG' . rand(100, 999),
            'nama' => 'Debug Test Student',
            'email' => 'debug' . rand(100, 999) . '@test.com',
            'password' => 'password',
            'status_pembayaran' => 'Lunas',
            'rekomendasi' => 'ya',
        ]);
        echo "<p class='success'>✅ Created sample student: {$siswa->nama} ({$siswa->idyayasan})</p>";
        $siswaList = collect([$siswa]);
    } else {
        echo "<p class='success'>Found " . $siswaList->count() . " students:</p>";
        echo "<ul>";
        foreach ($siswaList as $s) {
            echo "<li>{$s->nama} (<strong>{$s->idyayasan}</strong>) - {$s->status_pembayaran}</li>";
        }
        echo "</ul>";
    }

    echo "<h2>2. Available Enrollments with Tokens</h2>";
    $enrollments = EnrollmentUjian::with(['siswa', 'sesiRuangan'])
        ->whereNotNull('token_login')
        ->limit(10)
        ->get();

    if ($enrollments->isEmpty()) {
        echo "<p class='error'>❌ No enrollments with tokens found!</p>";

        // Find or create active session
        $activeSession = SesiRuangan::whereIn('status', ['belum_mulai', 'berlangsung'])
            ->where('tanggal', '>=', now()->toDateString())
            ->first();

        if (!$activeSession) {
            echo "<p class='warning'>Creating sample session...</p>";
            $activeSession = SesiRuangan::create([
                'kode_sesi' => 'DEBUG-' . rand(100, 999),
                'nama_sesi' => 'Debug Test Session ' . now()->format('H:i'),
                'tanggal' => now()->toDateString(),
                'waktu_mulai' => now()->format('H:i:s'),
                'waktu_selesai' => now()->addHours(2)->format('H:i:s'),
                'status' => 'berlangsung',
                'token_ujian' => strtoupper(Str::random(8)),
            ]);
            echo "<p class='success'>✅ Created session: {$activeSession->nama_sesi}</p>";
        }

        // Create enrollment for first student
        if ($siswaList->isNotEmpty()) {
            $firstStudent = $siswaList->first();
            $enrollment = EnrollmentUjian::create([
                'siswa_id' => $firstStudent->id,
                'sesi_ruangan_id' => $activeSession->id,
                'status_enrollment' => 'enrolled',
                'token_login' => strtoupper(Str::random(6)),
                'token_dibuat_pada' => now(),
            ]);

            echo "<p class='success'>✅ Created enrollment with token: <strong>{$enrollment->token_login}</strong></p>";
            $enrollments = collect([$enrollment->load(['siswa', 'sesiRuangan'])]);
        }
    } else {
        echo "<p class='success'>Found " . $enrollments->count() . " enrollments:</p>";
        echo "<ul>";
        foreach ($enrollments as $e) {
            $siswaName = $e->siswa ? $e->siswa->nama : 'N/A';
            $siswaIdyayasan = $e->siswa ? $e->siswa->idyayasan : 'N/A';
            $sessionName = $e->sesiRuangan ? $e->sesiRuangan->nama_sesi : 'N/A';
            echo "<li><strong>Token: {$e->token_login}</strong> | Student: {$siswaName} ({$siswaIdyayasan}) | Session: {$sessionName} | Status: {$e->status_enrollment}</li>";
        }
        echo "</ul>";
    }

    echo "<h2>3. Test Login Validation Logic</h2>";
    if ($enrollments->isNotEmpty()) {
        $testEnrollment = $enrollments->first();
        $testSiswa = $testEnrollment->siswa;
        $testToken = $testEnrollment->token_login;

        if ($testSiswa && $testToken) {
            echo "<h3>Testing with:</h3>";
            echo "<ul>";
            echo "<li><strong>ID Yayasan:</strong> {$testSiswa->idyayasan}</li>";
            echo "<li><strong>Token:</strong> {$testToken}</li>";
            echo "<li><strong>Payment Status:</strong> {$testSiswa->status_pembayaran}</li>";
            echo "<li><strong>Enrollment Status:</strong> {$testEnrollment->status_enrollment}</li>";
            echo "</ul>";

            echo "<h3>Step-by-step validation:</h3>";
            echo "<ol>";

            // Step 1: Find student
            $foundSiswa = Siswa::where('idyayasan', $testSiswa->idyayasan)->first();
            echo "<li>Student found: " . ($foundSiswa ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</li>";

            if ($foundSiswa) {
                // Step 2: Payment status
                $paymentOk = $foundSiswa->status_pembayaran === 'Lunas';
                echo "<li>Payment status OK: " . ($paymentOk ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No - {$foundSiswa->status_pembayaran}</span>") . "</li>";

                // Step 3: Find enrollment
                $foundEnrollment = EnrollmentUjian::with(['sesiRuangan', 'siswa'])
                    ->where('siswa_id', $foundSiswa->id)
                    ->where('token_login', $testToken)
                    ->whereIn('status_enrollment', ['enrolled', 'active'])
                    ->first();

                echo "<li>Enrollment found: " . ($foundEnrollment ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</li>";

                if (!$foundEnrollment) {
                    echo "<li style='color: orange;'>Debug - checking enrollments for this student:";
                    $allEnrollments = EnrollmentUjian::where('siswa_id', $foundSiswa->id)->get();
                    echo "<ul>";
                    foreach ($allEnrollments as $e) {
                        echo "<li>Token: {$e->token_login} | Status: {$e->status_enrollment}</li>";
                    }
                    echo "</ul>";

                    echo "Debug - checking enrollments with this token:";
                    $tokenEnrollments = EnrollmentUjian::where('token_login', $testToken)->get();
                    echo "<ul>";
                    foreach ($tokenEnrollments as $e) {
                        echo "<li>Student ID: {$e->siswa_id} | Status: {$e->status_enrollment}</li>";
                    }
                    echo "</ul></li>";
                } else {
                    // Step 4: Token validation
                    $tokenValid = $foundEnrollment->validateToken($testToken);
                    echo "<li>Token validation: " . ($tokenValid ? "<span class='success'>✅ Valid</span>" : "<span class='error'>❌ Invalid</span>") . "</li>";

                    // Step 5: Session status
                    if ($foundEnrollment->sesiRuangan) {
                        $sessionOk = in_array($foundEnrollment->sesiRuangan->status, ['berlangsung', 'belum_mulai']);
                        echo "<li>Session status OK: " . ($sessionOk ? "<span class='success'>✅ Yes - {$foundEnrollment->sesiRuangan->status}</span>" : "<span class='error'>❌ No - {$foundEnrollment->sesiRuangan->status}</span>") . "</li>";

                        // Step 6: Time validation
                        $sesiRuangan = $foundEnrollment->sesiRuangan;
                        $now = now();
                        $sessionDate = $sesiRuangan->tanggal;
                        $startTime = $sessionDate . ' ' . $sesiRuangan->waktu_mulai;
                        $endTime = $sessionDate . ' ' . $sesiRuangan->waktu_selesai;

                        $timeOk = $now->gte($startTime) && $now->lte($endTime);
                        echo "<li>Time validation: " . ($timeOk ? "<span class='success'>✅ Within time</span>" : "<span class='warning'>❌ Outside time</span>") . "<br>";
                        echo "Current: {$now}<br>";
                        echo "Session: {$startTime} - {$endTime}</li>";
                    } else {
                        echo "<li><span class='error'>Session: ❌ No session found</span></li>";
                    }
                }
            }
            echo "</ol>";
        }
    }

    echo "<h2>4. Valid Test Credentials</h2>";
    if ($enrollments->isNotEmpty()) {
        $validEnrollment = $enrollments->first();
        if ($validEnrollment && $validEnrollment->siswa) {
            echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007cba;'>";
            echo "<h3>Use these credentials to test login:</h3>";
            echo "<p><strong>ID Yayasan:</strong> <code>{$validEnrollment->siswa->idyayasan}</code></p>";
            echo "<p><strong>Token:</strong> <code>{$validEnrollment->token_login}</code></p>";
            echo "<p><strong>Login URL:</strong> <a href='" . url('/login/siswa') . "' target='_blank'>" . url('/login/siswa') . "</a></p>";
            echo "</div>";
        }
    }

    echo "<h2>5. All Current Enrollments (Debug)</h2>";
    $allEnrollments = EnrollmentUjian::with(['siswa', 'sesiRuangan'])
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    if ($allEnrollments->isNotEmpty()) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Student</th><th>ID Yayasan</th><th>Token</th><th>Status</th><th>Session</th><th>Created</th></tr>";
        foreach ($allEnrollments as $e) {
            $siswaName = $e->siswa ? $e->siswa->nama : 'N/A';
            $siswaIdyayasan = $e->siswa ? $e->siswa->idyayasan : 'N/A';
            $sessionName = $e->sesiRuangan ? $e->sesiRuangan->nama_sesi : 'N/A';
            echo "<tr>";
            echo "<td>{$e->id}</td>";
            echo "<td>{$siswaName}</td>";
            echo "<td><strong>{$siswaIdyayasan}</strong></td>";
            echo "<td><code>{$e->token_login}</code></td>";
            echo "<td>{$e->status_enrollment}</td>";
            echo "<td>{$sessionName}</td>";
            echo "<td>{$e->created_at}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No enrollments found.</p>";
    }

    echo "<hr><p><small>This debug page is only available in development mode.</small></p>";
});

// Route to create test student login data
Route::get('/debug/create-student-test-data', function () {
    if (!app()->environment(['local', 'development'])) {
        abort(404);
    }

    try {
        // Create or find a test student
        $student = Siswa::where('idyayasan', 'TEST001')->first();
        if (!$student) {
            $student = Siswa::create([
                'nis' => 'TEST001',
                'idyayasan' => 'TEST001',
                'nama' => 'Test Student for Login',
                'email' => 'test001@smkdata.sch.id',
                'password' => 'password',
                'status_pembayaran' => 'Lunas',
                'rekomendasi' => 'ya',
            ]);
        }

        // Create or find an active session
        $session = SesiRuangan::where('status', 'berlangsung')->first();
        if (!$session) {
            $session = SesiRuangan::create([
                'kode_sesi' => 'TEST-SESSION-001',
                'nama_sesi' => 'Test Session for Login',
                'tanggal' => now()->toDateString(),
                'waktu_mulai' => now()->subMinutes(30)->format('H:i:s'),
                'waktu_selesai' => now()->addHours(2)->format('H:i:s'),
                'status' => 'berlangsung',
                'token_ujian' => 'TESTSESS',
            ]);
        }

        // Delete any existing enrollment for this student
        EnrollmentUjian::where('siswa_id', $student->id)->delete();

        // Create new enrollment with fresh token
        $enrollment = EnrollmentUjian::create([
            'siswa_id' => $student->id,
            'sesi_ruangan_id' => $session->id,
            'status_enrollment' => 'enrolled',
            'token_login' => 'TEST01', // 6 character token
            'token_dibuat_pada' => now(),
        ]);

        return response("<h1>✅ Test Data Created Successfully!</h1>
            <p><strong>Student:</strong> {$student->nama}</p>
            <p><strong>ID Yayasan:</strong> <code>{$student->idyayasan}</code></p>
            <p><strong>Token:</strong> <code>{$enrollment->token_login}</code></p>
            <p><strong>Payment Status:</strong> {$student->status_pembayaran}</p>
            <p><strong>Session:</strong> {$session->nama_sesi}</p>
            <p><strong>Session Status:</strong> {$session->status}</p>
            <hr>
            <p><a href='" . url('/login/siswa') . "'>🔗 Go to Student Login</a></p>
            <p><a href='" . url('/debug/student-login') . "'>🔍 Debug Student Login</a></p>
        ");
    } catch (Exception $e) {
        return response("<h1>❌ Error Creating Test Data</h1><p>{$e->getMessage()}</p>");
    }
});
