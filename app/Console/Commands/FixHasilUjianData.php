<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HasilUjian;
use App\Models\JawabanSiswa;
use Illuminate\Support\Facades\Log;

class FixHasilUjianData extends Command
{
    protected $signature = 'fix:hasil-ujian';
    protected $description = 'Fix missing data in hasil_ujian table';

    public function handle()
    {
        $this->info('Starting to fix hasil_ujian data...');

        // Get all hasil_ujian with missing data
        $hasilUjians = HasilUjian::where(function ($query) {
            $query->whereNull('sesi_ruangan_id')
                ->orWhereNull('durasi_menit')
                ->orWhere('jumlah_soal', 0)
                ->orWhere('is_final', 0)
                ->where('status', 'selesai');
        })
            ->get();

        $this->info("Found {$hasilUjians->count()} hasil ujian records to fix");

        foreach ($hasilUjians as $hasil) {
            $this->info("Processing hasil ujian ID: {$hasil->id}");

            try {
                // Get enrollment to find sesi_ruangan_id
                $enrollment = $hasil->enrollment;
                $sesiRuanganId = $enrollment ? $enrollment->sesi_ruangan_id : null;

                // Calculate durasi_menit if we have waktu_mulai and waktu_selesai
                $durasiMenit = null;
                if ($hasil->waktu_mulai && $hasil->waktu_selesai) {
                    $durasiMenit = $hasil->waktu_selesai->diffInMinutes($hasil->waktu_mulai);
                } else if ($hasil->jadwalUjian) {
                    $durasiMenit = $hasil->jadwalUjian->durasi_menit;
                }

                // Count jumlah_soal from bank_soal if available
                $jumlahSoal = 0;
                if ($hasil->jadwalUjian && $hasil->jadwalUjian->bank_soal_id) {
                    $jumlahSoal = \App\Models\SoalUjian::where('bank_soal_id', $hasil->jadwalUjian->bank_soal_id)
                        ->where('status', 'aktif')
                        ->count();
                }

                // Count jumlah_dijawab
                $jumlahDijawab = JawabanSiswa::where('hasil_ujian_id', $hasil->id)
                    ->whereNotNull('jawaban')
                    ->count();

                // Count jumlah_benar and jumlah_salah
                $jumlahBenar = 0;
                $jumlahSalah = 0;
                $jawabanList = JawabanSiswa::with('soalUjian')
                    ->where('hasil_ujian_id', $hasil->id)
                    ->whereNotNull('jawaban')
                    ->get();

                foreach ($jawabanList as $jwb) {
                    if ($jwb->soalUjian && $jwb->jawaban === $jwb->soalUjian->kunci_jawaban) {
                        $jumlahBenar++;
                    } else {
                        $jumlahSalah++;
                    }
                }

                // Calculate nilai (percentage) and lulus status
                $nilai = $jumlahSoal > 0 ? ($jumlahBenar / $jumlahSoal) * 100 : 0;

                // Determine KKM from jadwal_ujian settings or use 75 as default
                $kkm = 75;
                if ($hasil->jadwalUjian && isset($hasil->jadwalUjian->pengaturan['kkm'])) {
                    $kkm = $hasil->jadwalUjian->pengaturan['kkm'];
                }

                $lulus = $nilai >= $kkm;

                // Update hasil ujian
                $updateData = [
                    'is_final' => true,
                    'jumlah_benar' => $jumlahBenar,
                    'jumlah_salah' => $jumlahSalah,
                    'nilai' => $nilai,
                    'lulus' => $lulus
                ];

                if ($sesiRuanganId) {
                    $updateData['sesi_ruangan_id'] = $sesiRuanganId;
                }

                if ($durasiMenit) {
                    $updateData['durasi_menit'] = $durasiMenit;
                }

                if ($jumlahSoal > 0) {
                    $updateData['jumlah_soal'] = $jumlahSoal;
                    $updateData['jumlah_tidak_dijawab'] = $jumlahSoal - $jumlahDijawab;
                }

                if ($jumlahDijawab > 0) {
                    $updateData['jumlah_dijawab'] = $jumlahDijawab;
                }
                $hasil->update($updateData);
                $this->info("Fixed hasil ujian ID: {$hasil->id}");
            } catch (\Exception $e) {
                $this->error("Error fixing hasil ujian ID: {$hasil->id}");
                $this->error($e->getMessage());
                Log::error("Error fixing hasil ujian ID: {$hasil->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Completed fixing hasil ujian data");
    }
}
