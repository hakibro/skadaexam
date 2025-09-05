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
        Schema::table('soal', function (Blueprint $table) {
            // Add tipe_pertanyaan if it doesn't exist
            if (!Schema::hasColumn('soal', 'tipe_pertanyaan')) {
                $table->string('tipe_pertanyaan', 20)->default('teks')->after('pertanyaan');
            }

            // Add pilihan columns
            if (!Schema::hasColumn('soal', 'pilihan_a_teks')) {
                $table->text('pilihan_a_teks')->nullable()->after('tipe_pertanyaan');
                $table->string('pilihan_a_gambar')->nullable()->after('pilihan_a_teks');
                $table->string('pilihan_a_tipe', 20)->default('teks')->after('pilihan_a_gambar');
            }

            if (!Schema::hasColumn('soal', 'pilihan_b_teks')) {
                $table->text('pilihan_b_teks')->nullable()->after('pilihan_a_tipe');
                $table->string('pilihan_b_gambar')->nullable()->after('pilihan_b_teks');
                $table->string('pilihan_b_tipe', 20)->default('teks')->after('pilihan_b_gambar');
            }

            if (!Schema::hasColumn('soal', 'pilihan_c_teks')) {
                $table->text('pilihan_c_teks')->nullable()->after('pilihan_b_tipe');
                $table->string('pilihan_c_gambar')->nullable()->after('pilihan_c_teks');
                $table->string('pilihan_c_tipe', 20)->default('teks')->after('pilihan_c_gambar');
            }

            if (!Schema::hasColumn('soal', 'pilihan_d_teks')) {
                $table->text('pilihan_d_teks')->nullable()->after('pilihan_c_tipe');
                $table->string('pilihan_d_gambar')->nullable()->after('pilihan_d_teks');
                $table->string('pilihan_d_tipe', 20)->default('teks')->after('pilihan_d_gambar');
            }

            if (!Schema::hasColumn('soal', 'pilihan_e_teks')) {
                $table->text('pilihan_e_teks')->nullable()->after('pilihan_d_tipe');
                $table->string('pilihan_e_gambar')->nullable()->after('pilihan_e_teks');
                $table->string('pilihan_e_tipe', 20)->default('teks')->after('pilihan_e_gambar');
            }

            // Add pembahasan columns
            if (!Schema::hasColumn('soal', 'pembahasan_teks')) {
                $table->text('pembahasan_teks')->nullable()->after('kunci_jawaban');
                $table->string('pembahasan_gambar')->nullable()->after('pembahasan_teks');
                $table->string('pembahasan_tipe', 20)->default('teks')->after('pembahasan_gambar');
            }

            // Add kategori if tingkat_kesulitan doesn't exist and kategori doesn't exist
            if (!Schema::hasColumn('soal', 'tingkat_kesulitan') && !Schema::hasColumn('soal', 'kategori')) {
                $table->string('kategori', 20)->default('sedang')->after('bank_soal_id');
            }

            // Add nomor_soal if nomor doesn't exist and nomor_soal doesn't exist
            if (!Schema::hasColumn('soal', 'nomor') && !Schema::hasColumn('soal', 'nomor_soal')) {
                $table->integer('nomor_soal')->default(1)->after('bank_soal_id');
            }

            // Add display_settings if metadata doesn't exist and display_settings doesn't exist
            if (!Schema::hasColumn('soal', 'metadata') && !Schema::hasColumn('soal', 'display_settings')) {
                $table->json('display_settings')->nullable()->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada operasi mundur karena ini adalah migrasi yang menambahkan kolom-kolom yang dibutuhkan model
    }
};
