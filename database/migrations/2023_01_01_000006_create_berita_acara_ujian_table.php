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
        Schema::create('berita_acara_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengawas_id')->references('id')->on('guru');
            $table->text('catatan_pembukaan')->nullable();
            $table->text('catatan_pelaksanaan')->nullable();
            $table->text('catatan_penutupan')->nullable();
            $table->integer('jumlah_peserta_terdaftar')->default(0);
            $table->integer('jumlah_peserta_hadir')->default(0);
            $table->integer('jumlah_peserta_tidak_hadir')->default(0);
            $table->enum('status_pelaksanaan', ['lancar', 'kurang_lancar', 'tidak_lancar'])->default('lancar');
            $table->boolean('is_final')->default(false); // True jika sudah final dan dikirim ke koordinator
            $table->timestamp('waktu_finalisasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acara_ujian');
    }
};
