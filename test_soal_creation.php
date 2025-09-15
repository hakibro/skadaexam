<?php

require_once 'vendor/autoload.php';

use App\Models\BankSoal;
use App\Models\Soal;
use App\Services\SoalImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Soal Creation with Images ===\n";

// Get first bank soal
$bankSoal = BankSoal::first();
if (!$bankSoal) {
    echo "✗ No bank soal found!\n";
    exit(1);
}

echo "Using Bank Soal: {$bankSoal->judul} (ID: {$bankSoal->id})\n";

// Create test image service
$imageService = new SoalImageService();

// Create test images
echo "\nCreating test images...\n";
$testPertanyaan = $imageService->createTestImage('pertanyaan');
$testPilihan = $imageService->createTestImage('pilihan');
$testPembahasan = $imageService->createTestImage('pembahasan');

if (!$testPertanyaan || !$testPilihan || !$testPembahasan) {
    echo "✗ Failed to create test images!\n";
    exit(1);
}

echo "✓ Test images created:\n";
echo "  - Pertanyaan: $testPertanyaan\n";
echo "  - Pilihan: $testPilihan\n";
echo "  - Pembahasan: $testPembahasan\n";

// Test 1: Create soal with text only (should work)
echo "\n=== Test 1: Text-only question ===\n";
try {
    $soalText = Soal::create([
        'bank_soal_id' => $bankSoal->id,
        'nomor_soal' => 99,
        'pertanyaan' => 'Ini adalah pertanyaan teks saja',
        'tipe_pertanyaan' => 'teks',
        'tipe_soal' => 'pilihan_ganda',
        'pilihan_a_teks' => 'Pilihan A',
        'pilihan_a_tipe' => 'teks',
        'pilihan_b_teks' => 'Pilihan B',
        'pilihan_b_tipe' => 'teks',
        'pilihan_c_teks' => 'Pilihan C',
        'pilihan_c_tipe' => 'teks',
        'pilihan_d_teks' => 'Pilihan D',
        'pilihan_d_tipe' => 'teks',
        'kunci_jawaban' => 'A',
        'pembahasan_teks' => 'Pembahasan soal ini',
        'pembahasan_tipe' => 'teks',
        'kategori' => 'umum',
        'bobot' => 1.0,
        'display_settings' => []
    ]);

    echo "✓ Text-only question created successfully (ID: {$soalText->id})\n";

    // Delete the test question
    $soalText->delete();
    echo "✓ Test question deleted\n";
} catch (Exception $e) {
    echo "✗ Failed to create text-only question: " . $e->getMessage() . "\n";
}

// Test 2: Create soal with images using the actual filenames
echo "\n=== Test 2: Question with images ===\n";
try {
    $soalImage = Soal::create([
        'bank_soal_id' => $bankSoal->id,
        'nomor_soal' => 98,
        'pertanyaan' => 'Ini adalah pertanyaan dengan gambar',
        'gambar_pertanyaan' => $testPertanyaan,
        'tipe_pertanyaan' => 'teks_gambar',
        'tipe_soal' => 'pilihan_ganda',
        'pilihan_a_teks' => 'Pilihan A dengan gambar',
        'pilihan_a_gambar' => $testPilihan,
        'pilihan_a_tipe' => 'teks_gambar', // This should be 'gambar' or 'teks', not 'teks_gambar'
        'pilihan_b_teks' => 'Pilihan B',
        'pilihan_b_tipe' => 'teks',
        'pilihan_c_teks' => 'Pilihan C',
        'pilihan_c_tipe' => 'teks',
        'pilihan_d_teks' => 'Pilihan D',
        'pilihan_d_tipe' => 'teks',
        'kunci_jawaban' => 'A',
        'pembahasan_teks' => 'Pembahasan dengan gambar',
        'pembahasan_gambar' => $testPembahasan,
        'pembahasan_tipe' => 'teks_gambar',
        'kategori' => 'umum',
        'bobot' => 1.0,
        'display_settings' => []
    ]);

    echo "✓ Question with images created successfully (ID: {$soalImage->id})\n";

    // Verify the images are accessible
    echo "Verifying image URLs:\n";
    echo "  - Pertanyaan: " . ($soalImage->gambar_pertanyaan_url ?: 'NULL') . "\n";
    echo "  - Pilihan A: " . ($soalImage->pilihan_a_gambar_url ?: 'NULL') . "\n";
    echo "  - Pembahasan: " . ($soalImage->pembahasan_gambar_url ?: 'NULL') . "\n";

    // Delete the test question
    $soalImage->delete();
    echo "✓ Test question deleted\n";
} catch (Exception $e) {
    echo "✗ Failed to create question with images: " . $e->getMessage() . "\n";
    echo "Error trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Tests completed ===\n";
