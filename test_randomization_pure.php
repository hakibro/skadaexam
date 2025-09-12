<?php

echo "=== PURE RANDOMIZATION TEST ===\n";

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

echo "\n=== TESTING CONTROLLER LOGIC ===\n";

// Simulate the controller randomization logic
$acak_jawaban = true; // This should be set to true in jadwal_ujian

if ($acak_jawaban) {
    echo "✅ acak_jawaban is TRUE - randomization will run\n";

    // Test with consistent seed (like in controller)
    $siswa_id = 5;
    $soal_id = 123;
    $seed = $siswa_id * 1000 + $soal_id;

    echo "Using seed: $seed (siswa_id: $siswa_id, soal_id: $soal_id)\n\n";

    // First shuffle
    mt_srand($seed);
    $keys = array_keys($options);
    shuffle($keys);

    $shuffledOptions = [];
    foreach ($keys as $i => $key) {
        $shuffledOptions[chr(65 + $i)] = $options[$key];
    }

    echo "After randomization (1st time):\n";
    foreach ($shuffledOptions as $key => $value) {
        echo "  $key: $value\n";
    }

    // Reset and shuffle again with same seed (should be identical)
    mt_srand($seed);
    $keys2 = array_keys($options);
    shuffle($keys2);

    $shuffledOptions2 = [];
    foreach ($keys2 as $i => $key) {
        $shuffledOptions2[chr(65 + $i)] = $options[$key];
    }

    echo "\nAfter randomization (2nd time - same seed):\n";
    foreach ($shuffledOptions2 as $key => $value) {
        echo "  $key: $value\n";
    }

    // Verify they're the same
    $same = json_encode($shuffledOptions) === json_encode($shuffledOptions2);
    echo "\nConsistency check: " . ($same ? 'PASS ✅' : 'FAIL ❌') . "\n";

    // Test with different seed (different student or question)
    $different_seed = ($siswa_id + 1) * 1000 + $soal_id;
    mt_srand($different_seed);
    $keys3 = array_keys($options);
    shuffle($keys3);

    $shuffledOptions3 = [];
    foreach ($keys3 as $i => $key) {
        $shuffledOptions3[chr(65 + $i)] = $options[$key];
    }

    echo "\nWith different seed ($different_seed):\n";
    foreach ($shuffledOptions3 as $key => $value) {
        echo "  $key: $value\n";
    }

    $different = json_encode($shuffledOptions) !== json_encode($shuffledOptions3);
    echo "\nDifference check: " . ($different ? 'PASS ✅' : 'FAIL ❌') . "\n";

    mt_srand(); // Reset seed

} else {
    echo "❌ acak_jawaban is FALSE - randomization will NOT run\n";
    echo "Options will remain in original order\n";
}

echo "\n=== DIAGNOSIS ===\n";
echo "If options are NOT randomized in the exam, check:\n";
echo "1. Database: jadwal_ujian.acak_jawaban should be TRUE\n";
echo "2. Controller: \$jadwalUjian->acak_jawaban should return true\n";
echo "3. Browser developer tools: check if randomization is applied\n";
echo "4. Clear cache after database changes\n";
