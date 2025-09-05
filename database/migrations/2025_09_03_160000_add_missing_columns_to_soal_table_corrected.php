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
            // Add parent_id if it doesn't exist
            if (!Schema::hasColumn('soal', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')
                    ->references('id')->on('soal')->onDelete('set null');
            }

            // Add is_parent if it doesn't exist
            if (!Schema::hasColumn('soal', 'is_parent')) {
                $table->boolean('is_parent')->default(false)->after('bank_soal_id');
            }

            // Add tipe if it doesn't exist (not after kode, but after is_parent)
            if (!Schema::hasColumn('soal', 'tipe')) {
                $table->string('tipe', 20)->default('pg')->after('is_parent');
            }

            // Add tipe_soal if it doesn't exist
            if (!Schema::hasColumn('soal', 'tipe_soal')) {
                $table->string('tipe_soal', 20)->default('pilihan_ganda')->after('tipe');
            }

            // Add status if it doesn't exist
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

            if (Schema::hasColumn('soal', 'tipe_soal')) {
                $table->dropColumn('tipe_soal');
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
