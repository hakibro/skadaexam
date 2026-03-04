<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            $table->boolean('auto_enroll')->default(true)->after('auto_assign_sesi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('jadwal_ujian', function (Blueprint $table) {
            $table->dropColumn('auto_enroll');
        });
    }
};
