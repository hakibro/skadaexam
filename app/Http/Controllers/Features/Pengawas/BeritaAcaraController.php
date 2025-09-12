<?php

namespace App\Http\Controllers\Features\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\JadwalUjianSesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BeritaAcaraController extends Controller
{
    /**
     * Check if current pengawas has access to the given sesi ruangan
     */
    private function checkPengawasAccess(SesiRuangan $sesiRuangan)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return false;
        }

        // Check if the guru is assigned as pengawas in any of the associated jadwal ujian
        return JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesiRuangan->id)
            ->where('pengawas_id', $guru->id)
            ->exists();
    }

    /**
     * Display the berita acara for a session
     */
    public function show($id)
    {
        $sesiRuangan = SesiRuangan::with([
            'ruangan',
            'jadwalUjians',
            'jadwalUjians.mapel',
            'sesiRuanganSiswa',
            'sesiRuanganSiswa.siswa',
            'beritaAcaraUjian'
        ])->findOrFail($id);
        $today = Carbon::today();
        $sesiRuangan->setRelation('jadwalUjians', $sesiRuangan->jadwalUjians->filter(function ($jadwal) use ($today) {
            $jadwalDate = Carbon::parse($jadwal->tanggal);
            // Include today's exams and future exams, exclude past exams
            return $jadwalDate->isToday() || $jadwalDate->isFuture();
        }));

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Filter jadwal ujians to only show current/future exams (not past ones)


        // Get the berita acara if it exists, or prepare to create a new one
        $beritaAcara = $sesiRuangan->beritaAcaraUjian;

        return view('features.pengawas.berita_acara.show', compact('sesiRuangan', 'beritaAcara'));
    }

    /**
     * Show form to create a berita acara
     */
    public function create($id)
    {
        $sesiRuangan = SesiRuangan::with([
            'ruangan',
            'jadwalUjians',
            'jadwalUjians.mapel',
            'sesiRuanganSiswa',
            'sesiRuanganSiswa.siswa'
        ])->findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Check if a berita acara already exists for this session
        if ($sesiRuangan->beritaAcaraUjian) {
            return redirect()->route('pengawas.berita-acara.edit', $id)
                ->with('info', 'Berita acara sudah ada, silakan edit jika diperlukan');
        }

        // Calculate attendance statistics
        $totalStudents = $sesiRuangan->sesiRuanganSiswa->count();
        $presentStudents = $sesiRuangan->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count();
        $absentStudents = $sesiRuangan->sesiRuanganSiswa->whereIn('status_kehadiran', ['tidak_hadir', 'sakit', 'izin'])->count();

        return view('features.pengawas.berita_acara.create', compact(
            'sesiRuangan',
            'totalStudents',
            'presentStudents',
            'absentStudents'
        ));
    }

    /**
     * Store a newly created berita acara
     */
    public function store(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Get the guru for later use
        $user = Auth::user();
        $guru = $user->guru;

        // Validate request
        $request->validate([
            'catatan_pembukaan' => 'nullable|string|max:1000',
            'catatan_pelaksanaan' => 'nullable|string|max:1000',
            'catatan_penutupan' => 'nullable|string|max:1000',
            'status_pelaksanaan' => 'required|in:selesai_normal,selesai_terganggu,dibatalkan',
            'jumlah_peserta_terdaftar' => 'required|integer|min:0',
            'jumlah_peserta_hadir' => 'required|integer|min:0',
            'jumlah_peserta_tidak_hadir' => 'required|integer|min:0',
            'is_final' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Check if a berita acara already exists
            $beritaAcara = $sesiRuangan->beritaAcaraUjian;

            if ($beritaAcara) {
                return redirect()->route('pengawas.berita-acara.edit', $id)
                    ->with('info', 'Berita acara sudah ada, silakan edit jika diperlukan');
            }

            // Create new berita acara
            $beritaAcara = new BeritaAcaraUjian([
                'sesi_ruangan_id' => $sesiRuangan->id,
                'pengawas_id' => $guru->id,
                'catatan_pembukaan' => $request->catatan_pembukaan,
                'catatan_pelaksanaan' => $request->catatan_pelaksanaan,
                'catatan_penutupan' => $request->catatan_penutupan,
                'jumlah_peserta_terdaftar' => $request->jumlah_peserta_terdaftar,
                'jumlah_peserta_hadir' => $request->jumlah_peserta_hadir,
                'jumlah_peserta_tidak_hadir' => $request->jumlah_peserta_tidak_hadir,
                'status_pelaksanaan' => $request->status_pelaksanaan,
                'is_final' => $request->has('is_final') && $request->is_final ? true : false,
            ]);

            // If finalized, set the finalization time
            if ($beritaAcara->is_final) {
                $beritaAcara->waktu_finalisasi = now();
            }

            $beritaAcara->save();

            DB::commit();

            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('success', 'Berita acara berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating berita acara: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat berita acara: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit an existing berita acara
     */
    public function edit($id)
    {
        $sesiRuangan = SesiRuangan::with([
            'ruangan',
            'jadwalUjians',
            'jadwalUjians.mapel',
            'sesiRuanganSiswa',
            'sesiRuanganSiswa.siswa',
            'beritaAcaraUjian'
        ])->findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Get the berita acara
        $beritaAcara = $sesiRuangan->beritaAcaraUjian;

        if (!$beritaAcara) {
            return redirect()->route('pengawas.berita-acara.create', $id)
                ->with('info', 'Berita acara belum ada, silakan buat baru');
        }

        // Check if the berita acara is already finalized
        if ($beritaAcara->is_final) {
            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('info', 'Berita acara sudah difinalisasi dan tidak dapat diedit');
        }

        return view('features.pengawas.berita_acara.edit', compact('sesiRuangan', 'beritaAcara'));
    }

    /**
     * Update an existing berita acara
     */
    public function update(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Get the berita acara
        $beritaAcara = $sesiRuangan->beritaAcaraUjian;

        if (!$beritaAcara) {
            return redirect()->route('pengawas.berita-acara.create', $id)
                ->with('info', 'Berita acara belum ada, silakan buat baru');
        }

        // Check if the berita acara is already finalized
        if ($beritaAcara->is_final) {
            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('info', 'Berita acara sudah difinalisasi dan tidak dapat diedit');
        }

        // Validate request
        $request->validate([
            'catatan_pembukaan' => 'nullable|string|max:1000',
            'catatan_pelaksanaan' => 'nullable|string|max:1000',
            'catatan_penutupan' => 'nullable|string|max:1000',
            'status_pelaksanaan' => 'required|in:selesai_normal,selesai_terganggu,dibatalkan',
            'jumlah_peserta_terdaftar' => 'required|integer|min:0',
            'jumlah_peserta_hadir' => 'required|integer|min:0',
            'jumlah_peserta_tidak_hadir' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update berita acara
            $beritaAcara->update([
                'catatan_pembukaan' => $request->catatan_pembukaan,
                'catatan_pelaksanaan' => $request->catatan_pelaksanaan,
                'catatan_penutupan' => $request->catatan_penutupan,
                'jumlah_peserta_terdaftar' => $request->jumlah_peserta_terdaftar,
                'jumlah_peserta_hadir' => $request->jumlah_peserta_hadir,
                'jumlah_peserta_tidak_hadir' => $request->jumlah_peserta_tidak_hadir,
                'status_pelaksanaan' => $request->status_pelaksanaan,
            ]);

            DB::commit();

            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('success', 'Berita acara berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating berita acara: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui berita acara: ' . $e->getMessage());
        }
    }

    /**
     * Finalize a berita acara
     */
    public function finalize(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        if (!$this->checkPengawasAccess($sesiRuangan)) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Get the berita acara
        $beritaAcara = $sesiRuangan->beritaAcaraUjian;

        if (!$beritaAcara) {
            return redirect()->route('pengawas.berita-acara.create', $id)
                ->with('info', 'Berita acara belum ada, silakan buat baru');
        }

        // Check if the berita acara is already finalized
        if ($beritaAcara->is_final) {
            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('info', 'Berita acara sudah difinalisasi');
        }

        try {
            DB::beginTransaction();

            // Finalize the berita acara
            $beritaAcara->is_final = true;
            $beritaAcara->waktu_finalisasi = now();
            $beritaAcara->save();

            // Update the session status if needed
            if ($sesiRuangan->status !== 'selesai' && $sesiRuangan->status !== 'dibatalkan') {
                if ($beritaAcara->status_pelaksanaan === 'dibatalkan') {
                    $sesiRuangan->status = 'dibatalkan';
                } else {
                    $sesiRuangan->status = 'selesai';
                }
                $sesiRuangan->save();
            }

            DB::commit();

            return redirect()->route('pengawas.berita-acara.show', $id)
                ->with('success', 'Berita acara berhasil difinalisasi');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizing berita acara: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memfinalisasi berita acara: ' . $e->getMessage());
        }
    }
}
