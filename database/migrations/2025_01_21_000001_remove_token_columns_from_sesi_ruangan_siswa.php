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
            // Remove token columns that duplicate enrollment_ujian functionality
            if (Schema::hasColumn('sesi_ruangan_siswa', 'token')) {
                $table->dropColumn('token');
            }
            if (Schema::hasColumn('sesi_ruangan_siswa', 'token_expired_at')) {
                $table->dropColumn('token_expired_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            // Restore token columns if needed
            $table->string('token')->nullable()->after('status_kehadiran');
            $table->timestamp('token_expired_at')->nullable()->after('token');
        });
    }
};
