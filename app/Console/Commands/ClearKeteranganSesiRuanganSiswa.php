<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearKeteranganSesiRuanganSiswa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-keterangan-sesi-ruangan-siswa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('sesi_ruangan_siswa')
            ->whereNotNull('keterangan')
            ->update(['keterangan' => null]);

        $this->info('Kolom keterangan berhasil dikosongkan.');
    }
}
