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
        // Update existing status values to new format
        DB::table('jadwal_ujian')->where('status', 'active')->update(['status' => 'aktif']);
        DB::table('jadwal_ujian')->where('status', 'completed')->update(['status' => 'selesai']);
        DB::table('jadwal_ujian')->where('status', 'cancelled')->update(['status' => 'nonaktif']);
        // draft status remains the same
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original status values
        DB::table('jadwal_ujian')->where('status', 'aktif')->update(['status' => 'active']);
        DB::table('jadwal_ujian')->where('status', 'selesai')->update(['status' => 'completed']);
        DB::table('jadwal_ujian')->where('status', 'nonaktif')->update(['status' => 'cancelled']);
    }
};
