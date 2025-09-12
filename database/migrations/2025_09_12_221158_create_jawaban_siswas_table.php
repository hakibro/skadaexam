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
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hasil_ujian_id');
            $table->unsignedBigInteger('soal_ujian_id');
            $table->char('jawaban', 1)->nullable(); // a, b, c, d, e
            $table->boolean('is_flagged')->default(false);
            $table->timestamp('waktu_jawab')->nullable();
            $table->timestamps();

            $table->foreign('hasil_ujian_id')->references('id')->on('hasil_ujian')->onDelete('cascade');
            $table->foreign('soal_ujian_id')->references('id')->on('soal_ujians')->onDelete('cascade');
            $table->unique(['hasil_ujian_id', 'soal_ujian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswas');
    }
};
