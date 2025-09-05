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
        Schema::table('soal', function (Blueprint $table) {
            // Add missing columns based on the Soal model
            if (!Schema::hasColumn('soal', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')
                    ->references('id')->on('soal')->onDelete('set null');
            }

            if (!Schema::hasColumn('soal', 'is_parent')) {
                $table->boolean('is_parent')->default(false)->after('bank_soal_id');
            }

            if (!Schema::hasColumn('soal', 'tipe')) {
                $table->string('tipe', 20)->default('pg')->after('kode');
            }

            if (!Schema::hasColumn('soal', 'kunci_jawaban')) {
                $table->text('kunci_jawaban')->nullable()->after('pilihan');
            }

            if (!Schema::hasColumn('soal', 'pembahasan')) {
                $table->text('pembahasan')->nullable()->after('kunci_jawaban');
            }

            if (!Schema::hasColumn('soal', 'status')) {
                $table->enum('status', ['aktif', 'draft', 'arsip'])->default('aktif')->after('bobot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            // Drop columns in reverse order
            if (Schema::hasColumn('soal', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('soal', 'pembahasan')) {
                $table->dropColumn('pembahasan');
            }

            if (Schema::hasColumn('soal', 'kunci_jawaban')) {
                $table->dropColumn('kunci_jawaban');
            }

            if (Schema::hasColumn('soal', 'tipe')) {
                $table->dropColumn('tipe');
            }

            if (Schema::hasColumn('soal', 'is_parent')) {
                $table->dropColumn('is_parent');
            }

            if (Schema::hasColumn('soal', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
