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
        // Add pengawas_id to jadwal_ujian_sesi_ruangan pivot table
        Schema::table('jadwal_ujian_sesi_ruangan', function (Blueprint $table) {
            $table->unsignedBigInteger('pengawas_id')->nullable();
            $table->foreign('pengawas_id')
                ->references('id')
                ->on('guru')
                ->nullOnDelete();
        });

        // Migrate existing pengawas assignments from sesi_ruangan to the pivot table
        DB::statement("
            UPDATE jadwal_ujian_sesi_ruangan jsr
            INNER JOIN sesi_ruangan sr ON jsr.sesi_ruangan_id = sr.id
            SET jsr.pengawas_id = sr.pengawas_id
            WHERE sr.pengawas_id IS NOT NULL
        ");

        // Keep pengawas_id in sesi_ruangan for backward compatibility
        // But mark it as deprecated in the comments
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate data back from pivot table to sesi_ruangan table
        DB::statement("
            UPDATE sesi_ruangan sr
            INNER JOIN jadwal_ujian_sesi_ruangan jsr ON sr.id = jsr.sesi_ruangan_id
            SET sr.pengawas_id = jsr.pengawas_id
            WHERE jsr.pengawas_id IS NOT NULL
        ");

        // Remove pengawas_id from pivot table
        Schema::table('jadwal_ujian_sesi_ruangan', function (Blueprint $table) {
            $table->dropForeign(['pengawas_id']);
            $table->dropColumn('pengawas_id');
        });
    }
};
