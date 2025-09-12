<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            // First, let's migrate any existing status data to status_kehadiran
            // if status_kehadiran is null but status has a value
            DB::statement("UPDATE sesi_ruangan_siswa SET status_kehadiran = status WHERE status_kehadiran IS NULL AND status IS NOT NULL");

            // Now drop the redundant status column
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan_siswa', function (Blueprint $table) {
            // Recreate the status column
            $table->string('status')->nullable()->after('siswa_id');
        });
    }
};
