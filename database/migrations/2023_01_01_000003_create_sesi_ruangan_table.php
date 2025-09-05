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
        Schema::create('sesi_ruangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sesi', 20);
            $table->string('nama_sesi');
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('token_ujian', 20)->nullable();
            $table->dateTime('token_expired_at')->nullable();
            $table->string('status', 20)->default('belum_mulai');
            $table->json('pengaturan')->nullable();
            $table->unsignedBigInteger('ruangan_id');
            $table->foreign('ruangan_id')
                ->references('id')
                ->on('ruangan')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('pengawas_id')->nullable();
            $table->foreign('pengawas_id')
                ->references('id')
                ->on('guru')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('sesi_ruangan');
    }
};
