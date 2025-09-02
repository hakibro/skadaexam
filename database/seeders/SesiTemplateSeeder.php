<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SesiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the sesi_templates table if it doesn't exist
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
                $table->foreignId('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Add template_id to sesi_ruangan if it doesn't exist
        if (Schema::hasTable('sesi_ruangan') && !Schema::hasColumn('sesi_ruangan', 'template_id')) {
            Schema::table('sesi_ruangan', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('pengawas_id');
            });
        }

        // Create sample templates
        $templates = [
            [
                'kode_sesi' => 'TEMP-PAGI',
                'nama_sesi' => 'Sesi Pagi',
                'deskripsi' => 'Template untuk sesi pagi 08:00 - 10:00',
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '10:00',
                'status' => 'belum_mulai',
                'is_active' => true,
            ],
            [
                'kode_sesi' => 'TEMP-SIANG',
                'nama_sesi' => 'Sesi Siang',
                'deskripsi' => 'Template untuk sesi siang 11:00 - 13:00',
                'waktu_mulai' => '11:00',
                'waktu_selesai' => '13:00',
                'status' => 'belum_mulai',
                'is_active' => true,
            ],
            [
                'kode_sesi' => 'TEMP-SORE',
                'nama_sesi' => 'Sesi Sore',
                'deskripsi' => 'Template untuk sesi sore 14:00 - 16:00',
                'waktu_mulai' => '14:00',
                'waktu_selesai' => '16:00',
                'status' => 'belum_mulai',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            \App\Models\SesiTemplate::updateOrCreate(
                ['kode_sesi' => $template['kode_sesi']],
                $template
            );
        }
    }
}
