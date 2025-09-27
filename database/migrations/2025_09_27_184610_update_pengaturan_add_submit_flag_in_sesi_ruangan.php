<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Update semua data yang sudah ada, tambahkan key default
        $sesiList = DB::table('sesi_ruangan')->get();

        foreach ($sesiList as $sesi) {
            $pengaturan = json_decode($sesi->pengaturan, true) ?? [];
            if (! array_key_exists('tampilkan_tombol_submit', $pengaturan)) {
                $pengaturan['tampilkan_tombol_submit'] = false;
                DB::table('sesi_ruangan')
                    ->where('id', $sesi->id)
                    ->update(['pengaturan' => json_encode($pengaturan)]);
            }
        }
    }

    public function down(): void
    {
        // Rollback = hapus key tampilkan_tombol_submit
        $sesiList = DB::table('sesi_ruangan')->get();

        foreach ($sesiList as $sesi) {
            $pengaturan = json_decode($sesi->pengaturan, true) ?? [];
            if (array_key_exists('tampilkan_tombol_submit', $pengaturan)) {
                unset($pengaturan['tampilkan_tombol_submit']);
                DB::table('sesi_ruangan')
                    ->where('id', $sesi->id)
                    ->update(['pengaturan' => json_encode($pengaturan)]);
            }
        }
    }
};
