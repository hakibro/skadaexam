<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->foreignId('paket_ujian_id')
                ->nullable()
                ->after('tahun_ajaran_id')
                ->constrained('paket_ujian')
                ->nullOnDelete();
        });

        Schema::table('mapel', function (Blueprint $table) {
            $table->foreignId('paket_ujian_id')
                ->nullable()
                ->after('tahun_ajaran_id')
                ->constrained('paket_ujian')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropForeign(['paket_ujian_id']);
            $table->dropColumn('paket_ujian_id');
        });

        Schema::table('mapel', function (Blueprint $table) {
            $table->dropForeign(['paket_ujian_id']);
            $table->dropColumn('paket_ujian_id');
        });
    }
};