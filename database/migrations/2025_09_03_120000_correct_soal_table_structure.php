<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan tabel soal memiliki semua kolom sesuai dengan Model Soal.php
        if (!Schema::hasColumn('soal', 'tipe_pertanyaan')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->string('tipe_pertanyaan', 20)->default('teks')->after('pertanyaan');
            });
        }

        // Rename nomor → nomor_soal
        if (Schema::hasColumn('soal', 'nomor') && !Schema::hasColumn('soal', 'nomor_soal')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->integer('nomor_soal')->default(1)->after('id');
            });

            DB::statement('UPDATE soal SET nomor_soal = nomor');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('nomor');
            });
        }

        // Rename tingkat_kesulitan → kategori
        if (Schema::hasColumn('soal', 'tingkat_kesulitan') && !Schema::hasColumn('soal', 'kategori')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->string('kategori', 20)->default('sedang')->after('bank_soal_id');
            });

            DB::statement('UPDATE soal SET kategori = tingkat_kesulitan');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('tingkat_kesulitan');
            });
        }

        // Rename metadata → display_settings
        if (Schema::hasColumn('soal', 'metadata') && !Schema::hasColumn('soal', 'display_settings')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->json('display_settings')->nullable()->after('kategori');
            });

            DB::statement('UPDATE soal SET display_settings = metadata');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        // Add pilihan columns with image and type support
        if (!Schema::hasColumn('soal', 'pilihan_a_teks')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->text('pilihan_a_teks')->nullable()->after('tipe_pertanyaan');
                $table->string('pilihan_a_gambar')->nullable()->after('pilihan_a_teks');
                $table->string('pilihan_a_tipe', 20)->default('teks')->after('pilihan_a_gambar');

                $table->text('pilihan_b_teks')->nullable()->after('pilihan_a_tipe');
                $table->string('pilihan_b_gambar')->nullable()->after('pilihan_b_teks');
                $table->string('pilihan_b_tipe', 20)->default('teks')->after('pilihan_b_gambar');

                $table->text('pilihan_c_teks')->nullable()->after('pilihan_b_tipe');
                $table->string('pilihan_c_gambar')->nullable()->after('pilihan_c_teks');
                $table->string('pilihan_c_tipe', 20)->default('teks')->after('pilihan_c_gambar');

                $table->text('pilihan_d_teks')->nullable()->after('pilihan_c_tipe');
                $table->string('pilihan_d_gambar')->nullable()->after('pilihan_d_teks');
                $table->string('pilihan_d_tipe', 20)->default('teks')->after('pilihan_d_gambar');

                $table->text('pilihan_e_teks')->nullable()->after('pilihan_d_tipe');
                $table->string('pilihan_e_gambar')->nullable()->after('pilihan_e_teks');
                $table->string('pilihan_e_tipe', 20)->default('teks')->after('pilihan_e_gambar');
            });

            // Migrate data from pilihan JSON if exists
            if (Schema::hasColumn('soal', 'pilihan')) {
                DB::statement('
                    UPDATE soal 
                    SET 
                        pilihan_a_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.A.text")),
                        pilihan_b_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.B.text")),
                        pilihan_c_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.C.text")),
                        pilihan_d_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.D.text")),
                        pilihan_e_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.E.text"))
                ');

                Schema::table('soal', function (Blueprint $table) {
                    $table->dropColumn('pilihan');
                });
            }
        }

        // Add pembahasan fields
        if (!Schema::hasColumn('soal', 'pembahasan_teks')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->text('pembahasan_teks')->nullable()->after('kunci_jawaban');
                $table->string('pembahasan_gambar')->nullable()->after('pembahasan_teks');
                $table->string('pembahasan_tipe', 20)->default('teks')->after('pembahasan_gambar');
            });

            // Migrate data from pembahasan if exists
            if (Schema::hasColumn('soal', 'pembahasan')) {
                DB::statement('UPDATE soal SET pembahasan_teks = pembahasan');

                Schema::table('soal', function (Blueprint $table) {
                    $table->dropColumn('pembahasan');
                });
            }
        }

        // Update bobot to decimal
        if (Schema::hasColumn('soal', 'bobot')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->decimal('bobot_new', 5, 2)->default(1.00)->after('bobot');
            });

            DB::statement('UPDATE soal SET bobot_new = bobot');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('bobot');
            });

            Schema::table('soal', function (Blueprint $table) {
                $table->decimal('bobot', 5, 2)->default(1.00)->after('pembahasan_tipe');
            });

            DB::statement('UPDATE soal SET bobot = bobot_new');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('bobot_new');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada operasi mundur karena ini adalah migrasi koreksi struktur
    }
};
