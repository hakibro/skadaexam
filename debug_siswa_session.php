<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;

// Cek data siswa 255092 yang kita setup sebelumnya
$siswa = Siswa::where('idyayasan', '255092')->first();

if ($siswa) {
    echo "=== DATA SISWA ===\n";
    echo "ID: {$siswa->id}\n";
    echo "ID Yayasan: {$siswa->idyayasan}\n";
    echo "Nama: {$siswa->nama}\n";
    echo "Status Pembayaran: {$siswa->status_pembayaran}\n";
    echo "Rekomendasi: {$siswa->rekomendasi}\n\n";

    echo "=== ENROLLMENT DATA ===\n";
    $enrollments = EnrollmentUjian::where('siswa_id', $siswa->id)->get();

    if ($enrollments->count() > 0) {
        foreach ($enrollments as $enrollment) {
            echo "Enrollment ID: {$enrollment->id}\n";
            echo "Status: {$enrollment->status_enrollment}\n";
            echo "Sesi Ruangan ID: {$enrollment->sesi_ruangan_id}\n";

            if ($enrollment->sesiRuangan) {
                echo "Sesi Ruangan: {$enrollment->sesiRuangan->nama_sesi}\n";
                echo "Token: {$enrollment->sesiRuangan->token_ujian}\n";
                echo "Status Sesi: {$enrollment->sesiRuangan->status}\n";
                echo "Waktu: {$enrollment->sesiRuangan->waktu_mulai} - {$enrollment->sesiRuangan->waktu_selesai}\n";

                // Cek jadwal ujian yang terkait
                $jadwals = $enrollment->sesiRuangan->jadwalUjians;
                echo "Jadwal Ujians: " . $jadwals->count() . "\n";

                foreach ($jadwals as $jadwal) {
                    echo "  - Jadwal ID: {$jadwal->id}\n";
                    echo "    Judul: {$jadwal->judul}\n";
                    echo "    Tanggal: {$jadwal->tanggal}\n";
                    echo "    Status: {$jadwal->status}\n";
                    echo "    Mapel: " . ($jadwal->mapel->nama_mapel ?? 'N/A') . "\n";
                }
            } else {
                echo "Sesi Ruangan: NULL\n";
            }
            echo "---\n";
        }
    } else {
        echo "Tidak ada enrollment\n";
    }

    echo "\n=== SESI RUANGAN DENGAN TOKEN ODQRLJ ===\n";
    $sesiWithToken = SesiRuangan::where('token_ujian', 'ODQRLJ')->first();
    if ($sesiWithToken) {
        echo "ID: {$sesiWithToken->id}\n";
        echo "Nama: {$sesiWithToken->nama_sesi}\n";
        echo "Token: {$sesiWithToken->token_ujian}\n";
        echo "Status: {$sesiWithToken->status}\n";
        echo "Token Expired: " . ($sesiWithToken->token_expired_at ?? 'NULL') . "\n";
        echo "Waktu: {$sesiWithToken->waktu_mulai} - {$sesiWithToken->waktu_selesai}\n";

        // Cek apakah ada jadwal ujian
        $jadwals = $sesiWithToken->jadwalUjians;
        echo "Jadwal Ujians: " . $jadwals->count() . "\n";
        foreach ($jadwals as $jadwal) {
            echo "  - Jadwal ID: {$jadwal->id}, Judul: {$jadwal->judul}, Status: {$jadwal->status}\n";
        }
    } else {
        echo "Tidak ditemukan sesi dengan token ODQRLJ\n";
    }
} else {
    echo "Siswa 255092 tidak ditemukan\n";
}
