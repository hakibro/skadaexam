<?php

// Script untuk memperbaiki data hasil_ujian yang bermasalah
// Simpan file ini di folder public untuk akses mudah

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Auth Check - Only allow admin
if (!auth()->check() || !auth()->user()->hasRole('admin')) {
    echo "Akses ditolak! Hanya admin yang boleh mengakses halaman ini.";
    exit;
}

echo "<h1>Memperbaiki Data Hasil Ujian</h1>";
echo "<p>Memproses data hasil ujian yang memiliki nilai null atau 0 di kolom penting...</p>";

// Get all hasil_ujian with missing data
$hasilUjians = App\Models\HasilUjian::where(function ($query) {
    $query->whereNull('sesi_ruangan_id')
        ->orWhereNull('durasi_menit')
        ->orWhere('jumlah_soal', 0)
        ->orWhere('is_final', 0)
        ->where('status', 'selesai');
})
    ->get();

echo "<p>Ditemukan {$hasilUjians->count()} record hasil ujian untuk diperbaiki</p>";
echo "<ul>";

foreach ($hasilUjians as $hasil) {
    echo "<li>Memproses hasil ujian ID: {$hasil->id} (Siswa ID: {$hasil->siswa_id})</li>";

    try {
        // Get enrollment to find sesi_ruangan_id
        $enrollment = $hasil->enrollment;
        $sesiRuanganId = $enrollment ? $enrollment->sesi_ruangan_id : null;

        // Calculate durasi_menit if we have waktu_mulai and waktu_selesai
        $durasiMenit = null;
        if ($hasil->waktu_mulai && $hasil->waktu_selesai) {
            $durasiMenit = $hasil->waktu_selesai->diffInMinutes($hasil->waktu_mulai);
        } else if ($hasil->jadwalUjian) {
            $durasiMenit = $hasil->jadwalUjian->durasi_menit;
        }

        // Count jumlah_soal from bank_soal if available
        $jumlahSoal = 0;
        if ($hasil->jadwalUjian && $hasil->jadwalUjian->bank_soal_id) {
            $jumlahSoal = App\Models\SoalUjian::where('bank_soal_id', $hasil->jadwalUjian->bank_soal_id)
                ->where('status', 'aktif')
                ->count();
        }

        // Count jumlah_dijawab
        $jumlahDijawab = App\Models\JawabanSiswa::where('hasil_ujian_id', $hasil->id)
            ->whereNotNull('jawaban')
            ->count();

        // Count jumlah_benar and jumlah_salah
        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $jawabanList = App\Models\JawabanSiswa::with('soalUjian')
            ->where('hasil_ujian_id', $hasil->id)
            ->whereNotNull('jawaban')
            ->get();

        foreach ($jawabanList as $jwb) {
            if ($jwb->soalUjian && $jwb->jawaban === $jwb->soalUjian->kunci_jawaban) {
                $jumlahBenar++;
            } else {
                $jumlahSalah++;
            }
        }

        // Calculate nilai (percentage) and lulus status
        $nilai = $jumlahSoal > 0 ? ($jumlahBenar / $jumlahSoal) * 100 : 0;

        // Determine KKM from jadwal_ujian settings or use 75 as default
        $kkm = 75;
        if ($hasil->jadwalUjian && isset($hasil->jadwalUjian->pengaturan['kkm'])) {
            $kkm = $hasil->jadwalUjian->pengaturan['kkm'];
        }

        $lulus = $nilai >= $kkm;

        // Update hasil ujian
        $updateData = [
            'is_final' => true,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'nilai' => $nilai,
            'lulus' => $lulus
        ];

        if ($sesiRuanganId) {
            $updateData['sesi_ruangan_id'] = $sesiRuanganId;
        }

        if ($durasiMenit) {
            $updateData['durasi_menit'] = $durasiMenit;
        }

        if ($jumlahSoal > 0) {
            $updateData['jumlah_soal'] = $jumlahSoal;
            $updateData['jumlah_tidak_dijawab'] = $jumlahSoal - $jumlahDijawab;
        }

        if ($jumlahDijawab > 0) {
            $updateData['jumlah_dijawab'] = $jumlahDijawab;
        }

        $hasil->update($updateData);

        echo "<li style='color:green'>Berhasil memperbaiki hasil ujian ID: {$hasil->id}</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>Error memperbaiki hasil ujian ID: {$hasil->id} - {$e->getMessage()}</li>";
        Illuminate\Support\Facades\Log::error("Error fixing hasil ujian ID: {$hasil->id}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

echo "</ul>";
echo "<p>Selesai memperbaiki data hasil ujian!</p>";
echo "<p><a href='/admin/dashboard'>Kembali ke Dashboard</a></p>";
