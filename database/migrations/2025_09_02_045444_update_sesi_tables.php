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
        // Create sesi_templates table if it doesn't exist
        if (!Schema::hasTable('sesi_templates')) {
            Schema::create('sesi_templates', function (Blueprint $table) {
                $table->id();
                $table->string('kode_sesi', 50)->nullable();
                $table->string('nama_sesi', 100);
                $table->string('deskripsi')->nullable();
                $table->time('waktu_mulai');
                $table->time('waktu_selesai');
                $table->string('status')->default('belum_mulai');
                $table->json('pengaturan')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Add template_id to sesi_ruangan if it doesn't exist
        if (Schema::hasTable('sesi_ruangan') && !Schema::hasColumn('sesi_ruangan', 'template_id')) {
            Schema::table('sesi_ruangan', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('pengawas_id')
                    ->constrained('sesi_templates')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key first if it exists
        if (Schema::hasTable('sesi_ruangan') && Schema::hasColumn('sesi_ruangan', 'template_id')) {
            Schema::table('sesi_ruangan', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }

        // Drop the templates table
        Schema::dropIfExists('sesi_templates');
    }
};
