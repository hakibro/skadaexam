<?php

/**
 * Debug script untuk menganalisis masalah jawaban benar/salah saat pilihan diacak
 */

require_once 'vendor/autoload.php';

use App\Models\SoalUjian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\JadwalUjian;
use App\Models\Siswa;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG JAWABAN BENAR/SALAH DENGAN PILIHAN DIACAK ===\n\n";

// Ambil contoh hasil ujian terbaru
$hasilUjian = HasilUjian::with(['siswa', 'jadwalUjian'])
    ->orderBy('created_at', 'desc')
    ->first();

if (!$hasilUjian) {
    echo "❌ Tidak ada hasil ujian ditemukan\n";
    exit(1);
}

echo "📝 Analyzing Hasil Ujian ID: {$hasilUjian->id}\n";
echo "   Siswa: {$hasilUjian->siswa->nama} (ID: {$hasilUjian->siswa->id})\n";
echo "   Jadwal: {$hasilUjian->jadwalUjian->kode_ujian}\n";
echo "   Acak Soal: " . ($hasilUjian->jadwalUjian->acak_soal ? 'Ya' : 'Tidak') . "\n";
echo "   Acak Jawaban: " . ($hasilUjian->jadwalUjian->acak_jawaban ? 'Ya' : 'Tidak') . "\n\n";

// Ambil jawaban siswa
$jawabanSiswa = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
    ->with('soalUjian')
    ->get();

if ($jawabanSiswa->count() === 0) {
    echo "❌ Tidak ada jawaban siswa ditemukan\n";
    exit(1);
}

echo "📊 Analisis Jawaban:\n";
echo "====================\n";

$benarCount = 0;
$salahCount = 0;
$problemSoals = [];

foreach ($jawabanSiswa as $jawaban) {
    $soal = $jawaban->soalUjian;
    $jawabanSiswa = $jawaban->jawaban;
    $kunciJawaban = $soal->kunci_jawaban;

    echo "\n🔍 Soal ID {$soal->id}:\n";
    echo "   Kunci Jawaban Asli: {$kunciJawaban}\n";
    echo "   Jawaban Siswa: {$jawabanSiswa}\n";

    // Simulasi pengacakan pilihan seperti di controller
    $siswa = $hasilUjian->siswa;
    $jadwalUjian = $hasilUjian->jadwalUjian;

    if ($jadwalUjian->acak_jawaban) {
        echo "   🔄 Pilihan jawaban diacak!\n";

        // Buat options asli
        $options = [];
        $options['A'] = $soal->pilihan_a_teks;
        $options['B'] = $soal->pilihan_b_teks;
        $options['C'] = $soal->pilihan_c_teks;
        $options['D'] = $soal->pilihan_d_teks;
        if ($soal->pilihan_e_teks) {
            $options['E'] = $soal->pilihan_e_teks;
        }

        echo "   📝 Options Asli:\n";
        foreach ($options as $key => $value) {
            $marker = ($key === $kunciJawaban) ? " ← KUNCI" : "";
            echo "      {$key}: " . substr($value, 0, 50) . "...{$marker}\n";
        }

        // Simulasi pengacakan dengan seed yang sama
        $seed = $siswa->id * 1000 + $soal->id;
        mt_srand($seed);

        $keys = array_keys($options);
        shuffle($keys);
        $shuffledOptions = [];
        $kunciBaruMapping = null;

        foreach ($keys as $i => $key) {
            $newKey = chr(65 + $i); // A, B, C, D, E
            $shuffledOptions[$newKey] = $options[$key];

            // Cari mapping kunci jawaban baru
            if ($key === $kunciJawaban) {
                $kunciBaruMapping = $newKey;
            }
        }

        mt_srand(); // Reset seed

        echo "   🔀 Options Setelah Diacak:\n";
        foreach ($shuffledOptions as $key => $value) {
            $marker = ($key === $kunciBaruMapping) ? " ← KUNCI BARU" : "";
            echo "      {$key}: " . substr($value, 0, 50) . "...{$marker}\n";
        }

        echo "   ⚡ Kunci Jawaban: {$kunciJawaban} → {$kunciBaruMapping}\n";

        // Cek apakah jawaban benar
        $isCorrect = ($jawabanSiswa === $kunciBaruMapping);
        echo "   📊 Jawaban " . ($isCorrect ? "✅ BENAR" : "❌ SALAH") . "\n";
        echo "      (Siswa jawab: {$jawabanSiswa}, Seharusnya: {$kunciBaruMapping})\n";

        if ($isCorrect) {
            $benarCount++;
        } else {
            $salahCount++;
            $problemSoals[] = [
                'soal_id' => $soal->id,
                'kunci_asli' => $kunciJawaban,
                'kunci_baru' => $kunciBaruMapping,
                'jawaban_siswa' => $jawabanSiswa,
                'expected' => $kunciBaruMapping
            ];
        }
    } else {
        // Tidak diacak, langsung compare
        $isCorrect = ($jawabanSiswa === $kunciJawaban);
        echo "   📊 Jawaban " . ($isCorrect ? "✅ BENAR" : "❌ SALAH") . "\n";

        if ($isCorrect) {
            $benarCount++;
        } else {
            $salahCount++;
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 HASIL ANALISIS:\n";
echo "   Benar (Simulasi): {$benarCount}\n";
echo "   Salah (Simulasi): {$salahCount}\n";
echo "   Total Dijawab: " . ($benarCount + $salahCount) . "\n\n";

echo "💾 HASIL DI DATABASE:\n";
echo "   Benar (DB): {$hasilUjian->jumlah_benar}\n";
echo "   Salah (DB): {$hasilUjian->jumlah_salah}\n";
echo "   Dijawab (DB): {$hasilUjian->jumlah_dijawab}\n";
echo "   Tidak Dijawab (DB): {$hasilUjian->jumlah_tidak_dijawab}\n\n";

if ($benarCount != $hasilUjian->jumlah_benar || $salahCount != $hasilUjian->jumlah_salah) {
    echo "🚨 MASALAH TERDETEKSI!\n";
    echo "   Simulasi dan database tidak cocok!\n";
    echo "   Kemungkinan: Sistem tidak menghitung kunci jawaban yang sudah diacak\n\n";

    if (!empty($problemSoals)) {
        echo "🔍 SOAL BERMASALAH:\n";
        foreach ($problemSoals as $problem) {
            echo "   Soal {$problem['soal_id']}: Kunci {$problem['kunci_asli']}→{$problem['kunci_baru']}, Siswa: {$problem['jawaban_siswa']}\n";
        }
    }
} else {
    echo "✅ Perhitungan sudah benar!\n";
}

echo "\n🔧 SOLUSI:\n";
echo "1. Simpan mapping kunci jawaban setelah pengacakan\n";
echo "2. Gunakan kunci jawaban yang sudah diacak untuk validasi\n";
echo "3. Atau simpan jawaban dalam format yang konsisten\n";
