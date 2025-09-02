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
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment_ujian', 'sesi_ujian_id')) {
                $table->dropForeign(['sesi_ujian_id']);
                $table->dropColumn('sesi_ujian_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_ujian', 'sesi_ujian_id')) {
                $table->unsignedBigInteger('sesi_ujian_id')->nullable()->after('sesi_ruangan_id');
            }
        });
    }
};
