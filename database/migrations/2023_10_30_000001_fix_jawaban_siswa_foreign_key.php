<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah tabel jawaban_siswas sudah ada
        if (Schema::hasTable('jawaban_siswas')) {
            // Tidak perlu mencoba hapus foreign key, langsung rename saja
            Schema::rename('jawaban_siswas', 'jawaban_siswa');
        }

        // Cek apakah tabel jawaban_siswa sudah ada
        if (Schema::hasTable('jawaban_siswa')) {
            // Tidak ada aksi khusus di sini, karena table sudah ada
            // Foreign key sudah ditangani oleh model JawabanSiswa
        } else {
            // Buat tabel jawaban_siswa jika belum ada
            Schema::create('jawaban_siswa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hasil_ujian_id');
                $table->unsignedBigInteger('soal_ujian_id');
                $table->char('jawaban', 1)->nullable(); // a, b, c, d, e
                $table->boolean('is_flagged')->default(false);
                $table->timestamp('waktu_jawab')->nullable();
                $table->timestamps();

                $table->foreign('hasil_ujian_id')->references('id')->on('hasil_ujian')->onDelete('cascade');
                $table->foreign('soal_ujian_id')->references('id')->on('soal')->onDelete('cascade');
                $table->unique(['hasil_ujian_id', 'soal_ujian_id']);
            });
        }

        // Update model to use correct table name
        $this->updateModelFile();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to undo these fixes
    }

    /**
     * Update the JawabanSiswa model file to ensure correct table name
     */
    private function updateModelFile()
    {
        $modelPath = app_path('Models/JawabanSiswa.php');
        if (file_exists($modelPath)) {
            $content = file_get_contents($modelPath);

            if (!str_contains($content, "protected \$table = 'jawaban_siswa'")) {
                // Add table definition after class definition
                $content = preg_replace(
                    '/class JawabanSiswa extends Model\s*{\s*use HasFactory;/m',
                    "class JawabanSiswa extends Model\n{\n    use HasFactory;\n\n    protected \$table = 'jawaban_siswa';",
                    $content
                );

                file_put_contents($modelPath, $content);
            }
        }
    }
};
