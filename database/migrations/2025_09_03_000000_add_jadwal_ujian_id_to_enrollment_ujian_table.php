<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            // Add jadwal_ujian_id after sesi_ruangan_id if it doesn't exist
            if (!Schema::hasColumn('enrollment_ujian', 'jadwal_ujian_id')) {
                $table->unsignedBigInteger('jadwal_ujian_id')->nullable()->after('sesi_ruangan_id');

                $table->foreign('jadwal_ujian_id')
                    ->references('id')
                    ->on('jadwal_ujian')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment_ujian', 'jadwal_ujian_id')) {
                $table->dropForeign(['jadwal_ujian_id']);
                $table->dropColumn('jadwal_ujian_id');
            }
        });
    }
};
