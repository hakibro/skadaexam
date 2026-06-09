<?php
// Autoload Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use Illuminate\Support\Facades\DB;

echo "=== Testing siswa login after enum fix ===\n\n";

// First, verify enum was updated
$columnInfo = DB::select("SHOW COLUMNS FROM enrollment_ujian WHERE Field = 'status_enrollment'")[0];
echo "✅ Current enum values: {$columnInfo->Type}\n\n";

// Find siswa
$idyayasan = '220138';
$token = 'ODQRLJ';

$siswa = Siswa::where('idyayasan', $idyayasan)->first();
if (!$siswa) {
    echo "❌ Siswa not found\n";
    exit;
}

echo "✅ Found siswa: {$siswa->nama} (ID: {$siswa->id})\n";

// Find enrollment
$enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
    ->whereHas('sesiRuangan', function ($q) use ($token) {
        $q->where('token_ujian', $token);
    })
    ->with('sesiRuangan')
    ->first();

if (!$enrollment) {
    echo "❌ No enrollment found with token {$token}\n";
    exit;
}

echo "✅ Found enrollment: ID {$enrollment->id}, current status: '{$enrollment->status_enrollment}'\n";

// Test the startExam method
echo "\n=== Testing startExam method ===\n";

try {
    $enrollment->startExam();
    echo "✅ startExam() successful!\n";
    echo "   - New status: '{$enrollment->status_enrollment}'\n";
    echo "   - Waktu mulai: {$enrollment->waktu_mulai_ujian}\n";
    echo "   - Last login: {$enrollment->last_login_at}\n";

    // Reset for actual login test
    $enrollment->status_enrollment = 'enrolled';
    $enrollment->waktu_mulai_ujian = null;
    $enrollment->last_login_at = null;
    $enrollment->save();
    echo "✅ Reset enrollment for actual test\n";
} catch (\Exception $e) {
    echo "❌ startExam() failed: {$e->getMessage()}\n";
}

echo "\n🎉 Siswa login should work now!\n";
echo "Try: idyayasan={$idyayasan}, token={$token}\n";
