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
        Schema::table('berita_acara_ujian', function (Blueprint $table) {
            // Add sesi_ruangan_id column if it doesn't exist
            if (!Schema::hasColumn('berita_acara_ujian', 'sesi_ruangan_id')) {
                $table->unsignedBigInteger('sesi_ruangan_id')->nullable()->after('id');
                $table->foreign('sesi_ruangan_id')
                    ->references('id')
                    ->on('sesi_ruangan')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('berita_acara_ujian', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('berita_acara_ujian', 'sesi_ruangan_id')) {
                $table->dropForeign(['sesi_ruangan_id']);
                $table->dropColumn('sesi_ruangan_id');
            }
        });
    }
};
