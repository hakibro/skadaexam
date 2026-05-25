<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            $table->string('sumber', 50)->nullable()->after('kode_sesi')->index();
        });

        DB::table('sesi_ruangan')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('jadwal_ujian_sesi_ruangan')
                    ->whereColumn('jadwal_ujian_sesi_ruangan.sesi_ruangan_id', 'sesi_ruangan.id');
            })
            ->update(['sumber' => 'sumber']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            $table->dropColumn('sumber');
        });
    }
};
