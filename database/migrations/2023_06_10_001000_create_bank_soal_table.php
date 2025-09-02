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
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bank', 20)->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('mapel_id');
            $table->string('tingkat')->nullable(); // kelas 10, 11, 12
            $table->string('jenis_soal', 20)->default('pilihan_ganda'); // pilihan_ganda, essay, campuran
            $table->integer('total_soal')->default(0);
            $table->string('status', 20)->default('draft'); // draft, published, archived
            $table->json('pengaturan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('mapel_id')->references('id')->on('mapel');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['mapel_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};
