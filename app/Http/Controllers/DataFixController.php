<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcaraUjian;
use App\Models\EnrollmentUjian;
use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataFixController extends Controller
{
    /**
     * Fix data associations between models
     */
    public function fixAssociations(Request $request)
    {
        $output = [];

        try {
            DB::beginTransaction();

            // Fix SesiRuangan jadwal_ujian associations by looking at pengaturan
            $output['sesiRuangan'] = $this->fixSesiRuanganAssociations();

            // Fix BeritaAcaraUjian associations
            $output['beritaAcara'] = $this->fixBeritaAcaraAssociations();

            // Fix EnrollmentUjian associations
            $output['enrollment'] = $this->fixEnrollmentAssociations();

            // Fix HasilUjian associations
            $output['hasil'] = $this->fixHasilUjianAssociations();

            DB::commit();
            $status = 'success';
            $message = 'Data fixed successfully';
        } catch (\Exception $e) {
            DB::rollback();
            $status = 'error';
            $message = 'Error: ' . $e->getMessage();
            $output['error'] = $e->getMessage();
            $output['trace'] = $e->getTraceAsString();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $output
            ]);
        }

        return view('admin.data-fix-result', [
            'status' => $status,
            'message' => $message,
            'output' => $output
        ]);
    }

    /**
     * Fix SesiRuangan associations
     */
    private function fixSesiRuanganAssociations()
    {
        $sesiRuanganList = SesiRuangan::whereNotNull('pengaturan')->get();
        $fixed = 0;

        foreach ($sesiRuanganList as $sesiRuangan) {
            $pengaturan = $sesiRuangan->pengaturan;
            if (is_string($pengaturan)) {
                $pengaturan = json_decode($pengaturan, true);
            }

            if (!empty($pengaturan) && isset($pengaturan['jadwal_ujian_id'])) {
                $jadwalUjianId = $pengaturan['jadwal_ujian_id'];

                // Add to berita_acara if exists
                $beritaAcara = BeritaAcaraUjian::where('sesi_ruangan_id', $sesiRuangan->id)->first();
                if ($beritaAcara) {
                    $fixed++;
                }
            }
        }

        return ['processed' => count($sesiRuanganList), 'fixed' => $fixed];
    }

    /**
     * Fix BeritaAcaraUjian associations
     */
    private function fixBeritaAcaraAssociations()
    {
        $beritaAcaraList = BeritaAcaraUjian::all();
        $fixed = 0;

        foreach ($beritaAcaraList as $beritaAcara) {
            // Calculate attendance
            if ($beritaAcara->sesi_ruangan_id) {
                $beritaAcara->calculateAttendance();
                $fixed++;
            }
        }

        return ['processed' => count($beritaAcaraList), 'fixed' => $fixed];
    }

    /**
     * Fix EnrollmentUjian associations
     */
    private function fixEnrollmentAssociations()
    {
        $enrollmentList = EnrollmentUjian::all();
        $fixed = 0;

        foreach ($enrollmentList as $enrollment) {
            if ($enrollment->sesi_ruangan_id) {
                $sesiRuangan = SesiRuangan::find($enrollment->sesi_ruangan_id);
                if ($sesiRuangan) {
                    $pengaturan = $sesiRuangan->pengaturan;
                    if (is_string($pengaturan)) {
                        $pengaturan = json_decode($pengaturan, true);
                    }

                    if (!empty($pengaturan) && isset($pengaturan['jadwal_ujian_id'])) {
                        $enrollment->jadwal_ujian_id = $pengaturan['jadwal_ujian_id'];
                        $enrollment->save();
                        $fixed++;
                    }
                }

                // Create SesiRuanganSiswa if it doesn't exist
                $existingSRS = SesiRuanganSiswa::where('sesi_ruangan_id', $enrollment->sesi_ruangan_id)
                    ->where('siswa_id', $enrollment->siswa_id)
                    ->first();

                if (!$existingSRS) {
                    // Determine status based on enrollment status
                    $status = 'tidak_hadir';
                    if ($enrollment->status_enrollment === 'completed') {
                        $status = 'hadir';
                    } elseif ($enrollment->token_digunakan_pada) {
                        $status = 'hadir';
                    }

                    SesiRuanganSiswa::create([
                        'sesi_ruangan_id' => $enrollment->sesi_ruangan_id,
                        'siswa_id' => $enrollment->siswa_id,
                        'status' => $status
                    ]);
                }
            }
        }

        return ['processed' => count($enrollmentList), 'fixed' => $fixed];
    }

    /**
     * Fix HasilUjian associations
     */
    private function fixHasilUjianAssociations()
    {
        $hasilList = HasilUjian::all();
        $fixed = 0;

        foreach ($hasilList as $hasil) {
            if (!$hasil->jadwal_ujian_id && $hasil->enrollment_ujian_id) {
                $enrollment = EnrollmentUjian::find($hasil->enrollment_ujian_id);
                if ($enrollment && $enrollment->jadwal_ujian_id) {
                    $hasil->jadwal_ujian_id = $enrollment->jadwal_ujian_id;
                    $hasil->save();
                    $fixed++;
                }
            }
        }

        return ['processed' => count($hasilList), 'fixed' => $fixed];
    }
}
