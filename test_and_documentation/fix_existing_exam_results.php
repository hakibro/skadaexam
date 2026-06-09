<?php

/**
 * Script untuk memperbarui hasil ujian yang sudah ada 
 * dengan perhitungan jawaban benar/salah yang sudah diperbaiki
 */

require_once 'vendor/autoload.php';

use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use App\Http\Controllers\Siswa\SiswaDashboardController;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATE HASIL UJIAN DENGAN PERHITUNGAN YANG DIPERBAIKI ===\n\n";

// Temukan semua hasil ujian dengan acak_jawaban enabled
$hasilUjianList = HasilUjian::with(['jadwalUjian', 'siswa'])
    ->whereHas('jadwalUjian', function ($query) {
        $query->where('acak_jawaban', true);
    })
    ->where('is_final', true)
    ->orderBy('created_at', 'desc')
    ->get();

if ($hasilUjianList->count() === 0) {
    echo "ℹ️  Tidak ada hasil ujian dengan acak_jawaban yang perlu diupdate\n";
    exit(0);
}

echo "📊 Ditemukan {$hasilUjianList->count()} hasil ujian dengan acak_jawaban\n";
echo "🔄 Memulai proses update...\n\n";

$controller = new SiswaDashboardController();
$reflection = new ReflectionClass($controller);
$calculateScoreMethod = $reflection->getMethod('calculateScore');
$calculateScoreMethod->setAccessible(true);

$updatedCount = 0;
$unchangedCount = 0;
$errorCount = 0;

foreach ($hasilUjianList as $hasilUjian) {
    try {
        echo "🔍 Processing Hasil Ujian ID: {$hasilUjian->id}\n";
        echo "   Siswa: {$hasilUjian->siswa->nama}\n";
        echo "   Jadwal: {$hasilUjian->jadwalUjian->kode_ujian}\n";

        // Simpan nilai lama
        $oldBenar = $hasilUjian->jumlah_benar;
        $oldSalah = $hasilUjian->jumlah_salah;
        $oldNilai = $hasilUjian->nilai;

        // Hitung ulang dengan method yang sudah diperbaiki
        $newScore = $calculateScoreMethod->invoke($controller, $hasilUjian);

        echo "   📊 Lama: {$oldBenar} benar, {$oldSalah} salah, nilai: " . round($oldNilai, 2) . "%\n";
        echo "   📊 Baru: {$newScore['jumlah_benar']} benar, {$newScore['jumlah_salah']} salah, nilai: " . round($newScore['persentase'], 2) . "%\n";

        // Cek apakah ada perubahan
        if ($oldBenar != $newScore['jumlah_benar'] || $oldSalah != $newScore['jumlah_salah']) {
            // Update database dengan nilai yang benar
            $hasilUjian->update([
                'jumlah_benar' => $newScore['jumlah_benar'],
                'jumlah_salah' => $newScore['jumlah_salah'],
                'jumlah_dijawab' => $newScore['jumlah_dijawab'],
                'jumlah_tidak_dijawab' => $newScore['jumlah_tidak_dijawab'],
                'skor' => $newScore['total_skor'],
                'nilai' => $newScore['persentase'],
            ]);

            echo "   ✅ UPDATED! Selisih: " . ($newScore['jumlah_benar'] - $oldBenar) . " benar, " .
                ($newScore['jumlah_salah'] - $oldSalah) . " salah\n";
            $updatedCount++;
        } else {
            echo "   ➡️  No change needed\n";
            $unchangedCount++;
        }

        echo "\n";
    } catch (\Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n\n";
        $errorCount++;
    }
}

echo str_repeat("=", 60) . "\n";
echo "📊 SUMMARY:\n";
echo "   ✅ Updated: {$updatedCount} hasil ujian\n";
echo "   ➡️  Unchanged: {$unchangedCount} hasil ujian\n";
echo "   ❌ Errors: {$errorCount} hasil ujian\n";
echo "   📁 Total processed: " . ($updatedCount + $unchangedCount + $errorCount) . " hasil ujian\n\n";

if ($updatedCount > 0) {
    echo "🎉 BERHASIL!\n";
    echo "   {$updatedCount} hasil ujian telah diperbaiki dengan perhitungan yang benar\n";
    echo "   Siswa yang jawaban benarnya sebelumnya salah hitung sekarang sudah diperbaiki\n\n";

    echo "💡 CATATAN:\n";
    echo "   - Sistem sekarang menggunakan perhitungan kunci jawaban yang sudah diacak\n";
    echo "   - Ujian baru otomatis menggunakan perhitungan yang benar\n";
    echo "   - Perubahan ini hanya mempengaruhi ujian dengan acak_jawaban = true\n";
} else {
    echo "ℹ️  Tidak ada hasil ujian yang perlu diperbaiki\n";
}

echo "\n🔧 TECHNICAL DETAILS:\n";
echo "   - Fixed: getCorrectAnswerForStudent() method considers randomization\n";
echo "   - Fixed: saveAnswer() and calculateScore() use proper validation\n";
echo "   - Impact: Only affects exams with acak_jawaban = true\n";
