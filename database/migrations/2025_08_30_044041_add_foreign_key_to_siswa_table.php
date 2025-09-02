<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds foreign key constraint to siswa table for kelas_id column
     */
    public function up(): void
    {
        // First check if any orphaned records exist
        $orphans = DB::table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereNotNull('siswa.kelas_id')
            ->whereNull('kelas.id')
            ->count();

        if ($orphans > 0) {
            // Set kelas_id to NULL for orphaned records
            DB::table('siswa')
                ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->whereNotNull('siswa.kelas_id')
                ->whereNull('kelas.id')
                ->update(['kelas_id' => null]);

            // Log warning about orphaned records
            Log::warning('Fixed ' . $orphans . ' orphaned siswa records before adding foreign key constraint');
        }

        // Add foreign key in a simple, direct way
        Schema::table('siswa', function (Blueprint $table) {
            try {
                $table->foreign('kelas_id')
                    ->references('id')
                    ->on('kelas')
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                Log::info('Successfully added foreign key constraint to siswa.kelas_id');
            } catch (\Exception $e) {
                Log::warning('Could not add foreign key constraint: ' . $e->getMessage());
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            try {
                $table->dropForeign(['kelas_id']);
            } catch (\Exception $e) {
                Log::warning('Could not drop foreign key constraint: ' . $e->getMessage());
            }
        });
    }
};
