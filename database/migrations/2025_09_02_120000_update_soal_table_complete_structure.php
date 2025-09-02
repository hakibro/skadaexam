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
        // Step 1: Add new columns for temporary storage
        Schema::table('soal', function (Blueprint $table) {
            $table->string('temp_nomor_soal')->nullable()->after('nomor');
            $table->string('temp_kategori')->nullable()->after('tingkat_kesulitan');
            $table->json('temp_display_settings')->nullable()->after('metadata');
            $table->decimal('temp_bobot', 5, 2)->default(1.00)->after('bobot');
            
            // Create temp columns for pilihan
            $table->text('temp_pilihan_a_teks')->nullable()->after('pilihan_a');
            $table->text('temp_pilihan_b_teks')->nullable()->after('pilihan_b');
            $table->text('temp_pilihan_c_teks')->nullable()->after('pilihan_c');
            $table->text('temp_pilihan_d_teks')->nullable()->after('pilihan_d');
            $table->text('temp_pilihan_e_teks')->nullable()->after('pilihan_e');
        });

        // Step 2: Migrate data to temp columns
        DB::statement('UPDATE soal SET temp_nomor_soal = nomor');
        DB::statement('UPDATE soal SET temp_kategori = tingkat_kesulitan');
        DB::statement('UPDATE soal SET temp_display_settings = metadata');
        DB::statement('UPDATE soal SET temp_bobot = bobot');
        
        DB::statement('UPDATE soal SET temp_pilihan_a_teks = pilihan_a');
        DB::statement('UPDATE soal SET temp_pilihan_b_teks = pilihan_b');
        DB::statement('UPDATE soal SET temp_pilihan_c_teks = pilihan_c');
        DB::statement('UPDATE soal SET temp_pilihan_d_teks = pilihan_d');
        DB::statement('UPDATE soal SET temp_pilihan_e_teks = pilihan_e');

        // Step 3: Drop old columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('nomor');
            $table->dropColumn('tingkat_kesulitan');
            $table->dropColumn('metadata');
            $table->dropColumn('bobot');
            
            $table->dropColumn('pilihan_a');
            $table->dropColumn('pilihan_b');
            $table->dropColumn('pilihan_c');
            $table->dropColumn('pilihan_d');
            $table->dropColumn('pilihan_e');
        });

        // Step 4: Add new columns with correct names
        Schema::table('soal', function (Blueprint $table) {
            $table->integer('nomor_soal')->default(1)->after('bank_soal_id');
            $table->string('kategori', 20)->default('sedang')->after('bank_soal_id');
            $table->json('display_settings')->nullable()->after('kategori');
            $table->decimal('bobot', 5, 2)->default(1.00)->after('kunci_jawaban');
            
            $table->text('pilihan_a_teks')->nullable()->after('tipe_pertanyaan');
            $table->text('pilihan_b_teks')->nullable()->after('pilihan_a_teks');
            $table->text('pilihan_c_teks')->nullable()->after('pilihan_b_teks');
            $table->text('pilihan_d_teks')->nullable()->after('pilihan_c_teks');
            $table->text('pilihan_e_teks')->nullable()->after('pilihan_d_teks');
        });

        // Step 5: Migrate data from temp columns to new columns
        DB::statement('UPDATE soal SET nomor_soal = temp_nomor_soal');
        DB::statement('UPDATE soal SET kategori = temp_kategori');
        DB::statement('UPDATE soal SET display_settings = temp_display_settings');
        DB::statement('UPDATE soal SET bobot = temp_bobot');
        
        DB::statement('UPDATE soal SET pilihan_a_teks = temp_pilihan_a_teks');
        DB::statement('UPDATE soal SET pilihan_b_teks = temp_pilihan_b_teks');
        DB::statement('UPDATE soal SET pilihan_c_teks = temp_pilihan_c_teks');
        DB::statement('UPDATE soal SET pilihan_d_teks = temp_pilihan_d_teks');
        DB::statement('UPDATE soal SET pilihan_e_teks = temp_pilihan_e_teks');

        // Step 6: Drop temp columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('temp_nomor_soal');
            $table->dropColumn('temp_kategori');
            $table->dropColumn('temp_display_settings');
            $table->dropColumn('temp_bobot');
            
            $table->dropColumn('temp_pilihan_a_teks');
            $table->dropColumn('temp_pilihan_b_teks');
            $table->dropColumn('temp_pilihan_c_teks');
            $table->dropColumn('temp_pilihan_d_teks');
            $table->dropColumn('temp_pilihan_e_teks');
        });

        // Step 7: Add image and type columns for options
        Schema::table('soal', function (Blueprint $table) {
            $table->string('pilihan_a_gambar')->nullable()->after('pilihan_a_teks');
            $table->string('pilihan_a_tipe', 20)->default('teks')->after('pilihan_a_gambar');
            
            $table->string('pilihan_b_gambar')->nullable()->after('pilihan_b_teks');
            $table->string('pilihan_b_tipe', 20)->default('teks')->after('pilihan_b_gambar');
            
            $table->string('pilihan_c_gambar')->nullable()->after('pilihan_c_teks');
            $table->string('pilihan_c_tipe', 20)->default('teks')->after('pilihan_c_gambar');
            
            $table->string('pilihan_d_gambar')->nullable()->after('pilihan_d_teks');
            $table->string('pilihan_d_tipe', 20)->default('teks')->after('pilihan_d_gambar');
            
            $table->string('pilihan_e_gambar')->nullable()->after('pilihan_e_teks');
            $table->string('pilihan_e_tipe', 20)->default('teks')->after('pilihan_e_gambar');

            // Add pembahasan fields
            $table->string('pembahasan_gambar')->nullable()->after('pembahasan_teks');
            $table->string('pembahasan_tipe', 20)->default('teks')->after('pembahasan_gambar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add temp columns
        Schema::table('soal', function (Blueprint $table) {
            $table->integer('temp_nomor')->default(1)->after('nomor_soal');
            $table->string('temp_tingkat_kesulitan', 20)->default('sedang')->after('kategori');
            $table->json('temp_metadata')->nullable()->after('display_settings');
            $table->integer('temp_bobot')->default(1)->after('bobot');
            
            $table->text('temp_pilihan_a')->nullable();
            $table->text('temp_pilihan_b')->nullable();
            $table->text('temp_pilihan_c')->nullable();
            $table->text('temp_pilihan_d')->nullable();
            $table->text('temp_pilihan_e')->nullable();
        });

        // Step 2: Migrate data to temp columns
        DB::statement('UPDATE soal SET temp_nomor = nomor_soal');
        DB::statement('UPDATE soal SET temp_tingkat_kesulitan = kategori');
        DB::statement('UPDATE soal SET temp_metadata = display_settings');
        DB::statement('UPDATE soal SET temp_bobot = bobot');
        
        DB::statement('UPDATE soal SET temp_pilihan_a = pilihan_a_teks');
        DB::statement('UPDATE soal SET temp_pilihan_b = pilihan_b_teks');
        DB::statement('UPDATE soal SET temp_pilihan_c = pilihan_c_teks');
        DB::statement('UPDATE soal SET temp_pilihan_d = pilihan_d_teks');
        DB::statement('UPDATE soal SET temp_pilihan_e = pilihan_e_teks');

        // Step 3: Drop new columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn([
                'pilihan_a_gambar', 'pilihan_a_tipe',
                'pilihan_b_gambar', 'pilihan_b_tipe',
                'pilihan_c_gambar', 'pilihan_c_tipe',
                'pilihan_d_gambar', 'pilihan_d_tipe',
                'pilihan_e_gambar', 'pilihan_e_tipe',
                'pembahasan_gambar', 'pembahasan_tipe'
            ]);
        });
        
        // Step 4: Drop renamed columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('nomor_soal');
            $table->dropColumn('kategori');
            $table->dropColumn('display_settings');
            $table->dropColumn('bobot');
            
            $table->dropColumn('pilihan_a_teks');
            $table->dropColumn('pilihan_b_teks');
            $table->dropColumn('pilihan_c_teks');
            $table->dropColumn('pilihan_d_teks');
            $table->dropColumn('pilihan_e_teks');
        });

        // Step 5: Add original columns back
        Schema::table('soal', function (Blueprint $table) {
            $table->integer('nomor')->default(1)->after('bank_soal_id');
            $table->string('tingkat_kesulitan', 20)->default('sedang')->after('bank_soal_id');
            $table->json('metadata')->nullable()->after('tingkat_kesulitan');
            $table->integer('bobot')->default(1)->after('kunci_jawaban');
            
            $table->text('pilihan_a')->nullable();
            $table->text('pilihan_b')->nullable();
            $table->text('pilihan_c')->nullable();
            $table->text('pilihan_d')->nullable();
            $table->text('pilihan_e')->nullable();
        });

        // Step 6: Migrate data back
        DB::statement('UPDATE soal SET nomor = temp_nomor');
        DB::statement('UPDATE soal SET tingkat_kesulitan = temp_tingkat_kesulitan');
        DB::statement('UPDATE soal SET metadata = temp_metadata');
        DB::statement('UPDATE soal SET bobot = temp_bobot');
        
        DB::statement('UPDATE soal SET pilihan_a = temp_pilihan_a');
        DB::statement('UPDATE soal SET pilihan_b = temp_pilihan_b');
        DB::statement('UPDATE soal SET pilihan_c = temp_pilihan_c');
        DB::statement('UPDATE soal SET pilihan_d = temp_pilihan_d');
        DB::statement('UPDATE soal SET pilihan_e = temp_pilihan_e');

        // Step 7: Drop temp columns
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn('temp_nomor');
            $table->dropColumn('temp_tingkat_kesulitan');
            $table->dropColumn('temp_metadata');
            $table->dropColumn('temp_bobot');
            
            $table->dropColumn('temp_pilihan_a');
            $table->dropColumn('temp_pilihan_b');
            $table->dropColumn('temp_pilihan_c');
            $table->dropColumn('temp_pilihan_d');
            $table->dropColumn('temp_pilihan_e');
        });
    }
};
