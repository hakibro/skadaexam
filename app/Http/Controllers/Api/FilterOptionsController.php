<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PaketUjian;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;

class FilterOptionsController extends Controller
{
    public function index(Request $request)
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);

        $kelas = Kelas::query()
            ->select('id', 'nama_kelas', 'tingkat', 'jurusan')
            ->forTahunAjaran($tahunAjaranId)
            ->orderByRaw("FIELD(tingkat, 'X', 'XI', 'XII')")
            ->orderBy('nama_kelas')
            ->get();

        $ruangan = Ruangan::query()
            ->select('id', 'kode_ruangan', 'nama_ruangan', 'kapasitas', 'status')
            ->forTahunAjaran($tahunAjaranId)
            ->orderBy('nama_ruangan')
            ->get();

        $namaSesi = SesiRuangan::query()
            ->select('nama_sesi')
            ->forTahunAjaran($tahunAjaranId)
            ->whereNotNull('nama_sesi')
            ->distinct()
            ->orderBy('nama_sesi')
            ->pluck('nama_sesi')
            ->values();

        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $paketUjians = PaketUjian::when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tahun_ajaran' => $tahunAjarans->map(fn($item) => [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'nama' => $item->nama,
                    'status' => $item->status,
                    'is_active' => $item->is_active,
                ])->values(),
                'paket_ujian' => $paketUjians->map(fn($item) => [
                    'id' => $item->id,
                    'tahun_ajaran_id' => $item->tahun_ajaran_id,
                    'nama' => $item->nama,
                    'status' => $item->status,
                ])->values(),
                'tingkat' => $kelas->pluck('tingkat')
                    ->filter()
                    ->unique()
                    ->values(),
                'kelas' => $kelas->map(fn($item) => [
                    'id' => $item->id,
                    'nama_kelas' => $item->nama_kelas,
                    'tingkat' => $item->tingkat,
                    'jurusan' => $item->jurusan,
                ])->values(),
                'jurusan' => $kelas->pluck('jurusan')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values(),
                'ruangan' => $ruangan->map(fn($item) => [
                    'id' => $item->id,
                    'kode_ruangan' => $item->kode_ruangan,
                    'nama_ruangan' => $item->nama_ruangan,
                    'kapasitas' => $item->kapasitas,
                    'status' => $item->status,
                ])->values(),
                'nama_sesi_ruangan' => $namaSesi,
                'status_kehadiran' => [
                    ['value' => 'hadir', 'label' => 'Hadir'],
                    ['value' => 'tidak_hadir', 'label' => 'Tidak Hadir'],
                    ['value' => 'sakit', 'label' => 'Sakit'],
                    ['value' => 'izin', 'label' => 'Izin'],
                ],
            ],
        ]);
    }
}
