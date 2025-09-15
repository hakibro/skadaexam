<?php

require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Features\Naskah\SoalController;

echo "=== Testing Complete SoalController Flow ===\n";

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

try {
    // Simulate complete form data for SoalController
    $formData = [
        'bank_soal_id' => 1,
        'nomor_soal' => 101,
        'tipe_soal' => 'pilihan_ganda',
        'tipe_pertanyaan' => 'teks_gambar',
        'pertanyaan' => 'Test question with image through controller',
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
        'pembahasan_teks' => 'Test explanation through controller',
        'kategori' => 'test'
    ];

    $fileData = [
        'gambar_pertanyaan' => $uploadedFile
    ];

    echo "\n=== Creating Real HTTP Request ===\n";

    // Create a proper Laravel request
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->request->replace($formData);
    $request->files->replace($fileData);

    // Add CSRF token (mock)
    $request->headers->set('X-CSRF-TOKEN', 'mock-token');

    echo "Request prepared with all form data\n";
    echo "Request has gambar_pertanyaan file: " . ($request->hasFile('gambar_pertanyaan') ? 'Yes' : 'No') . "\n";

    echo "\n=== Testing Controller Store Method ===\n";

    // Initialize controller
    $controller = new SoalController();

    // Call store method - note: we're bypassing middleware in this test
    // In a real request, this would be validated by StoreSoalRequest
    echo "Calling controller store method...\n";

    // This will fail because we bypass request validation, but let's see what happens
    $response = $controller->store($request);

    echo "Controller method executed successfully!\n";
    echo "Response type: " . get_class($response) . "\n";

    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirect URL: " . $response->getTargetUrl() . "\n";

        // Check if there are any session messages
        $session = $request->session();
        if ($session && $session->has('success')) {
            echo "Success message: " . $session->get('success') . "\n";
        }
        if ($session && $session->has('error')) {
            echo "Error message: " . $session->get('error') . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error during controller test: " . $e->getMessage() . "\n";
    echo "Error at line: " . $e->getLine() . " in file: " . $e->getFile() . "\n";

    // More detailed error for debugging
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        echo "Validation errors:\n";
        foreach ($e->errors() as $field => $messages) {
            echo "- $field: " . implode(', ', $messages) . "\n";
        }
    }
}

// Cleanup
if (file_exists($tempFilePath)) {
    unlink($tempFilePath);
    echo "\nTemporary file cleaned up\n";
}

echo "\n=== Test Complete ===\n";
