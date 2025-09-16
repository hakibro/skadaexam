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
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            // Drop foreign key first if it exists
            if (Schema::hasColumn('sesi_ruangan', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable();
            $table->foreign('template_id')->references('id')->on('sesi_templates')->onDelete('set null');
        });
    }
};
