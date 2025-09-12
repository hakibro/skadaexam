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
        // Modify enum to include 'active' status
        DB::statement("ALTER TABLE enrollment_ujian MODIFY COLUMN status_enrollment ENUM('enrolled','active','completed','cancelled') DEFAULT 'enrolled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'active' from enum (but first update any active records to enrolled)
        DB::statement("UPDATE enrollment_ujian SET status_enrollment = 'enrolled' WHERE status_enrollment = 'active'");
        DB::statement("ALTER TABLE enrollment_ujian MODIFY COLUMN status_enrollment ENUM('enrolled','completed','cancelled') DEFAULT 'enrolled'");
    }
};
