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
        Schema::dropIfExists('sesi_ujian');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_ujian_id');
            $table->string('kode_sesi', 20);
            $table->string('nama_sesi');
            $table->date('tanggal_sesi');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('token_ujian', 20)->nullable();
            $table->dateTime('token_expired_at')->nullable();
            $table->unsignedBigInteger('pengawas_id')->nullable();
            $table->string('status', 20)->default('belum_mulai');
            $table->json('pengaturan')->nullable();
            $table->timestamps();

            $table->foreign('jadwal_ujian_id')
                ->references('id')
                ->on('jadwal_ujian')
                ->cascadeOnDelete();

            $table->foreign('pengawas_id')
                ->references('id')
                ->on('guru')
                ->nullOnDelete();
        });
    }
};
