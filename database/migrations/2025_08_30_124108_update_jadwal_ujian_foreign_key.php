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
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Hapus foreign key constraint yang lama
            $table->dropForeign(['mapel_id']);

            // Rename kolom
            $table->renameColumn('mapel_id', 'mapel_id');
        });

        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Tambahkan foreign key constraint yang baru
            $table->foreign('mapel_id')->references('id')->on('mapel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Hapus foreign key constraint yang baru
            $table->dropForeign(['mapel_id']);
        });

        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Tambahkan kembali foreign key constraint yang lama
            $table->foreign('mapel_id')->references('id')->on('mapel');
        });
    }
};
