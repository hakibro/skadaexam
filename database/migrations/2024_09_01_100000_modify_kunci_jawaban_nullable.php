<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menggunakan raw SQL karena kita belum memiliki doctrine/dbal untuk mengubah kolom
        DB::statement('ALTER TABLE soal MODIFY COLUMN kunci_jawaban CHAR(1) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE soal MODIFY COLUMN kunci_jawaban CHAR(1) NOT NULL');
    }
};
