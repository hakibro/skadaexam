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
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_soal_id');
            $table->integer('nomor')->default(1);
            $table->string('tipe_soal', 20)->default('pilihan_ganda'); // pilihan_ganda, essay, true_false
            $table->text('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->json('pilihan')->nullable();
            $table->string('kunci_jawaban', 1)->nullable(); // A, B, C, D, E
            $table->text('pembahasan')->nullable();
            $table->string('gambar_pembahasan')->nullable();
            $table->integer('bobot')->default(1);
            $table->string('tingkat_kesulitan', 20)->default('sedang'); // mudah, sedang, sulit
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('bank_soal_id')->references('id')->on('bank_soal')->onDelete('cascade');
            $table->index(['bank_soal_id', 'nomor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
