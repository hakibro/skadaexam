<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->dropForeign(['paket_ujian_id']);
            $table->dropColumn('paket_ujian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->foreignId('paket_ujian_id')
                ->nullable()
                ->after('tahun_ajaran_id')
                ->constrained('paket_ujian')
                ->nullOnDelete();
        });
    }
};
