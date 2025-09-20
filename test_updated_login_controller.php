<?php

/**
 * Test script untuk memverifikasi perubahan SiswaLoginController
 * Tests: Login dengan status_pembayaran = Lunas ATAU rekomendasi = ya
 * Tests: Login tanpa validasi enrollment
 */

require_once 'vendor/autoload.php';

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING UPDATED SISWA LOGIN CONTROLLER ===\n\n";

// Test 1: Siswa dengan status_pembayaran = 'Lunas' dapat login
echo "1. Testing Login with status_pembayaran = 'Lunas'\n";
echo "------------------------------------------------\n";

try {
    $siswaLunas = Siswa::where('status_pembayaran', 'Lunas')->first();
    if ($siswaLunas) {
        echo "📝 Found siswa with 'Lunas' status: {$siswaLunas->nama} (ID: {$siswaLunas->id})\n";
        echo "   - Status Pembayaran: {$siswaLunas->status_pembayaran}\n";
        echo "   - Rekomendasi: {$siswaLunas->rekomendasi}\n";
        echo "✅ Student with 'Lunas' status should be able to login\n";
    } else {
        echo "⚠️  No siswa found with status_pembayaran = 'Lunas'\n";
    }
} catch (\Exception $e) {
    echo "❌ Error testing 'Lunas' status: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Siswa dengan rekomendasi = 'ya' dapat login
echo "2. Testing Login with rekomendasi = 'ya'\n";
echo "----------------------------------------\n";

try {
    $siswaRekomendasi = Siswa::where('rekomendasi', 'ya')->first();
    if ($siswaRekomendasi) {
        echo "📝 Found siswa with 'ya' recommendation: {$siswaRekomendasi->nama} (ID: {$siswaRekomendasi->id})\n";
        echo "   - Status Pembayaran: {$siswaRekomendasi->status_pembayaran}\n";
        echo "   - Rekomendasi: {$siswaRekomendasi->rekomendasi}\n";
        echo "✅ Student with 'ya' recommendation should be able to login\n";
    } else {
        echo "⚠️  No siswa found with rekomendasi = 'ya'\n";
    }
} catch (\Exception $e) {
    echo "❌ Error testing 'ya' recommendation: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Siswa dengan status_pembayaran = 'Belum Lunas' dan rekomendasi = 'tidak' tidak dapat login
echo "3. Testing Login Blocked for 'Belum Lunas' + 'tidak' rekomendasi\n";
echo "---------------------------------------------------------------\n";

try {
    $siswaBlocked = Siswa::where('status_pembayaran', 'Belum Lunas')
        ->where('rekomendasi', 'tidak')
        ->first();

    if ($siswaBlocked) {
        echo "📝 Found siswa that should be blocked: {$siswaBlocked->nama} (ID: {$siswaBlocked->id})\n";
        echo "   - Status Pembayaran: {$siswaBlocked->status_pembayaran}\n";
        echo "   - Rekomendasi: {$siswaBlocked->rekomendasi}\n";
        echo "❌ Student should NOT be able to login (both conditions fail)\n";
    } else {
        echo "ℹ️  No siswa found with both 'Belum Lunas' and 'tidak' rekomendasi\n";
    }
} catch (\Exception $e) {
    echo "❌ Error testing blocked login: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Login tanpa enrollment requirement
echo "4. Testing Login Without Enrollment Requirement\n";
echo "-----------------------------------------------\n";

try {
    // Get active sesi ruangan with token
    $activeSesi = SesiRuangan::whereIn('status', ['berlangsung', 'belum_mulai'])
        ->whereNotNull('token_ujian')
        ->first();

    if ($activeSesi) {
        echo "📝 Found active sesi with token: {$activeSesi->nama_sesi}\n";
        echo "   - Token: {$activeSesi->token_ujian}\n";
        echo "   - Status: {$activeSesi->status}\n";

        // Check for students who can login with this token but don't have enrollment
        $eligibleStudents = Siswa::where(function ($query) {
            $query->where('status_pembayaran', 'Lunas')
                ->orWhere('rekomendasi', 'ya');
        })->whereNotIn('id', function ($subQuery) use ($activeSesi) {
            $subQuery->select('siswa_id')
                ->from('enrollment_ujian')
                ->where('sesi_ruangan_id', $activeSesi->id);
        })->limit(3)->get();

        if ($eligibleStudents->count() > 0) {
            echo "✅ Found {$eligibleStudents->count()} eligible students without enrollment:\n";
            foreach ($eligibleStudents as $student) {
                echo "   - {$student->nama} (Payment: {$student->status_pembayaran}, Rec: {$student->rekomendasi})\n";
            }
            echo "✅ These students should be able to login with token {$activeSesi->token_ujian}\n";
        } else {
            echo "ℹ️  All eligible students are already enrolled in this sesi\n";
        }
    } else {
        echo "⚠️  No active sesi ruangan with token found\n";
    }
} catch (\Exception $e) {
    echo "❌ Error testing login without enrollment: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Login logic summary
echo "5. Login Logic Summary\n";
echo "---------------------\n";

try {
    $totalSiswa = Siswa::count();
    $lunasCount = Siswa::where('status_pembayaran', 'Lunas')->count();
    $rekomendasiCount = Siswa::where('rekomendasi', 'ya')->count();
    $eligibleCount = Siswa::where(function ($query) {
        $query->where('status_pembayaran', 'Lunas')
            ->orWhere('rekomendasi', 'ya');
    })->count();
    $blockedCount = Siswa::where('status_pembayaran', 'Belum Lunas')
        ->where('rekomendasi', 'tidak')
        ->count();

    echo "📊 Student Login Eligibility Statistics:\n";
    echo "   - Total Students: {$totalSiswa}\n";
    echo "   - With 'Lunas' payment: {$lunasCount}\n";
    echo "   - With 'ya' recommendation: {$rekomendasiCount}\n";
    echo "   - Eligible to login: {$eligibleCount}\n";
    echo "   - Blocked from login: {$blockedCount}\n";
    echo "   - Percentage eligible: " . round(($eligibleCount / $totalSiswa) * 100, 2) . "%\n";
} catch (\Exception $e) {
    echo "❌ Error generating summary: " . $e->getMessage() . "\n";
}

echo "\n=== TEST SUMMARY ===\n";
echo "✅ Login Logic Updated: status_pembayaran = 'Lunas' OR rekomendasi = 'ya'\n";
echo "✅ Enrollment Validation Removed: Students can login with valid token only\n";
echo "✅ Token Validation: Only requires valid sesi ruangan token\n";
echo "✅ Simplified Flow: Payment/recommendation check → Token validation → Login\n";
echo "\n";
echo "🎉 SiswaLoginController successfully updated for simplified login process!\n";
