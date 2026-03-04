<?php

namespace App\Imports;

use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\Siswa;
use App\Models\SesiRuangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EnrollmentImport implements ToCollection, WithHeadingRow
{
    protected $success = 0;
    protected $failed = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ambil data dari baris
            $idperson = trim($row['idperson'] ?? '');
            $kodeJadwal = trim($row['kode_jadwal'] ?? '');

            if (empty($idperson) || empty($kodeJadwal)) {
                $this->failed++;
                $this->errors[] = "Baris dengan idperson '$idperson' dan kode_jadwal '$kodeJadwal' tidak lengkap, dilewati.";
                continue;
            }

            DB::beginTransaction();
            try {
                // Cari siswa berdasarkan idyayasan atau nis
                $siswa = Siswa::where('idyayasan', $idperson)
                    ->orWhere('nis', $idperson)
                    ->first();

                if (!$siswa) {
                    $this->failed++;
                    $this->errors[] = "Siswa dengan idperson '$idperson' tidak ditemukan.";
                    DB::rollBack();
                    continue;
                }

                // Cari jadwal berdasarkan kode_ujian
                $jadwal = JadwalUjian::where('kode_ujian', $kodeJadwal)->first();

                if (!$jadwal) {
                    $this->failed++;
                    $this->errors[] = "Jadwal dengan kode '$kodeJadwal' tidak ditemukan (untuk siswa {$siswa->nama}).";
                    DB::rollBack();
                    continue;
                }

                // Cek apakah sudah terdaftar di enrollment
                $existing = EnrollmentUjian::where('siswa_id', $siswa->id)
                    ->where('jadwal_ujian_id', $jadwal->id)
                    ->exists();

                if ($existing) {
                    $this->failed++;
                    $this->errors[] = "Siswa {$siswa->nama} sudah terdaftar di jadwal {$jadwal->judul}.";
                    DB::rollBack();
                    continue;
                }

                // Cari sesi ruangan yang terhubung dengan jadwal dan dimiliki siswa
                $sesi = SesiRuangan::whereHas('jadwalUjians', function ($q) use ($jadwal) {
                    $q->where('jadwal_ujian.id', $jadwal->id);
                })
                    ->whereHas('sesiRuanganSiswa', function ($q) use ($siswa) {
                        $q->where('siswa_id', $siswa->id);
                    })
                    ->first();

                if (!$sesi) {
                    $this->failed++;
                    $this->errors[] = "Siswa {$siswa->nama} tidak memiliki sesi ruangan pada jadwal {$jadwal->judul}.";
                    DB::rollBack();
                    continue;
                }

                // Buat enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswa->id,
                    'jadwal_ujian_id' => $jadwal->id,
                    'sesi_ruangan_id' => $sesi->id,
                    'status_enrollment' => 'enrolled',
                ]);

                DB::commit();
                $this->success++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->failed++;
                $this->errors[] = "Error pada siswa idperson '$idperson': " . $e->getMessage();
                Log::error('Import Enrollment Error: ' . $e->getMessage());
            }
        }
    }

    public function getSuccessCount()
    {
        return $this->success;
    }

    public function getFailedCount()
    {
        return $this->failed;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}