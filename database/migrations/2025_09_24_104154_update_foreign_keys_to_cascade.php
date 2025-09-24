<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint lama di bank_soal
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
        });

        // Tambah constraint baru dengan cascade
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->foreign('mapel_id')
                ->references('id')->on('mapel')
                ->onDelete('cascade');
        });

        // Hapus constraint lama di soal
        Schema::table('soal', function (Blueprint $table) {
            $table->dropForeign(['bank_soal_id']);
        });

        // Tambah constraint baru dengan cascade
        Schema::table('soal', function (Blueprint $table) {
            $table->foreign('bank_soal_id')
                ->references('id')->on('bank_soal')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Rollback ke tanpa cascade
        Schema::table('soal', function (Blueprint $table) {
            $table->dropForeign(['bank_soal_id']);
            $table->foreign('bank_soal_id')
                ->references('id')->on('bank_soal');
        });

        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->foreign('mapel_id')
                ->references('id')->on('mapel');
        });
    }
};
