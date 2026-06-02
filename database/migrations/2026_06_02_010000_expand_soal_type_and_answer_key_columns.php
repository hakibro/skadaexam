<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE soal MODIFY COLUMN tipe_soal VARCHAR(50) NOT NULL DEFAULT "pilihan_ganda"');
        DB::statement('ALTER TABLE soal MODIFY COLUMN kunci_jawaban TEXT NULL');

        if (Schema::hasTable('soal_ujians') && Schema::hasColumn('soal_ujians', 'kunci_jawaban')) {
            DB::statement('ALTER TABLE soal_ujians MODIFY COLUMN kunci_jawaban TEXT NULL');
        }

        if (Schema::hasTable('jawaban_siswa') && Schema::hasColumn('jawaban_siswa', 'jawaban')) {
            $this->dropIndexIfExists('jawaban_siswa', 'idx_jawaban_siswa_hasil_jawaban');
            DB::statement('ALTER TABLE jawaban_siswa MODIFY COLUMN jawaban TEXT NULL');

            if (Schema::hasColumn('jawaban_siswa', 'hasil_ujian_id')) {
                DB::statement('CREATE INDEX idx_jawaban_siswa_hasil_jawaban ON jawaban_siswa (hasil_ujian_id, jawaban(32))');
            }
        }

        if (Schema::hasTable('jawaban_siswas') && Schema::hasColumn('jawaban_siswas', 'jawaban')) {
            DB::statement('ALTER TABLE jawaban_siswas MODIFY COLUMN jawaban TEXT NULL');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE soal MODIFY COLUMN tipe_soal VARCHAR(20) NOT NULL DEFAULT "pilihan_ganda"');
        DB::statement('ALTER TABLE soal MODIFY COLUMN kunci_jawaban CHAR(1) NULL');

        if (Schema::hasTable('soal_ujians') && Schema::hasColumn('soal_ujians', 'kunci_jawaban')) {
            DB::statement('ALTER TABLE soal_ujians MODIFY COLUMN kunci_jawaban CHAR(1) NULL');
        }

        if (Schema::hasTable('jawaban_siswa') && Schema::hasColumn('jawaban_siswa', 'jawaban')) {
            $this->dropIndexIfExists('jawaban_siswa', 'idx_jawaban_siswa_hasil_jawaban');
            DB::statement('ALTER TABLE jawaban_siswa MODIFY COLUMN jawaban VARCHAR(10) NULL');

            if (Schema::hasColumn('jawaban_siswa', 'hasil_ujian_id')) {
                DB::statement('CREATE INDEX idx_jawaban_siswa_hasil_jawaban ON jawaban_siswa (hasil_ujian_id, jawaban)');
            }
        }

        if (Schema::hasTable('jawaban_siswas') && Schema::hasColumn('jawaban_siswas', 'jawaban')) {
            DB::statement('ALTER TABLE jawaban_siswas MODIFY COLUMN jawaban VARCHAR(10) NULL');
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();

        if ($exists) {
            DB::statement("DROP INDEX {$index} ON {$table}");
        }
    }
};
