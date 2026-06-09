<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\JadwalUjian;
use App\Models\PaketUjian;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PaketUjianController extends Controller
{
    public function index(Request $request, TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->active();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear?->id);
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        $paketUjians = PaketUjian::with(['tahunAjaran'])
            ->withCount(['jadwalUjian', 'bankSoals', 'ruangans', 'sesiRuangans'])
            ->when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->paginate(30)
            ->appends($request->query());

        return view('features.naskah.paket.index', compact('paketUjians', 'tahunAjarans', 'tahunAjaranId', 'activeYear'));
    }

    public function create(TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->active();
        if (!$activeYear) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.');
        }

        return view('features.naskah.paket.create', [
            'paketUjian' => new PaketUjian([
                'tahun_ajaran_id' => $activeYear->id,
                'tanggal_mulai' => $activeYear->tanggal_mulai,
                'tanggal_selesai' => $activeYear->tanggal_selesai,
                'status' => 'draft',
            ]),
            'activeYear' => $activeYear,
        ]);
    }

    public function store(Request $request, TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->ensureActive();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
        ]);

        $validated['tahun_ajaran_id'] = $activeYear->id;
        $paketUjian = PaketUjian::create($validated);

        return redirect()->route('naskah.paket-ujian.show', $paketUjian)
            ->with('success', 'Paket ujian berhasil dibuat.');
    }

    public function show(PaketUjian $paketUjian)
    {
        $paketUjian->load(['tahunAjaran', 'jadwalUjian.mapel']);

        return view('features.naskah.paket.show', compact('paketUjian'));
    }

    public function edit(PaketUjian $paketUjian)
    {
        if ($paketUjian->isReadOnly()) {
            return redirect()->route('naskah.paket-ujian.show', $paketUjian)
                ->with('error', 'Paket ujian arsip bersifat read-only.');
        }

        return view('features.naskah.paket.edit', compact('paketUjian'));
    }

    public function update(Request $request, PaketUjian $paketUjian)
    {
        if ($paketUjian->isReadOnly()) {
            return redirect()->route('naskah.paket-ujian.show', $paketUjian)
                ->with('error', 'Paket ujian arsip bersifat read-only.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
        ]);

        $paketUjian->update($validated);

        return redirect()->route('naskah.paket-ujian.show', $paketUjian)
            ->with('success', 'Paket ujian berhasil diperbarui.');
    }

    public function updateStatus(Request $request, PaketUjian $paketUjian)
    {
        if ($paketUjian->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Paket ujian pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,aktif,arsip',
        ]);

        $paketUjian->update($validated);

        return redirect()->back()
            ->with('success', 'Status paket ujian berhasil diperbarui.');
    }

    public function destroy(PaketUjian $paketUjian)
    {
        if ($paketUjian->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Paket ujian pada tahun ajaran arsip hanya dapat dilihat.');
        }

        if ($paketUjian->jadwalUjian()->exists()) {
            return redirect()->back()
                ->with('error', 'Paket ujian tidak dapat dihapus karena masih memiliki jadwal ujian.');
        }

        $paketUjian->delete();

        return redirect()->route('naskah.paket-ujian.index')
            ->with('success', 'Paket ujian berhasil dihapus.');
    }

    public function forceDestroy(Request $request, PaketUjian $paketUjian)
    {
        if ($paketUjian->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Paket ujian pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'force_delete' => 'accepted',
            'confirmation_name' => ['required', 'string', Rule::in([$paketUjian->nama])],
        ], [
            'force_delete.accepted' => 'Konfirmasi hapus paksa wajib dicentang.',
            'confirmation_name.in' => 'Nama paket yang diketik tidak sesuai.',
        ]);

        $impact = $this->getForceDeleteImpact($paketUjian);

        DB::transaction(function () use ($paketUjian, $impact) {
            $this->deletePaketRelatedData($impact);

            $paketUjian->delete();
        });

        return redirect()->route('naskah.paket-ujian.index')
            ->with('success', "Paket ujian '{$paketUjian->nama}' dan data terkait berhasil dihapus paksa.");
    }

    private function getForceDeleteImpact(PaketUjian $paketUjian): array
    {
        $jadwalIds = JadwalUjian::where('paket_ujian_id', $paketUjian->id)->pluck('id');
        $bankSoalIds = BankSoal::withTrashed()->where('paket_ujian_id', $paketUjian->id)->pluck('id');
        $ruanganIds = Ruangan::where('paket_ujian_id', $paketUjian->id)->pluck('id');

        $sesiIds = SesiRuangan::where('paket_ujian_id', $paketUjian->id)
            ->when($ruanganIds->isNotEmpty(), fn($query) => $query->orWhereIn('ruangan_id', $ruanganIds))
            ->pluck('id');

        if ($jadwalIds->isNotEmpty()) {
            $sesiFromJadwal = DB::table('jadwal_ujian_sesi_ruangan')
                ->whereIn('jadwal_ujian_id', $jadwalIds)
                ->pluck('sesi_ruangan_id');

            $sesiIds = $sesiIds->merge($sesiFromJadwal)->unique()->values();
        }

        $hasilIds = Schema::hasTable('hasil_ujian') && $jadwalIds->isNotEmpty()
            ? DB::table('hasil_ujian')->whereIn('jadwal_ujian_id', $jadwalIds)->pluck('id')
            : collect();

        $enrollmentIds = collect();
        if (Schema::hasTable('enrollment_ujian') && ($jadwalIds->isNotEmpty() || $sesiIds->isNotEmpty())) {
            $enrollmentQuery = DB::table('enrollment_ujian');
            $enrollmentQuery->where(function ($query) use ($jadwalIds, $sesiIds) {
                if ($jadwalIds->isNotEmpty() && Schema::hasColumn('enrollment_ujian', 'jadwal_ujian_id')) {
                    $query->whereIn('jadwal_ujian_id', $jadwalIds);
                }

                if ($sesiIds->isNotEmpty() && Schema::hasColumn('enrollment_ujian', 'sesi_ruangan_id')) {
                    $method = $jadwalIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('sesi_ruangan_id', $sesiIds);
                }
            });
            $enrollmentIds = $enrollmentQuery->pluck('id');
        }

        return [
            'jadwal_ids' => $jadwalIds,
            'bank_soal_ids' => $bankSoalIds,
            'ruangan_ids' => $ruanganIds,
            'sesi_ids' => $sesiIds,
            'hasil_ids' => $hasilIds,
            'enrollment_ids' => $enrollmentIds,
        ];
    }

    private function deletePaketRelatedData(array $impact): void
    {
        $jadwalIds = $impact['jadwal_ids'];
        $bankSoalIds = $impact['bank_soal_ids'];
        $ruanganIds = $impact['ruangan_ids'];
        $sesiIds = $impact['sesi_ids'];
        $hasilIds = $impact['hasil_ids'];
        $enrollmentIds = $impact['enrollment_ids'];

        if ($hasilIds->isNotEmpty()) {
            if (Schema::hasTable('jawaban_siswa')) {
                DB::table('jawaban_siswa')->whereIn('hasil_ujian_id', $hasilIds)->delete();
            }

            if (Schema::hasTable('jawaban_siswas')) {
                DB::table('jawaban_siswas')->whereIn('hasil_ujian_id', $hasilIds)->delete();
            }

            if (Schema::hasTable('pelanggaran_ujian')) {
                DB::table('pelanggaran_ujian')->whereIn('hasil_ujian_id', $hasilIds)->delete();
            }

            DB::table('hasil_ujian')->whereIn('id', $hasilIds)->delete();
        }

        if ($jadwalIds->isNotEmpty() && Schema::hasTable('pelanggaran_ujian')) {
            DB::table('pelanggaran_ujian')->whereIn('jadwal_ujian_id', $jadwalIds)->delete();
        }

        if ($enrollmentIds->isNotEmpty()) {
            DB::table('enrollment_ujian')->whereIn('id', $enrollmentIds)->delete();
        }

        if ($sesiIds->isNotEmpty()) {
            if (Schema::hasTable('berita_acara_ujian') && Schema::hasColumn('berita_acara_ujian', 'sesi_ruangan_id')) {
                DB::table('berita_acara_ujian')->whereIn('sesi_ruangan_id', $sesiIds)->delete();
            }

            if (Schema::hasTable('sesi_ruangan_siswa')) {
                DB::table('sesi_ruangan_siswa')->whereIn('sesi_ruangan_id', $sesiIds)->delete();
            }

            if (Schema::hasTable('jadwal_ujian_sesi_ruangan')) {
                DB::table('jadwal_ujian_sesi_ruangan')
                    ->whereIn('sesi_ruangan_id', $sesiIds)
                    ->orWhereIn('jadwal_ujian_id', $jadwalIds)
                    ->delete();
            }

            if (Schema::hasTable('pelanggaran_ujian')) {
                DB::table('pelanggaran_ujian')->whereIn('sesi_ruangan_id', $sesiIds)->delete();
            }

            DB::table('sesi_ruangan')->whereIn('id', $sesiIds)->delete();
        } elseif ($jadwalIds->isNotEmpty() && Schema::hasTable('jadwal_ujian_sesi_ruangan')) {
            DB::table('jadwal_ujian_sesi_ruangan')->whereIn('jadwal_ujian_id', $jadwalIds)->delete();
        }

        if ($jadwalIds->isNotEmpty()) {
            DB::table('jadwal_ujian')->whereIn('id', $jadwalIds)->delete();
        }

        if ($ruanganIds->isNotEmpty()) {
            DB::table('ruangan')->whereIn('id', $ruanganIds)->delete();
        }

        if ($bankSoalIds->isNotEmpty()) {
            BankSoal::withTrashed()
                ->whereIn('id', $bankSoalIds)
                ->get()
                ->each
                ->forceDelete();
        }
    }
}
