<?php

require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\BankSoal;

echo "=== Checking Bank Soal Records ===\n";

$count = BankSoal::count();
echo "Bank Soal records: $count\n";

if ($count > 0) {
    $first = BankSoal::first();
    echo "First Bank Soal ID: " . $first->id . "\n";
    echo "First Bank Soal Name: " . $first->nama . "\n";
} else {
    echo "No Bank Soal records found. Creating one...\n";

    $bankSoal = BankSoal::create([
        'nama' => 'Test Bank Soal',
        'deskripsi' => 'Test bank soal for debugging',
        'mapel_id' => 1,
        'kelas_id' => 1,
        'guru_id' => 1,
        'semester' => 1,
        'tahun_ajaran' => '2024/2025',
        'jumlah_soal' => 0
    ]);

    echo "Created Bank Soal with ID: " . $bankSoal->id . "\n";
}

echo "\n=== Test Complete ===\n";
