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
        // Rename columns and migrate data
        if (Schema::hasColumn('soal', 'nomor') && !Schema::hasColumn('soal', 'nomor_soal')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->integer('nomor_soal')->default(1)->after('nomor');
            });

            DB::statement('UPDATE soal SET nomor_soal = nomor');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('nomor');
            });
        }

        // Rename tingkat_kesulitan → kategori
        if (Schema::hasColumn('soal', 'tingkat_kesulitan') && !Schema::hasColumn('soal', 'kategori')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->string('kategori', 20)->default('sedang')->after('tingkat_kesulitan');
            });

            DB::statement('UPDATE soal SET kategori = tingkat_kesulitan');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('tingkat_kesulitan');
            });
        }

        // Rename metadata → display_settings
        if (Schema::hasColumn('soal', 'metadata') && !Schema::hasColumn('soal', 'display_settings')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->json('display_settings')->nullable()->after('metadata');
            });

            DB::statement('UPDATE soal SET display_settings = metadata');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        // Make bobot a decimal field
        if (Schema::hasColumn('soal', 'bobot')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->decimal('bobot_new', 5, 2)->default(1.00)->after('bobot');
            });

            DB::statement('UPDATE soal SET bobot_new = bobot');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('bobot');
            });

            Schema::table('soal', function (Blueprint $table) {
                $table->renameColumn('bobot_new', 'bobot');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke nama kolom asli jika perlu
        if (Schema::hasColumn('soal', 'nomor_soal') && !Schema::hasColumn('soal', 'nomor')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->integer('nomor')->default(1)->after('nomor_soal');
            });

            DB::statement('UPDATE soal SET nomor = nomor_soal');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('nomor_soal');
            });
        }

        // Kembalikan kategori → tingkat_kesulitan
        if (Schema::hasColumn('soal', 'kategori') && !Schema::hasColumn('soal', 'tingkat_kesulitan')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->string('tingkat_kesulitan', 20)->default('sedang')->after('kategori');
            });

            DB::statement('UPDATE soal SET tingkat_kesulitan = kategori');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('kategori');
            });
        }

        // Kembalikan display_settings → metadata
        if (Schema::hasColumn('soal', 'display_settings') && !Schema::hasColumn('soal', 'metadata')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('display_settings');
            });

            DB::statement('UPDATE soal SET metadata = display_settings');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('display_settings');
            });
        }

        // Kembalikan bobot ke integer
        if (Schema::hasColumn('soal', 'bobot')) {
            Schema::table('soal', function (Blueprint $table) {
                $table->integer('bobot_int')->default(1)->after('bobot');
            });

            DB::statement('UPDATE soal SET bobot_int = CAST(bobot AS UNSIGNED)');

            Schema::table('soal', function (Blueprint $table) {
                $table->dropColumn('bobot');
            });

            Schema::table('soal', function (Blueprint $table) {
                $table->renameColumn('bobot_int', 'bobot');
            });
        }
    }
};
