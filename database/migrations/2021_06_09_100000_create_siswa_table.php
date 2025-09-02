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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique()->nullable(); // NIS baru
            $table->string('idyayasan')->unique();       // id yayasan siswa
            $table->string('nama');                      // nama siswa
            $table->string('email')->unique();           // email siswa
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');                  // password siswa
            $table->unsignedBigInteger('kelas_id')->nullable(); // Relasi ke tabel kelas
            $table->enum('status_pembayaran', ['Lunas', 'Belum Lunas'])->default('Belum Lunas');
            $table->enum('rekomendasi', ['ya', 'tidak'])->default('tidak');
            $table->text('catatan_rekomendasi')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
