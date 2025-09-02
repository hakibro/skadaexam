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
        // Skip table creation if it already exists
        if (!Schema::hasTable('jadwal_ujian')) {
            Schema::create('jadwal_ujian', function (Blueprint $table) {
                $table->id();
                $table->string('kode_ujian', 20)->unique();
                $table->string('nama_ujian');
                $table->text('deskripsi')->nullable();
                $table->unsignedBigInteger('mapel_id');
                $table->unsignedBigInteger('bank_soal_id');
                $table->string('jenis_ujian', 50); // ulangan, uts, uas, remedial, etc.
                $table->date('tanggal_ujian');
                $table->time('waktu_mulai');
                $table->time('waktu_selesai');
                $table->integer('durasi_menit');
                $table->integer('jumlah_soal');
                $table->boolean('acak_soal')->default(false);
                $table->boolean('acak_jawaban')->default(false);
                $table->boolean('tampilkan_hasil')->default(true);
                $table->string('status', 20)->default('draft'); // draft, aktif, selesai, dibatalkan
                $table->json('pengaturan')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->foreign('mapel_id')->references('id')->on('mapel');
                $table->foreign('bank_soal_id')->references('id')->on('bank_soal');
                $table->foreign('created_by')->references('id')->on('users');

                $table->index(['tanggal_ujian', 'status']);
            });
        } else {
            // If the table exists, ensure required columns are present and add missing ones
            Schema::table('jadwal_ujian', function (Blueprint $table) {
                if (!Schema::hasColumn('jadwal_ujian', 'kode_ujian')) {
                    $table->string('kode_ujian', 20)->unique();
                }

                if (!Schema::hasColumn('jadwal_ujian', 'jenis_ujian')) {
                    $table->string('jenis_ujian', 50)->nullable(); // Make nullable for existing records
                }

                // Add any other important columns that might be missing
                if (!Schema::hasColumn('jadwal_ujian', 'acak_soal')) {
                    $table->boolean('acak_soal')->default(false);
                }

                if (!Schema::hasColumn('jadwal_ujian', 'acak_jawaban')) {
                    $table->boolean('acak_jawaban')->default(false);
                }
            });

            // Try to add the index if it doesn't exist (might fail silently if it already exists)
            try {
                Schema::table('jadwal_ujian', function (Blueprint $table) {
                    $table->index(['tanggal_ujian', 'status'], 'jadwal_ujian_tanggal_status_index');
                });
            } catch (\Exception $e) {
                // Index likely already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian');
    }
};
