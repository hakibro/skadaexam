<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\JadwalUjianSesiRuangan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if pengawas_id column exists in jadwal_ujian table
        if (Schema::hasColumn('jadwal_ujian', 'pengawas_id')) {
            // Check for any jadwal_ujian with pengawas_id (legacy system) and migrate them to the pivot table
            $jadwalWithPengawas = DB::table('jadwal_ujian')
                ->whereNotNull('pengawas_id')
                ->select('id', 'pengawas_id')
                ->get();

            if ($jadwalWithPengawas->count() > 0) {
                foreach ($jadwalWithPengawas as $jadwal) {
                    // Find all sesi_ruangan linked to this jadwal
                    $sesiRuangans = DB::table('jadwal_ujian_sesi_ruangan')
                        ->where('jadwal_ujian_id', $jadwal->id)
                        ->select('sesi_ruangan_id')
                        ->get();

                    // Update each pivot record with the pengawas_id
                    foreach ($sesiRuangans as $sesi) {
                        DB::table('jadwal_ujian_sesi_ruangan')
                            ->where([
                                'jadwal_ujian_id' => $jadwal->id,
                                'sesi_ruangan_id' => $sesi->sesi_ruangan_id,
                            ])
                            ->update(['pengawas_id' => $jadwal->pengawas_id]);
                    }
                }
            }
        }

        // Check if pengawas_id column exists in jadwal_ujian table
        if (Schema::hasColumn('jadwal_ujian', 'pengawas_id')) {
            // Drop the legacy pengawas_id from jadwal_ujian table as it's now in the pivot
            Schema::table('jadwal_ujian', function (Blueprint $table) {
                // Check if the foreign key exists before trying to drop it
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('jadwal_ujian');

                $hasForeignKey = false;
                foreach ($foreignKeys as $key) {
                    if (in_array('pengawas_id', $key->getLocalColumns())) {
                        $hasForeignKey = true;
                        break;
                    }
                }

                if ($hasForeignKey) {
                    $table->dropForeign(['pengawas_id']);
                }

                $table->dropColumn('pengawas_id');
            });
        }

        // Log the migration if the migrations_log table exists
        if (Schema::hasTable('migrations_log')) {
            DB::table('migrations_log')->insert([
                'migration' => '2025_09_10_153213_migrate_legacy_pengawas_assignments_to_pivot',
                'batch' => DB::table('migrations')->max('batch') + 1,
                'message' => 'Legacy pengawas assignments migrated to pivot table',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't add back the pengawas_id column to jadwal_ujian
        // as the new system is designed to use the pivot table
        // If needed, we could recreate the column and move data back
    }
};
