<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING EXAM-QUESTION RELATIONSHIP ===\n\n";

// Get jadwal ujian with ID 37 (Aswaja)
$jadwalUjian = DB::table('jadwal_ujian')->where('id', 37)->first();
if ($jadwalUjian) {
    echo "📚 Jadwal Ujian: {$jadwalUjian->judul}\n";
    echo "   Bank Soal ID: {$jadwalUjian->bank_soal_id}\n";
    echo "   Jumlah Soal yang diinginkan: {$jadwalUjian->jumlah_soal}\n";

    // Get questions from bank_soal
    $soals = DB::table('soal')
        ->where('bank_soal_id', $jadwalUjian->bank_soal_id)
        ->where('status', 'aktif')
        ->get();

    echo "   Soal tersedia di bank: {$soals->count()}\n";

    if ($soals->count() > 0) {
        echo "\n   Sample questions:\n";
        foreach ($soals->take(3) as $soal) {
            $pertanyaan = substr($soal->pertanyaan, 0, 60) . '...';
            echo "   - ID {$soal->id}: {$pertanyaan}\n";
        }
    }
} else {
    echo "❌ Jadwal ujian not found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
