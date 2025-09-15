<?php

require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreSoalRequest;
use App\Http\Controllers\SoalController;
use Illuminate\Foundation\Http\FormRequest;

echo "=== Testing Form Submission with Image ===\n";

// Create a test image file
$testImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$tempFilePath = sys_get_temp_dir() . '/test_image_' . time() . '.png';
file_put_contents($tempFilePath, $testImageContent);

echo "Created temporary test image: $tempFilePath\n";

// Create an UploadedFile instance
$uploadedFile = new UploadedFile(
    $tempFilePath, // path
    'test_image.png', // original name
    'image/png', // mime type
    null, // error (null means no error)
    true // test mode
);

echo "Created UploadedFile instance\n";
echo "File size: " . $uploadedFile->getSize() . " bytes\n";
echo "File mime type: " . $uploadedFile->getMimeType() . "\n";
echo "File is valid: " . ($uploadedFile->isValid() ? 'Yes' : 'No') . "\n";

// Simulate form data
$formData = [
    'bank_soal_id' => 1,
    'nomor_soal' => 100,
    'tipe_soal' => 'pilihan_ganda',
    'tipe_pertanyaan' => 'teks_gambar',
    'pertanyaan' => 'Test question with image',
    'pilihan_a_tipe' => 'teks',
    'pilihan_a_teks' => 'Option A',
    'pilihan_b_tipe' => 'teks',
    'pilihan_b_teks' => 'Option B',
    'pilihan_c_tipe' => 'teks',
    'pilihan_c_teks' => 'Option C',
    'pilihan_d_tipe' => 'teks',
    'pilihan_d_teks' => 'Option D',
    'kunci_jawaban' => 'A',
    'pembahasan_tipe' => 'teks',
    'pembahasan_teks' => 'Test explanation',
    'kategori' => 'test'
];

$fileData = [
    'gambar_pertanyaan' => $uploadedFile
];

echo "\n=== Creating Mock Request ===\n";

// Create a mock request
$request = new Illuminate\Http\Request();
$request->replace($formData);
$request->files->replace($fileData);

echo "Request data set\n";
echo "Request has gambar_soal file: " . ($request->hasFile('gambar_soal') ? 'Yes' : 'No') . "\n";

try {
    echo "\n=== Testing Validation ===\n";

    // Test validation manually
    $validator = \Illuminate\Support\Facades\Validator::make(
        array_merge($formData, ['gambar_pertanyaan' => $uploadedFile]),
        [
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'nomor_soal' => 'required|integer',
            'tipe_soal' => 'required|string',
            'tipe_pertanyaan' => 'required|in:teks,gambar,teks_gambar',
            'pertanyaan' => 'nullable|string',
            'gambar_pertanyaan' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pilihan_a_tipe' => 'required|in:teks,gambar',
            'pilihan_a_teks' => 'nullable|string',
            'pilihan_b_tipe' => 'required|in:teks,gambar',
            'pilihan_b_teks' => 'nullable|string',
            'pilihan_c_tipe' => 'required|in:teks,gambar',
            'pilihan_c_teks' => 'nullable|string',
            'pilihan_d_tipe' => 'required|in:teks,gambar',
            'pilihan_d_teks' => 'nullable|string',
            'kunci_jawaban' => 'required|string',
            'pembahasan_tipe' => 'required|in:teks,gambar,teks_gambar',
            'pembahasan_teks' => 'nullable|string',
            'kategori' => 'required|string'
        ]
    );

    if ($validator->fails()) {
        echo "Validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "- $error\n";
        }
    } else {
        echo "Validation passed!\n";

        echo "\n=== Testing Image Service ===\n";

        // Test storage directly first
        $fileName = 'test_' . time() . '.png';
        $storagePath = 'soal/pertanyaan/' . $fileName;

        Storage::disk('public')->put($storagePath, $testImageContent);
        echo "Image stored directly to: $storagePath\n";

        if (Storage::disk('public')->exists($storagePath)) {
            $formData['gambar_pertanyaan'] = $fileName;

            echo "\n=== Creating Soal Record ===\n";

            $soal = \App\Models\Soal::create($formData);

            echo "Soal created successfully with ID: " . $soal->id . "\n";
            echo "Soal gambar_pertanyaan field: " . $soal->gambar_pertanyaan . "\n";

            // Verify file exists
            $fullImagePath = storage_path('app/public/' . $storagePath);
            echo "Full image path: $fullImagePath\n";
            echo "Image file exists: " . (file_exists($fullImagePath) ? 'Yes' : 'No') . "\n";
        } else {
            echo "Failed to upload image\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Cleanup
if (file_exists($tempFilePath)) {
    unlink($tempFilePath);
    echo "\nTemporary file cleaned up\n";
}

echo "\n=== Test Complete ===\n";
