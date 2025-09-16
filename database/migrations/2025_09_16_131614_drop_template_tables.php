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
        // Drop template tables in correct order (foreign key dependencies)
        Schema::dropIfExists('sesi_template_siswa');
        Schema::dropIfExists('sesi_template_ruangan');
        Schema::dropIfExists('sesi_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate template tables (simplified version - you may need to adjust based on your needs)
        Schema::create('sesi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sesi');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('status', 20)->default('belum_mulai');
            $table->json('pengaturan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sesi_template_ruangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesi_template_id');
            $table->unsignedBigInteger('ruangan_id');
            $table->timestamps();

            $table->foreign('sesi_template_id')->references('id')->on('sesi_templates')->onDelete('cascade');
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->unique(['sesi_template_id', 'ruangan_id']);
        });

        Schema::create('sesi_template_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesi_template_id');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('ruangan_id');
            $table->timestamps();

            $table->foreign('sesi_template_id')->references('id')->on('sesi_templates')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('cascade');
            $table->unique(['sesi_template_id', 'siswa_id', 'ruangan_id']);
        });
    }
};
