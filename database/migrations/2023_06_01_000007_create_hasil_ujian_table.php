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
        Schema::create('hasil_ujian', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->foreignId('enrollment_ujian_id')
                ->constrained('enrollment_ujian')
                ->onDelete('cascade');

            $table->foreignId('siswa_id')
                ->constrained('siswa')
                ->onDelete('cascade');

            $table->foreignId('jadwal_ujian_id')
                ->constrained('jadwal_ujian')
                ->onDelete('cascade');

            // Karena sesi_ujian dihapus → ganti ke sesi_ruangan
            $table->unsignedBigInteger('sesi_ruangan_id')
                ->references('id')->on('sesi_ruangan')
                ->onDelete('set null')
                ->nullable();

            // Informasi ujian
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_menit')->nullable();

            // Hasil & progres
            $table->integer('jumlah_soal')->default(0);
            $table->integer('jumlah_dijawab')->default(0);
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->integer('jumlah_tidak_dijawab')->default(0);

            $table->integer('skor')->default(0); // skor mentah
            $table->decimal('nilai', 5, 2)->default(0); // nilai dalam persen/100
            $table->boolean('lulus')->default(false);

            $table->boolean('is_final')->default(false); // ujian sudah selesai
            $table->string('status', 20)->default('belum_mulai');
            // status: belum_mulai, sedang_mengerjakan, selesai, dibatalkan

            // Detail jawaban & hasil
            $table->json('jawaban')->nullable();       // {"soal_id": "jawaban"}
            $table->json('hasil_detail')->nullable();  // detail per soal/penilaian

            $table->timestamps();

            // Index & constraint tambahan
            $table->index(['jadwal_ujian_id', 'status']);
            $table->index(['siswa_id', 'status']);
            $table->index(['sesi_ruangan_id', 'status']);

            $table->unique(['jadwal_ujian_id', 'siswa_id']);
            // Siswa hanya bisa ikut 1x pada jadwal ujian tertentu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ujian');
    }
};
