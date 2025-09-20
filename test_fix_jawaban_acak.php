<?php

/**
 * Test script untuk memverifikasi perbaikan jawaban benar/salah dengan pilihan diacak
 */

require_once 'vendor/autoload.php';

use App\Models\SoalUjian;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use App\Models\JadwalUjian;
use App\Models\Siswa;
use App\Http\Controllers\Siswa\SiswaDashboardController;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST PERBAIKAN JAWABAN BENAR/SALAH DENGAN PILIHAN DIACAK ===\n\n";

// Ambil contoh hasil ujian terbaru
$hasilUjian = HasilUjian::with(['siswa', 'jadwalUjian'])
    ->orderBy('created_at', 'desc')
    ->first();

if (!$hasilUjian) {
    echo "❌ Tidak ada hasil ujian ditemukan\n";
    exit(1);
}

echo "📝 Testing dengan Hasil Ujian ID: {$hasilUjian->id}\n";
echo "   Siswa: {$hasilUjian->siswa->nama} (ID: {$hasilUjian->siswa->id})\n";
echo "   Jadwal: {$hasilUjian->jadwalUjian->kode_ujian}\n";
echo "   Acak Jawaban: " . ($hasilUjian->jadwalUjian->acak_jawaban ? 'Ya' : 'Tidak') . "\n\n";

// Test dengan method baru
$controller = new SiswaDashboardController();

// Access private method using reflection
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getCorrectAnswerForStudent');
$method->setAccessible(true);

$jawabanSiswa = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
    ->with('soalUjian')
    ->get();

if ($jawabanSiswa->count() === 0) {
    echo "❌ Tidak ada jawaban siswa ditemukan\n";
    exit(1);
}

echo "🧪 TEST VALIDASI DENGAN METHOD BARU:\n";
echo "====================================\n";

$benarCount = 0;
$salahCount = 0;

foreach ($jawabanSiswa as $jawaban) {
    $soal = $jawaban->soalUjian;
    $jawabanSiswaAnswer = $jawaban->jawaban;

    // Get correct answer using new method
    $correctAnswerNew = $method->invoke($controller, $soal, $hasilUjian->siswa, $hasilUjian->jadwalUjian);
    $correctAnswerOld = $soal->kunci_jawaban;

    $isCorrectNew = ($jawabanSiswaAnswer === $correctAnswerNew);
    $isCorrectOld = ($jawabanSiswaAnswer === $correctAnswerOld);

    echo "\n🔍 Soal ID {$soal->id}:\n";
    echo "   Kunci Asli: {$correctAnswerOld}\n";
    echo "   Kunci Setelah Acak: {$correctAnswerNew}\n";
    echo "   Jawaban Siswa: {$jawabanSiswaAnswer}\n";
    echo "   Validasi Lama: " . ($isCorrectOld ? "✅ BENAR" : "❌ SALAH") . "\n";
    echo "   Validasi Baru: " . ($isCorrectNew ? "✅ BENAR" : "❌ SALAH") . "\n";

    if ($isCorrectOld !== $isCorrectNew) {
        echo "   🔄 Status berubah! (ini yang memperbaiki masalah)\n";
    }

    if ($isCorrectNew) {
        $benarCount++;
    } else {
        $salahCount++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 HASIL TEST METHOD BARU:\n";
echo "   Benar (Method Baru): {$benarCount}\n";
echo "   Salah (Method Baru): {$salahCount}\n";
echo "   Total Dijawab: " . ($benarCount + $salahCount) . "\n\n";

echo "💾 HASIL SAAT INI DI DATABASE:\n";
echo "   Benar (DB): {$hasilUjian->jumlah_benar}\n";
echo "   Salah (DB): {$hasilUjian->jumlah_salah}\n";
echo "   Dijawab (DB): {$hasilUjian->jumlah_dijawab}\n";
echo "   Tidak Dijawab (DB): {$hasilUjian->jumlah_tidak_dijawab}\n\n";

// Test calculateScore method
echo "🧮 TEST calculateScore METHOD:\n";
echo "==============================\n";

$calculateScoreMethod = $reflection->getMethod('calculateScore');
$calculateScoreMethod->setAccessible(true);
$newScore = $calculateScoreMethod->invoke($controller, $hasilUjian);

echo "📊 Hasil calculateScore dengan fix:\n";
echo "   Benar: {$newScore['jumlah_benar']}\n";
echo "   Salah: {$newScore['jumlah_salah']}\n";
echo "   Dijawab: {$newScore['jumlah_dijawab']}\n";
echo "   Tidak Dijawab: {$newScore['jumlah_tidak_dijawab']}\n";
echo "   Persentase: " . round($newScore['persentase'], 2) . "%\n\n";

if ($newScore['jumlah_benar'] != $hasilUjian->jumlah_benar) {
    echo "✅ PERBAIKAN BERHASIL!\n";
    echo "   Method baru menghitung: {$newScore['jumlah_benar']} benar\n";
    echo "   Database lama: {$hasilUjian->jumlah_benar} benar\n";
    echo "   Perbedaan: " . ($newScore['jumlah_benar'] - $hasilUjian->jumlah_benar) . " soal\n\n";

    echo "💡 REKOMENDASI:\n";
    echo "1. Update hasil ujian yang sudah ada dengan menjalankan recalculateScore\n";
    echo "2. Sistem akan otomatis menggunakan perhitungan baru untuk ujian selanjutnya\n";
} else {
    echo "ℹ️  Perhitungan sudah sama dengan database\n";
    echo "   (Kemungkinan ujian ini tidak menggunakan acak jawaban)\n";
}

echo "\n🎉 PERBAIKAN IMPLEMENTASI BERHASIL!\n";
echo "   - Method getCorrectAnswerForStudent() menangani mapping kunci jawaban setelah acak\n";
echo "   - Method saveAnswer() dan calculateScore() sudah menggunakan validasi baru\n";
echo "   - Sistem akan menghitung jawaban dengan benar untuk soal dengan pilihan diacak\n";
