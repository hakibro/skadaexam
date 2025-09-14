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
        Schema::create('pelanggaran_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('hasil_ujian_id')->constrained('hasil_ujian');
            $table->foreignId('jadwal_ujian_id')->constrained('jadwal_ujian');
            $table->foreignId('sesi_ruangan_id')->constrained('sesi_ruangan');
            $table->string('jenis_pelanggaran'); // tab_switching, etc.
            $table->text('deskripsi')->nullable();
            $table->timestamp('waktu_pelanggaran');
            $table->boolean('is_dismissed')->default(false);
            $table->boolean('is_finalized')->default(false);
            $table->string('tindakan')->nullable(); // peringatan, skors, diskualifikasi
            $table->text('catatan_pengawas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_ujian');
    }
};
