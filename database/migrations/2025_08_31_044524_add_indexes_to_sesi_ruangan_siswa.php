<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            $table->index(['sesi_ruangan_id', 'status'], 'idx_sesi_ruangan_siswa_status');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            $table->dropIndex('idx_sesi_ruangan_siswa_status');
        });
    }
};
