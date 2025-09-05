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
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            // Add jadwal_ujian_id column if it doesn't exist
            if (!Schema::hasColumn('sesi_ruangan', 'jadwal_ujian_id')) {
                $table->unsignedBigInteger('jadwal_ujian_id')->nullable()->after('pengawas_id');
                $table->foreign('jadwal_ujian_id')
                    ->references('id')
                    ->on('jadwal_ujian')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            // Drop the foreign key constraint and column if they exist
            if (Schema::hasColumn('sesi_ruangan', 'jadwal_ujian_id')) {
                $table->dropForeign(['jadwal_ujian_id']);
                $table->dropColumn('jadwal_ujian_id');
            }
        });
    }
};
