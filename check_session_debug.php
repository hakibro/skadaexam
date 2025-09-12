<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING SESSIONS TABLE ===\n";

// Check if sessions table exists
$sessionsExist = DB::select("SHOW TABLES LIKE 'sessions'");
if (empty($sessionsExist)) {
    echo "Sessions table tidak ada. Mungkin menggunakan file-based sessions.\n";
} else {
    echo "Sessions table ada. Cek data session:\n";
    $sessions = DB::table('sessions')->get();
    echo "Total sessions: " . $sessions->count() . "\n";

    foreach ($sessions as $session) {
        $payload = base64_decode($session->payload);
        if ($payload) {
            // Parse Laravel session data
            $data = @unserialize($payload);
            if ($data && isset($data['_token'])) {
                echo "\nSession ID: " . substr($session->id, 0, 10) . "...\n";
                echo "User ID: " . ($session->user_id ?? 'NULL') . "\n";

                // Check for siswa guard session
                if (isset($data['login_siswa_59ba36addc2b2f9401580f014c7f58ea4e30989d'])) {
                    echo "Siswa logged in: {$data['login_siswa_59ba36addc2b2f9401580f014c7f58ea4e30989d']}\n";
                }

                // Check enrollment session data
                if (isset($data['current_enrollment_id'])) {
                    echo "Current enrollment ID: {$data['current_enrollment_id']}\n";
                }
                if (isset($data['current_sesi_ruangan_id'])) {
                    echo "Current sesi ruangan ID: {$data['current_sesi_ruangan_id']}\n";
                }
            }
        }
    }
}

echo "\n=== ALTERNATIVE: CHECK VIA DIRECT LOGIN ===\n";

// Test login siswa 255092 dengan token ODQRLJ
use App\Models\Siswa;
use App\Models\EnrollmentUjian;

$siswa = Siswa::where('idyayasan', '255092')->first();
if ($siswa) {
    echo "Siswa found: {$siswa->nama}\n";

    // Find active enrollment dengan token ODQRLJ
    $enrollment = EnrollmentUjian::with(['sesiRuangan'])
        ->where('siswa_id', $siswa->id)
        ->whereHas('sesiRuangan', function ($query) {
            $query->where('token_ujian', 'ODQRLJ');
        })
        ->whereIn('status_enrollment', ['enrolled', 'active'])
        ->first();

    if ($enrollment) {
        echo "Enrollment found: {$enrollment->id}\n";
        echo "Sesi ruangan: {$enrollment->sesiRuangan->nama_sesi}\n";
        echo "Status enrollment: {$enrollment->status_enrollment}\n";
        echo "Status sesi: {$enrollment->sesiRuangan->status}\n";

        // Manual set session untuk testing
        echo "\nDashboard akan menampilkan:\n";
        echo "- Current enrollment: YES\n";
        echo "- Sesi ruangan: {$enrollment->sesiRuangan->nama_sesi}\n";
        echo "- Status: {$enrollment->sesiRuangan->status}\n";
        echo "- Token: {$enrollment->sesiRuangan->token_ujian}\n";

        // Check jadwal ujians
        $jadwals = $enrollment->sesiRuangan->jadwalUjians;
        echo "- Jadwal ujians: " . $jadwals->count() . "\n";
        foreach ($jadwals as $jadwal) {
            echo "  * {$jadwal->judul} - {$jadwal->mapel->nama_mapel} (Status: {$jadwal->status})\n";
        }
    } else {
        echo "No matching enrollment found\n";
    }
} else {
    echo "Siswa 255092 not found\n";
}
