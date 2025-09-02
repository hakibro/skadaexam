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
        // Skip this migration as we're now using 'sesi_templates' (plural) instead of 'sesi_template'
        // This is to avoid conflicts with other migrations

        // If for some reason we need to add additional columns to the existing 'sesi_templates' table,
        // we can add those here with Schema::table() instead
        if (Schema::hasTable('sesi_templates') && !Schema::hasColumn('sesi_templates', 'kode_sesi')) {
            Schema::table('sesi_templates', function (Blueprint $table) {
                $table->string('kode_sesi')->nullable()->unique()->after('id');
            });
        }

        // We don't need to add template_id again as it should already be added by the previous migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the kode_sesi column we might have added
        if (Schema::hasTable('sesi_templates') && Schema::hasColumn('sesi_templates', 'kode_sesi')) {
            Schema::table('sesi_templates', function (Blueprint $table) {
                $table->dropColumn('kode_sesi');
            });
        }

        // Do not drop template_id from sesi_ruangan or drop sesi_template table
        // as they're handled by other migrations
    }
};
