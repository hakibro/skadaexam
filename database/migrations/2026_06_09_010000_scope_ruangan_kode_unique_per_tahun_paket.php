<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropUnique('ruangan_kode_ruangan_unique');
            $table->unique(
                ['tahun_ajaran_id', 'paket_ujian_id', 'kode_ruangan'],
                'ruangan_tahun_paket_kode_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropUnique('ruangan_tahun_paket_kode_unique');
            $table->unique('kode_ruangan', 'ruangan_kode_ruangan_unique');
        });
    }
};
