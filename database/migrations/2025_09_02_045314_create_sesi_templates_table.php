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
        // Check if table already exists from the previous migration
        if (!Schema::hasTable('sesi_templates')) {
            Schema::create('sesi_templates', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('deskripsi')->nullable();
                $table->time('waktu_mulai');
                $table->time('waktu_selesai');
                $table->boolean('is_active')->default(true);
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            // Check if these columns exist and add them if they don't
            if (
                !Schema::hasColumn('sesi_templates', 'nama') &&
                Schema::hasColumn('sesi_templates', 'nama_sesi')
            ) {
                // The table exists but with different column names
                // Just mark this migration as run without modifying the table
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_templates');
    }
};
