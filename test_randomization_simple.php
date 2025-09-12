<?php

require 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';

use App\Models\JadwalUjian;

try {
    // Check jadwal ujian settings
    $jadwalUjian = JadwalUjian::first();

    if ($jadwalUjian) {
        echo "=== JADWAL UJIAN SETTINGS ===\n";
        echo "JU ID: " . $jadwalUjian->id . "\n";
        echo "acak_jawaban: " . ($jadwalUjian->acak_jawaban ? 'true' : 'false') . "\n";
        echo "acak_soal: " . ($jadwalUjian->acak_soal ? 'true' : 'false') . "\n";
    } else {
        echo "No jadwal ujian found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== RANDOMIZATION TEST (Simulated) ===\n";

$options = [
    'A' => 'Jawaban A - Pilihan Pertama',
    'B' => 'Jawaban B - Pilihan Kedua',
    'C' => 'Jawaban C - Pilihan Ketiga',
    'D' => 'Jawaban D - Pilihan Keempat'
];

echo "Original options:\n";
foreach ($options as $key => $value) {
    echo "  $key: $value\n";
}

echo "\nWith randomization (acak_jawaban = true):\n";

// Test with consistent seed
$siswa_id = 5;
$soal_id = 123;
$seed = $siswa_id * 1000 + $soal_id;

echo "Using seed: $seed (siswa_id: $siswa_id, soal_id: $soal_id)\n";

// First shuffle
mt_srand($seed);
$keys = array_keys($options);
shuffle($keys);

$shuffledOptions = [];
foreach ($keys as $i => $key) {
    $shuffledOptions[chr(65 + $i)] = $options[$key];
}

echo "First shuffle:\n";
foreach ($shuffledOptions as $key => $value) {
    echo "  $key: $value\n";
}

// Second shuffle with same seed (should be identical)
mt_srand($seed);
$keys2 = array_keys($options);
shuffle($keys2);

$shuffledOptions2 = [];
foreach ($keys2 as $i => $key) {
    $shuffledOptions2[chr(65 + $i)] = $options[$key];
}

echo "\nSecond shuffle (same seed - should be identical):\n";
foreach ($shuffledOptions2 as $key => $value) {
    echo "  $key: $value\n";
}

// Verify they're the same
$same = json_encode($shuffledOptions) === json_encode($shuffledOptions2);
echo "\nResults are identical: " . ($same ? 'YES ✅' : 'NO ❌') . "\n";

mt_srand(); // Reset seed
