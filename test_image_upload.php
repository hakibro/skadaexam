<?php

require_once 'vendor/autoload.php';

use App\Models\BankSoal;
use App\Services\SoalImageService;
use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test script untuk menguji upload gambar
echo "=== Testing Image Upload Functionality ===\n";

// Check if storage directories exist
$directories = [
    'storage/app/public/soal',
    'storage/app/public/soal/pertanyaan',
    'storage/app/public/soal/pilihan',
    'storage/app/public/soal/pembahasan'
];

echo "\nChecking storage directories:\n";
foreach ($directories as $dir) {
    $path = base_path($dir);
    if (!is_dir($path)) {
        echo "Creating directory: $dir\n";
        mkdir($path, 0755, true);
    } else {
        echo "✓ Directory exists: $dir\n";
    }
}

// Test SoalImageService
$imageService = new SoalImageService();

echo "\nTesting SoalImageService test image creation:\n";
try {
    $testImage = $imageService->createTestImage('pertanyaan');
    if ($testImage) {
        echo "✓ Test image created successfully: $testImage\n";

        // Check if file actually exists
        $filePath = storage_path('app/public/soal/pertanyaan/' . $testImage);
        if (file_exists($filePath)) {
            echo "✓ File exists at: $filePath\n";
            echo "  File size: " . filesize($filePath) . " bytes\n";
        } else {
            echo "✗ File not found at: $filePath\n";
        }
    } else {
        echo "✗ Failed to create test image\n";
    }
} catch (Exception $e) {
    echo "✗ Error creating test image: " . $e->getMessage() . "\n";
}

echo "\nChecking Bank Soal:\n";
$bankSoal = BankSoal::first();
if ($bankSoal) {
    echo "✓ Found Bank Soal: {$bankSoal->judul} (ID: {$bankSoal->id})\n";
} else {
    echo "✗ No Bank Soal found\n";
}

echo "\n=== Test completed ===\n";
