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
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            // Add auto assignment setting for sesi ruangan
            $table->boolean('auto_assign_sesi')->default(true)->after('status');

            // Add scheduling mode - 'fixed' for traditional scheduling, 'flexible' for sesi-based
            $table->enum('scheduling_mode', ['fixed', 'flexible'])->default('flexible')->after('auto_assign_sesi');

            // Add timezone support
            $table->string('timezone')->default('Asia/Jakarta')->after('scheduling_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            $table->dropColumn(['auto_assign_sesi', 'scheduling_mode', 'timezone']);
        });
    }
};
