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
        // Step 1: Change column to VARCHAR to allow longer values
        DB::statement("ALTER TABLE berita_acara_ujian MODIFY COLUMN status_pelaksanaan VARCHAR(50) DEFAULT 'lancar'");

        // Step 2: Update existing data to match new values
        DB::statement("UPDATE berita_acara_ujian SET status_pelaksanaan = CASE 
            WHEN status_pelaksanaan = 'lancar' THEN 'selesai_normal'
            WHEN status_pelaksanaan = 'kurang_lancar' THEN 'selesai_terganggu' 
            WHEN status_pelaksanaan = 'tidak_lancar' THEN 'selesai_terganggu'
            ELSE status_pelaksanaan 
        END WHERE status_pelaksanaan IN ('lancar', 'kurang_lancar', 'tidak_lancar')");

        // Step 3: Change back to ENUM with new values
        DB::statement("ALTER TABLE berita_acara_ujian MODIFY COLUMN status_pelaksanaan ENUM('selesai_normal', 'selesai_terganggu', 'dibatalkan') DEFAULT 'selesai_normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Change column to VARCHAR to allow data conversion
        DB::statement("ALTER TABLE berita_acara_ujian MODIFY COLUMN status_pelaksanaan VARCHAR(50)");

        // Step 2: Revert data back to original enum values
        DB::statement("UPDATE berita_acara_ujian SET status_pelaksanaan = CASE 
            WHEN status_pelaksanaan = 'selesai_normal' THEN 'lancar'
            WHEN status_pelaksanaan = 'selesai_terganggu' THEN 'kurang_lancar' 
            WHEN status_pelaksanaan = 'dibatalkan' THEN 'tidak_lancar'
            ELSE status_pelaksanaan 
        END WHERE status_pelaksanaan IN ('selesai_normal', 'selesai_terganggu', 'dibatalkan')");

        // Step 3: Change back to original ENUM
        DB::statement("ALTER TABLE berita_acara_ujian MODIFY COLUMN status_pelaksanaan ENUM('lancar', 'kurang_lancar', 'tidak_lancar') DEFAULT 'lancar'");
    }
};
