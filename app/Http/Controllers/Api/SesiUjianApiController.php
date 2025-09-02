<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\JadwalUjian;
use Illuminate\Http\Request;

class SesiUjianApiController extends Controller
{
    /**
     * Get available sesi for selection
     */
    public function getAvailableSesi(Request $request)
    {
        $query = SesiUjian::with(['ruangan', 'pengawas']);

        // Exclude sessions from a specific jadwal if provided
        if ($request->has('exclude_jadwal') && $request->exclude_jadwal != '') {
            $query->where('jadwal_ujian_id', '!=', $request->exclude_jadwal);
        }

        $sesiUjians = $query->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sesi) {
                return [
                    'id' => $sesi->id,
                    'nama_sesi' => $sesi->nama_sesi,
                    'waktu_mulai' => $sesi->waktu_mulai->format('H:i'),
                    'waktu_selesai' => $sesi->waktu_selesai->format('H:i'),
                    'kapasitas_maksimal' => $sesi->kapasitas_maksimal,
                    'peserta_terdaftar' => $sesi->peserta_terdaftar,
                    'ruangan' => $sesi->ruangan ? [
                        'nama_ruangan' => $sesi->ruangan->nama_ruangan,
                        'kapasitas' => $sesi->ruangan->kapasitas,
                    ] : null,
                    'pengawas' => $sesi->pengawas ? [
                        'nama' => $sesi->pengawas->nama,
                    ] : null,
                    'status' => $sesi->status,
                ];
            });

        return response()->json($sesiUjians);
    }
}
