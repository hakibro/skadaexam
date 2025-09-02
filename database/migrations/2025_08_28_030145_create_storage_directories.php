<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create storage directories
        $directories = [
            'soal/pertanyaan',
            'soal/pilihan',
            'soal/pembahasan',
            'soal/temp', // untuk temporary uploads
            'bank-soal/sources',
            'mapel/covers'
        ];

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove directories (be careful!)
        // Storage::deleteDirectory('soal');
    }
};
