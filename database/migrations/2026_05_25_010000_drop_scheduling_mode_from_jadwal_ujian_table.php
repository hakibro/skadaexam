<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_ujian', 'scheduling_mode')) {
                $table->dropColumn('scheduling_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_ujian', 'scheduling_mode')) {
                $table->enum('scheduling_mode', ['fixed', 'flexible'])
                    ->default('flexible')
                    ->after('auto_assign_sesi');
            }
        });
    }
};
