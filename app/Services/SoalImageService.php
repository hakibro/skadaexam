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

        $filename .= '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        $path = 'soal/' . $type . '/' . $filename;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $filename;
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
