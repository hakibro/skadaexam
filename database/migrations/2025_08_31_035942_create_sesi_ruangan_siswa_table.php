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
        Schema::create('sesi_ruangan_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ruangan_id')->constrained('sesi_ruangan')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'tidak_hadir', 'logout'])->default('tidak_hadir');
            $table->timestamps();

            $table->unique(['sesi_ruangan_id', 'siswa_id']); // biar 1 siswa tidak dobel masuk ruangan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_ruangan_siswa');
    }
};
