<?php
require __DIR__ . '/bootstrap/app.php';

use App\Models\Siswa;
use App\Models\SesiRuangan;
use App\Models\EnrollmentUjian;
use Carbon\Carbon;

// Test login siswa dengan token sesi ruangan
$idYayasan = '12345678910';  // ID Yayasan siswa 39
$token = 'ODQRLJ';

echo "=== Testing Siswa Login with Sesi Ruangan Token ===\n";
echo "ID Yayasan: {$idYayasan}\n";
echo "Token: {$token}\n\n";

// 1. Cari siswa berdasarkan ID Yayasan
$siswa = Siswa::where('id_yayasan', $idYayasan)->first();

if (!$siswa) {
    echo "❌ Siswa dengan ID Yayasan {$idYayasan} tidak ditemukan\n";
    exit;
}

echo "✅ Siswa ditemukan:\n";
echo "   - ID: {$siswa->id}\n";
echo "   - Nama: {$siswa->nama}\n";
echo "   - ID Yayasan: {$siswa->id_yayasan}\n\n";

// 2. Cari enrollment siswa dengan token sesi ruangan yang cocok
$enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
    ->whereHas('sesiRuangan', function ($query) use ($token) {
        $query->where('token_ujian', $token);
    })
    ->with(['sesiRuangan.jadwalUjians', 'jadwalUjian'])
    ->first();

if (!$enrollment) {
    echo "❌ Enrollment dengan token {$token} tidak ditemukan untuk siswa {$siswa->nama}\n\n";

    // Cek apakah ada enrollment untuk siswa ini
    $allEnrollments = EnrollmentUjian::where('siswa_id', $siswa->id)
        ->with('sesiRuangan')
        ->get();

    echo "Enrollment yang tersedia untuk siswa ini:\n";
    foreach ($allEnrollments as $enr) {
        $tokenSesi = $enr->sesiRuangan ? $enr->sesiRuangan->token_ujian : 'NULL';
        echo "   - Sesi ID: {$enr->sesi_ruangan_id}, Token: {$tokenSesi}\n";
    }
    exit;
}

echo "✅ Enrollment ditemukan:\n";
echo "   - Enrollment ID: {$enrollment->id}\n";
echo "   - Sesi Ruangan ID: {$enrollment->sesi_ruangan_id}\n";
echo "   - Status: {$enrollment->status_enrollment}\n";
echo "   - Token Sesi: {$enrollment->sesiRuangan->token_ujian}\n\n";

// 3. Validasi token belum expired
$tokenExpired = $enrollment->sesiRuangan->token_expired_at;
$now = Carbon::now();

echo "✅ Validasi Token:\n";
echo "   - Token Expired At: " . ($tokenExpired ? $tokenExpired->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "   - Current Time: " . $now->format('Y-m-d H:i:s') . "\n";

if ($tokenExpired && $now->greaterThan($tokenExpired)) {
    echo "❌ Token sudah expired!\n";
    exit;
} else {
    echo "✅ Token masih valid\n\n";
}

// 4. Validasi waktu ujian
$jadwalUjian = $enrollment->jadwalUjian ?? $enrollment->sesiRuangan->jadwalUjians()->first();

if (!$jadwalUjian) {
    echo "❌ Jadwal ujian tidak ditemukan\n";
    exit;
}

echo "✅ Jadwal Ujian:\n";
echo "   - Jadwal ID: {$jadwalUjian->id}\n";
echo "   - Tanggal: {$jadwalUjian->tanggal}\n";
echo "   - Jam Mulai: {$jadwalUjian->jam_mulai}\n";
echo "   - Jam Selesai: {$jadwalUjian->jam_selesai}\n";

$examStart = Carbon::parse($jadwalUjian->tanggal . ' ' . $jadwalUjian->jam_mulai);
$examEnd = Carbon::parse($jadwalUjian->tanggal . ' ' . $jadwalUjian->jam_selesai);

echo "   - Waktu Mulai: " . $examStart->format('Y-m-d H:i:s') . "\n";
echo "   - Waktu Selesai: " . $examEnd->format('Y-m-d H:i:s') . "\n";
echo "   - Current Time: " . $now->format('Y-m-d H:i:s') . "\n";

if ($now->lessThan($examStart)) {
    echo "❌ Ujian belum dimulai\n";
} elseif ($now->greaterThan($examEnd)) {
    echo "❌ Ujian sudah berakhir\n";
} else {
    echo "✅ Ujian sedang berlangsung - Login berhasil!\n";
}

echo "\n=== LOGIN SUCCESS ===\n";
echo "Siswa {$siswa->nama} berhasil login dengan token sesi ruangan {$token}\n";
