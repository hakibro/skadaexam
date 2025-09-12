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
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            $table->string('token', 10)->nullable()->after('status');
            $table->timestamp('token_expired_at')->nullable()->after('token');
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir', 'sakit', 'izin'])->nullable()->after('token_expired_at');
            $table->text('keterangan')->nullable()->after('status_kehadiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            $table->dropColumn(['token', 'token_expired_at', 'status_kehadiran', 'keterangan']);
        });
    }
};
