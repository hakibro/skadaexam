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
        // Remove jadwal_ujian_id column from sesi_ruangan table
        // since we're now using pivot table for many-to-many relationship
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            if (Schema::hasColumn('sesi_ruangan', 'jadwal_ujian_id')) {
                $table->dropForeign(['jadwal_ujian_id']);
                $table->dropColumn('jadwal_ujian_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add jadwal_ujian_id column if needed
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            if (!Schema::hasColumn('sesi_ruangan', 'jadwal_ujian_id')) {
                $table->unsignedBigInteger('jadwal_ujian_id')->nullable()->after('pengawas_id');
                $table->foreign('jadwal_ujian_id')
                    ->references('id')
                    ->on('jadwal_ujian')
                    ->onDelete('set null');
            }
        });
    }
};
