<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('hasil_ujian')) {
            Schema::table('hasil_ujian', function (Blueprint $table) {
                $table->index(['status', 'jadwal_ujian_id', 'nilai'], 'idx_hasil_analisis_status_jadwal_nilai');
                $table->index(['status', 'siswa_id'], 'idx_hasil_analisis_status_siswa');
                $table->index(['status', 'lulus'], 'idx_hasil_analisis_status_lulus');
            });
        }

        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->index(['kelas_id', 'deleted_at'], 'idx_siswa_kelas_deleted');
            });
        }

        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->index(['tingkat', 'jurusan'], 'idx_kelas_tingkat_jurusan');
                $table->index('nama_kelas', 'idx_kelas_nama');
            });
        }

        if (Schema::hasTable('jawaban_siswa')) {
            Schema::table('jawaban_siswa', function (Blueprint $table) {
                $table->index(['hasil_ujian_id', 'jawaban'], 'idx_jawaban_siswa_hasil_jawaban');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('jawaban_siswa')) {
            Schema::table('jawaban_siswa', function (Blueprint $table) {
                $table->dropIndex('idx_jawaban_siswa_hasil_jawaban');
            });
        }

        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropIndex('idx_kelas_tingkat_jurusan');
                $table->dropIndex('idx_kelas_nama');
            });
        }

        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropIndex('idx_siswa_kelas_deleted');
            });
        }

        if (Schema::hasTable('hasil_ujian')) {
            Schema::table('hasil_ujian', function (Blueprint $table) {
                $table->dropIndex('idx_hasil_analisis_status_lulus');
                $table->dropIndex('idx_hasil_analisis_status_siswa');
                $table->dropIndex('idx_hasil_analisis_status_jadwal_nilai');
            });
        }
    }
};
