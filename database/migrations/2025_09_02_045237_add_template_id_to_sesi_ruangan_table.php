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
        // Create the sesi_templates table first if it doesn't exist
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

        // Then add template_id column to sesi_ruangan
        if (!Schema::hasColumn('sesi_ruangan', 'template_id')) {
            Schema::table('sesi_ruangan', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('pengawas_id');

                // Now safely add the foreign key constraint
                if (Schema::hasTable('sesi_templates')) {
                    $table->foreign('template_id')
                        ->references('id')
                        ->on('sesi_templates')
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sesi_ruangan') && Schema::hasColumn('sesi_ruangan', 'template_id')) {
            Schema::table('sesi_ruangan', function (Blueprint $table) {
                if (Schema::hasTable('sesi_templates')) {
                    $table->dropForeign(['template_id']);
                }
                $table->dropColumn('template_id');
            });
        }

        // Do not drop the sesi_templates table here since other migrations might depend on it
    }
};
