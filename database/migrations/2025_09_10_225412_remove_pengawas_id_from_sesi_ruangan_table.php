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
        // First, check if there are any remaining pengawas assignments in sesi_ruangan
        // that haven't been migrated to the pivot table
        $sessionsWithPengawas = DB::table('sesi_ruangan')
            ->whereNotNull('pengawas_id')
            ->get();

        if ($sessionsWithPengawas->count() > 0) {
            foreach ($sessionsWithPengawas as $session) {
                // Find all jadwal_ujian linked to this sesi_ruangan
                $jadwalLinks = DB::table('jadwal_ujian_sesi_ruangan')
                    ->where('sesi_ruangan_id', $session->id)
                    ->get();

                // Update each pivot record with the pengawas_id
                foreach ($jadwalLinks as $link) {
                    DB::table('jadwal_ujian_sesi_ruangan')
                        ->where('id', $link->id)
                        ->whereNull('pengawas_id')
                        ->update(['pengawas_id' => $session->pengawas_id]);
                }
            }
        }

        // Now remove the column from sesi_ruangan table
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            if (Schema::hasColumn('sesi_ruangan', 'pengawas_id')) {
                // Try to drop the foreign key constraint
                try {
                    $table->dropForeign(['pengawas_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist or can't be dropped, continue anyway
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                }
                $table->dropColumn('pengawas_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            // Add back the pengawas_id column if needed
            if (!Schema::hasColumn('sesi_ruangan', 'pengawas_id')) {
                $table->unsignedBigInteger('pengawas_id')->nullable();
                $table->foreign('pengawas_id')->references('id')->on('guru')->nullOnDelete();
            }
        });
    }
};
