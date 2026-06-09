<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Clean up bank soal with UJI-VERIF kode
$banks = DB::table('bank_soal')->where('kode_bank', 'like', 'UJI-VERIF%')->get();
foreach ($banks as $b) {
    echo "Cleaning bank soal ID {$b->id}: {$b->kode_bank}\n";
    // Delete soal terkait
    $soals = DB::table('soal')->where('bank_soal_id', $b->id)->get();
    foreach ($soals as $s) {
        echo "  Deleting soal ID {$s->id}\n";
    }
    DB::table('soal')->where('bank_soal_id', $b->id)->delete();

    // Delete jadwal terkait
    $jadwals = DB::table('jadwal_ujian')->where('bank_soal_id', $b->id)->get();
    foreach ($jadwals as $j) {
        echo "  Deleting jadwal ID {$j->id}\n";
        // Delete hasil ujian terkait
        $hasil = DB::table('hasil_ujian')->where('jadwal_ujian_id', $j->id)->get();
        foreach ($hasil as $h) {
            DB::table('jawaban_siswa')->where('hasil_ujian_id', $h->id)->delete();
        }
        DB::table('hasil_ujian')->where('jadwal_ujian_id', $j->id)->delete();
    }
    DB::table('jadwal_ujian')->where('bank_soal_id', $b->id)->delete();

    // Delete bank soal
    DB::table('bank_soal')->where('id', $b->id)->delete();
}

// Clean up siswa uji verifikasi
$siswa = DB::table('siswa')->where('nama', 'like', '%Uji Verifikasi%')->get();
foreach ($siswa as $s) {
    echo "Cleaning siswa ID {$s->id}: {$s->nama}\n";
    // Delete enrollment
    $enrolls = DB::table('enrollment_ujian')->where('siswa_id', $s->id)->get();
    foreach ($enrolls as $e) {
        $hasil = DB::table('hasil_ujian')->where('enrollment_ujian_id', $e->id)->get();
        foreach ($hasil as $h) {
            DB::table('jawaban_siswa')->where('hasil_ujian_id', $h->id)->delete();
        }
        DB::table('hasil_ujian')->where('enrollment_ujian_id', $e->id)->delete();
    }
    DB::table('enrollment_ujian')->where('siswa_id', $s->id)->delete();

    // Delete model siswa (will use DB due to soft deletes)
    DB::table('siswa')->where('id', $s->id)->delete();
}

// Clean up ruangan uji verifikasi
$ruangan = DB::table('ruangan')->where('nama_ruangan', 'like', '%Uji Verifikasi%')->get();
foreach ($ruangan as $r) {
    echo "Cleaning ruangan ID {$r->id}: {$r->nama_ruangan}\n";
    $sesis = DB::table('sesi_ruangan')->where('ruangan_id', $r->id)->get();
    foreach ($sesis as $sesi) {
        $enrolls = DB::table('enrollment_ujian')->where('sesi_ruangan_id', $sesi->id)->get();
        foreach ($enrolls as $e) {
            $hasil = DB::table('hasil_ujian')->where('enrollment_ujian_id', $e->id)->get();
            foreach ($hasil as $h) {
                DB::table('jawaban_siswa')->where('hasil_ujian_id', $h->id)->delete();
            }
            DB::table('hasil_ujian')->where('enrollment_ujian_id', $e->id)->delete();
        }
        DB::table('enrollment_ujian')->where('sesi_ruangan_id', $sesi->id)->delete();
    }
    DB::table('sesi_ruangan')->where('ruangan_id', $r->id)->delete();
    DB::table('ruangan')->where('id', $r->id)->delete();
}

echo "Cleanup complete.\n";