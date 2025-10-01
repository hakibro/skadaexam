<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Ubah tipe data kolom `tanggal` dari datetime menjadi date
            $table->date('tanggal')->change();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Kembalikan lagi menjadi datetime jika di-rollback
            $table->dateTime('tanggal')->change();
        });
    }
};
