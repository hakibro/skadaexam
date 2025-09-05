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
        // Create pivot table for many-to-many relationship
        Schema::create('jadwal_ujian_sesi_ruangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_ujian_id');
            $table->unsignedBigInteger('sesi_ruangan_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('jadwal_ujian_id')
                ->references('id')
                ->on('jadwal_ujian')
                ->onDelete('cascade');

            $table->foreign('sesi_ruangan_id')
                ->references('id')
                ->on('sesi_ruangan')
                ->onDelete('cascade');

            // Ensure unique combinations
            $table->unique(['jadwal_ujian_id', 'sesi_ruangan_id'], 'jadwal_sesi_unique');

            // Add indexes for better performance
            $table->index(['jadwal_ujian_id']);
            $table->index(['sesi_ruangan_id']);
        });

        // Migrate existing data from sesi_ruangan.jadwal_ujian_id to pivot table
        if (Schema::hasColumn('sesi_ruangan', 'jadwal_ujian_id')) {
            DB::statement("
                INSERT INTO jadwal_ujian_sesi_ruangan (jadwal_ujian_id, sesi_ruangan_id, created_at, updated_at)
                SELECT jadwal_ujian_id, id, NOW(), NOW()
                FROM sesi_ruangan 
                WHERE jadwal_ujian_id IS NOT NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian_sesi_ruangan');
    }
};
