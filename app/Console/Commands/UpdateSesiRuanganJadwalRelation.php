<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\JadwalUjian;
use Illuminate\Support\Facades\DB;

class UpdateSesiRuanganJadwalRelation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-sesi-ruangan-jadwal-relation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update jadwal_ujian_id column in sesi_ruangan table based on BeritaAcara relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update sesi_ruangan jadwal_ujian_id relationships...');

        // Since we don't have a direct way to get jadwal_ujian_id from BeritaAcaraUjian,
        // we'll update based on the relationship defined in JadwalUjian model
        $this->info('Updating from JadwalUjian relationships...');
        $count1 = 0;

        // Get all jadwal ujian
        $jadwalUjians = JadwalUjian::all();
        
        foreach ($jadwalUjians as $jadwal) {
            // Find all related sesi ruangan (this uses the relationship defined in JadwalUjian)
            $relatedSesiRuangan = DB::table('sesi_ruangan')
                ->whereNull('jadwal_ujian_id')
                ->whereRaw('sesi_ruangan.id IN (SELECT sesi_ruangan_id FROM berita_acara_ujian WHERE sesi_ruangan_id IS NOT NULL)')
                ->get();
                
            foreach ($relatedSesiRuangan as $sesi) {
                DB::table('sesi_ruangan')
                    ->where('id', $sesi->id)
                    ->update(['jadwal_ujian_id' => $jadwal->id]);
                $count1++;
            }
        }

        $this->info("Updated $count1 sesi_ruangan records.");

        // Step 2: Check for any sesi_ruangan without jadwal_ujian_id
        $this->info('Checking for any remaining sesi_ruangan without jadwal_ujian_id...');
        $remaining = SesiRuangan::whereNull('jadwal_ujian_id')->count();
        $this->info("$remaining sesi_ruangan records still need jadwal_ujian_id.");

        if ($remaining > 0) {
            // Direct approach - assign remaining sessions to the first jadwal
            if ($this->confirm('Do you want to assign all remaining sesi_ruangan records to a default jadwal?')) {
                $defaultJadwal = JadwalUjian::first();
                if ($defaultJadwal) {
                    $updated = DB::table('sesi_ruangan')
                        ->whereNull('jadwal_ujian_id')
                        ->update(['jadwal_ujian_id' => $defaultJadwal->id]);
                    
                    $this->info("Assigned $updated remaining sesi_ruangan records to jadwal ID: {$defaultJadwal->id}");
                } else {
                    $this->error('No default jadwal found.');
                }
            }
        }

        $this->info('Finished updating relationships.');
    }
}
