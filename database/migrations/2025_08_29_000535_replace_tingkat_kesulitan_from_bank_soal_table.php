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
        Schema::table('bank_soal', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('bank_soal', 'tingkat_kesulitan')) {
                $table->dropColumn('tingkat_kesulitan');
            }

            // Add new column if it doesn't exist
            if (!Schema::hasColumn('bank_soal', 'tingkat')) {
                $table->string('tingkat', 5)->default('X')->after('deskripsi'); // X, XI, XII
            }

            // Safely handle index operations - removed index drop that doesn't exist

            try {
                $table->index(['status', 'tingkat']);
            } catch (\Exception $e) {
                // Index already exists or other issue, continue
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            // Check if column exists before trying to drop it
            if (Schema::hasColumn('bank_soal', 'tingkat')) {
                $table->dropColumn('tingkat');
            }

            // Add column if it doesn't exist
            if (!Schema::hasColumn('bank_soal', 'tingkat_kesulitan')) {
                $table->string('tingkat_kesulitan', 20)->default('sedang')->after('deskripsi'); // mudah, sedang, sulit
            }

            // Safely handle index operations - removed index drop that doesn't exist

            try {
                $table->index(['status', 'tingkat_kesulitan']);
            } catch (\Exception $e) {
                // Index already exists or other issue, continue
            }
        });
    }
};
