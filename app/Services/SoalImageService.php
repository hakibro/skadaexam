<?php
// filepath: app\Services\SoalImageService.php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SoalImageService
{
    /**
     * Upload pertanyaan image
     *
     * @param UploadedFile $file
     * @param int $bankSoalId
     * @param int $nomorSoal
     * @return string Filename
     */
    public function uploadPertanyaanImage(UploadedFile $file, $bankSoalId, $nomorSoal)
    {
        return $this->uploadImage($file, 'pertanyaan', $bankSoalId, $nomorSoal);
    }

    /**
     * Upload pilihan image
     *
     * @param UploadedFile $file
     * @param int $bankSoalId
     * @param int $nomorSoal
     * @param string $pilihan (a, b, c, d, e)
     * @return string Filename
     */
    public function uploadPilihanImage(UploadedFile $file, $bankSoalId, $nomorSoal, $pilihan)
    {
        return $this->uploadImage($file, 'pilihan', $bankSoalId, $nomorSoal, $pilihan);
    }

    /**
     * Upload pembahasan image
     *
     * @param UploadedFile $file
     * @param int $bankSoalId
     * @param int $nomorSoal
     * @return string Filename
     */
    public function uploadPembahasanImage(UploadedFile $file, $bankSoalId, $nomorSoal)
    {
        return $this->uploadImage($file, 'pembahasan', $bankSoalId, $nomorSoal);
    }

    /**
     * Upload image to storage
     *
     * @param UploadedFile $file
     * @param string $type
     * @param int $bankSoalId
     * @param int $nomorSoal
     * @param string|null $pilihan
     * @return string Filename
     */
    private function uploadImage(UploadedFile $file, $type, $bankSoalId, $nomorSoal, $pilihan = null)
    {
        $filename = 'soal_' . $bankSoalId . '_' . $nomorSoal;

        if ($pilihan) {
            $filename .= '_' . $pilihan;
        }

        $originalExtension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();

        // Use a fallback extension if needed
        if (empty($originalExtension)) {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];
            $originalExtension = $mimeToExt[$mimeType] ?? 'png';
        }

        $filename .= '_' . Str::random(8) . '.' . $originalExtension;
        $path = 'soal/' . $type . '/' . $filename;

        // Log image info
        \Illuminate\Support\Facades\Log::info("Uploading image to {$path}", [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'extension' => $originalExtension,
            'path' => $path
        ]);

        try {
            // Get file content
            $content = file_get_contents($file->getRealPath());

            // Verify we have content
            if (empty($content)) {
                throw new \Exception("Empty file content for {$file->getClientOriginalName()}");
            }

            // Upload to storage
            $success = Storage::disk('public')->put($path, $content);

            if (!$success) {
                throw new \Exception("Failed to write file to storage at {$path}");
            }

            \Illuminate\Support\Facades\Log::info("Image uploaded successfully", [
                'path' => $path,
                'filename' => $filename,
                'size' => strlen($content)
            ]);

            return $filename;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error uploading image", [
                'error' => $e->getMessage(),
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Delete image from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteImage($path)
    {
        return Storage::disk('public')->delete($path);
    }

    /**
     * Create a test image in the storage directory
     * 
     * @param string $type The type of directory (pertanyaan, pilihan, pembahasan)
     * @return string Filename
     */
    public function createTestImage($type = 'pertanyaan')
    {
        // Create a 100x100 image with a text
        $im = imagecreatetruecolor(300, 200);
        $backgroundColor = imagecolorallocate($im, 240, 240, 240);
        $textColor = imagecolorallocate($im, 0, 0, 0);
        $borderColor = imagecolorallocate($im, 120, 120, 120);

        // Fill background
        imagefilledrectangle($im, 0, 0, 299, 199, $backgroundColor);

        // Draw border
        imagerectangle($im, 0, 0, 299, 199, $borderColor);

        // Add text
        imagestring($im, 5, 50, 80, 'Test Image - ' . ucfirst($type), $textColor);
        imagestring($im, 3, 50, 100, date('Y-m-d H:i:s'), $textColor);

        // Start output buffering
        ob_start();
        imagepng($im);
        $content = ob_get_clean();

        // Generate filename
        $filename = 'test_' . $type . '_' . time() . '.png';

        // Save file
        $path = 'soal/' . $type . '/' . $filename;
        $saved = Storage::disk('public')->put($path, $content);

        // Free memory
        imagedestroy($im);

        if ($saved) {
            return $filename;
        }

        return null;
    }
}
