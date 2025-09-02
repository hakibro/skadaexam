<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->softDeletes(); // otomatis bikin kolom deleted_at nullable timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
