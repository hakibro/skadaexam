<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Rename nomor to nomor_soal for consistency
        Schema::table('soal', function (Blueprint $table) {
            $table->renameColumn('nomor', 'nomor_soal');
        });

        // Step 2: Add tipe_pertanyaan column and update fields for images
        Schema::table('soal', function (Blueprint $table) {
            // Add tipe_pertanyaan to specify format (teks, gambar, teks_gambar)
            $table->string('tipe_pertanyaan', 20)->default('teks')->after('pertanyaan');

            // Add fields for pilihan options and their types
            $table->text('pilihan_a_teks')->nullable()->after('pilihan');
            $table->string('pilihan_a_gambar')->nullable()->after('pilihan_a_teks');
            $table->string('pilihan_a_tipe', 20)->default('teks')->after('pilihan_a_gambar');

            $table->text('pilihan_b_teks')->nullable()->after('pilihan_a_tipe');
            $table->string('pilihan_b_gambar')->nullable()->after('pilihan_b_teks');
            $table->string('pilihan_b_tipe', 20)->default('teks')->after('pilihan_b_gambar');

            $table->text('pilihan_c_teks')->nullable()->after('pilihan_b_tipe');
            $table->string('pilihan_c_gambar')->nullable()->after('pilihan_c_teks');
            $table->string('pilihan_c_tipe', 20)->default('teks')->after('pilihan_c_gambar');

            $table->text('pilihan_d_teks')->nullable()->after('pilihan_c_tipe');
            $table->string('pilihan_d_gambar')->nullable()->after('pilihan_d_teks');
            $table->string('pilihan_d_tipe', 20)->default('teks')->after('pilihan_d_gambar');

            $table->text('pilihan_e_teks')->nullable()->after('pilihan_d_tipe');
            $table->string('pilihan_e_gambar')->nullable()->after('pilihan_e_teks');
            $table->string('pilihan_e_tipe', 20)->default('teks')->after('pilihan_e_gambar');

            // Add fields for pembahasan tipe
            $table->text('pembahasan_teks')->nullable()->after('pembahasan');
            $table->string('pembahasan_gambar')->nullable()->after('pembahasan_teks');
            $table->string('pembahasan_tipe', 20)->default('teks')->after('pembahasan_gambar');

            // Rename tingkat_kesulitan to kategori for broader categorization
            $table->renameColumn('tingkat_kesulitan', 'kategori');

            // Replace metadata with display_settings 
            $table->renameColumn('metadata', 'display_settings');

            // Change bobot to decimal for more precise scoring
            $table->decimal('bobot', 5, 2)->default(1.00)->change();
        });

        // Step 3: Migrate data from pilihan JSON to individual fields
        DB::statement('
            UPDATE soal 
            SET 
                pilihan_a_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.A.text")),
                pilihan_b_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.B.text")),
                pilihan_c_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.C.text")),
                pilihan_d_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.D.text")),
                pilihan_e_teks = JSON_UNQUOTE(JSON_EXTRACT(pilihan, "$.E.text"))
        ');

        // Step 4: Migrate pembahasan to pembahasan_teks
        DB::statement('UPDATE soal SET pembahasan_teks = pembahasan');

        // Step 5: Drop the old columns that are no longer needed
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('pilihan');
            $table->dropColumn('pembahasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back the old columns
        Schema::table('soal', function (Blueprint $table) {
            $table->json('pilihan')->nullable()->after('gambar_pertanyaan');
            $table->text('pembahasan')->nullable()->after('kunci_jawaban');
        });

        // Step 2: Migrate data back to pilihan JSON format
        DB::statement('
            UPDATE soal 
            SET pilihan = JSON_OBJECT(
                "A", JSON_OBJECT("text", pilihan_a_teks),
                "B", JSON_OBJECT("text", pilihan_b_teks),
                "C", JSON_OBJECT("text", pilihan_c_teks),
                "D", JSON_OBJECT("text", pilihan_d_teks),
                "E", JSON_OBJECT("text", pilihan_e_teks)
            )
        ');

        // Step 3: Migrate pembahasan_teks back to pembahasan
        DB::statement('UPDATE soal SET pembahasan = pembahasan_teks');

        // Step 4: Drop the new columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('tipe_pertanyaan');
            $table->dropColumn('pilihan_a_teks');
            $table->dropColumn('pilihan_a_gambar');
            $table->dropColumn('pilihan_a_tipe');
            $table->dropColumn('pilihan_b_teks');
            $table->dropColumn('pilihan_b_gambar');
            $table->dropColumn('pilihan_b_tipe');
            $table->dropColumn('pilihan_c_teks');
            $table->dropColumn('pilihan_c_gambar');
            $table->dropColumn('pilihan_c_tipe');
            $table->dropColumn('pilihan_d_teks');
            $table->dropColumn('pilihan_d_gambar');
            $table->dropColumn('pilihan_d_tipe');
            $table->dropColumn('pilihan_e_teks');
            $table->dropColumn('pilihan_e_gambar');
            $table->dropColumn('pilihan_e_tipe');
            $table->dropColumn('pembahasan_teks');
            $table->dropColumn('pembahasan_tipe');
        });

        // Step 5: Revert the column renames and changes
        Schema::table('soal', function (Blueprint $table) {
            $table->renameColumn('nomor_soal', 'nomor');
            $table->renameColumn('kategori', 'tingkat_kesulitan');
            $table->renameColumn('display_settings', 'metadata');
            $table->integer('bobot')->default(1)->change();
        });
    }
};
