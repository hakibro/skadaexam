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
        Schema::create('jadwal_ujian', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->foreignId('mapel_id')->constrained('mapel');
            $table->dateTime('tanggal_mulai');
            $table->integer('durasi_menit');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->boolean('tampilkan_hasil')->default(false);
            $table->integer('jumlah_soal')->default(0);
            $table->json('kelas_target')->nullable(); // Array of kelas IDs untuk enrollment otomatis
            $table->unsignedBigInteger('bank_soal_id')->nullable(); // Foreign key will be added in a later migration
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian');
    }
};
