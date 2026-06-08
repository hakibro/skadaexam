<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\EnrollmentUjian;
use App\Services\TahunAjaranService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UjianSusulanController extends Controller
{
    /**
     * Step 1: Buat ruangan untuk ujian susulan.
     * Mengembalikan data ruangan yang baru dibuat.
     */
    public function createRuangan(Request $request)
    {
        $validated = $request->validate([
            'jadwal_ids' => 'required|array|min:1',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'nama_ruangan' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
        ]);

        $jadwalAsliList = JadwalUjian::with('tahunAjaran')
            ->whereIn('id', $validated['jadwal_ids'])
            ->get();

        $tahunAjaranId = $jadwalAsliList->first()?->tahun_ajaran_id;
        $paketUjianId = $jadwalAsliList->first()?->paket_ujian_id;

        if (!$tahunAjaranId) {
            return response()->json(['message' => 'Jadwal tidak ditemukan.'], 422);
        }

        if ($jadwalAsliList->contains(fn($jadwal) => $jadwal->tahun_ajaran_id !== $tahunAjaranId)) {
            return response()->json(['message' => 'Semua jadwal susulan harus berada pada tahun ajaran yang sama.'], 422);
        }

        if ($jadwalAsliList->contains(fn($jadwal) => $jadwal->tahunAjaran?->isReadOnly())) {
            return response()->json(['message' => 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.'], 422);
        }

        $ruangan = Ruangan::create([
            'tahun_ajaran_id' => $tahunAjaranId,
            'paket_ujian_id' => $paketUjianId,
            'kode_ruangan' => 'RS' . strtoupper(Str::random(4)),
            'nama_ruangan' => $validated['nama_ruangan'],
            'kapasitas' => $validated['kapasitas'],
            'status' => 'aktif',
        ]);

        return response()->json([
            'message' => 'Ruangan susulan berhasil dibuat.',
            'ruangan' => [
                'id' => $ruangan->id,
                'kode_ruangan' => $ruangan->kode_ruangan,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'kapasitas' => $ruangan->kapasitas,
            ],
            'tahun_ajaran_id' => $tahunAjaranId,
            'paket_ujian_id' => $paketUjianId,
        ]);
    }

    /**
     * Step 2: Buat sesi ruangan secara komprehensif (multiple sesi dinamis).
     */
    public function createSesi(Request $request)
    {
        $validated = $request->validate([
            'ruangan_id' => 'required|exists:ruangan,id',
            'sesi' => 'required|array|min:1',
            'sesi.*.nama_sesi' => 'required|string|max:255',
            'sesi.*.waktu_mulai' => 'required|date_format:H:i',
            'sesi.*.waktu_selesai' => 'required|date_format:H:i',
        ]);

        $ruangan = Ruangan::findOrFail($validated['ruangan_id']);

        // Validasi waktu_selesai > waktu_mulai untuk setiap sesi
        foreach ($validated['sesi'] as $index => $sesiData) {
            if ($sesiData['waktu_selesai'] <= $sesiData['waktu_mulai']) {
                return response()->json([
                    'message' => "Sesi #" . ($index + 1) . ": Waktu selesai harus setelah waktu mulai.",
                ], 422);
            }
        }

        $createdSesi = [];

        DB::beginTransaction();
        try {
            foreach ($validated['sesi'] as $sesiData) {
                $sesi = SesiRuangan::create([
                    'tahun_ajaran_id' => $ruangan->tahun_ajaran_id,
                    'ruangan_id' => $ruangan->id,
                    'paket_ujian_id' => $ruangan->paket_ujian_id,
                    'nama_sesi' => $sesiData['nama_sesi'],
                    'waktu_mulai' => $sesiData['waktu_mulai'],
                    'waktu_selesai' => $sesiData['waktu_selesai'],
                    'status' => 'belum_mulai',
                    'pengaturan' => null,
                ]);

                $createdSesi[] = [
                    'id' => $sesi->id,
                    'nama_sesi' => $sesi->nama_sesi,
                    'waktu_mulai' => substr($sesi->waktu_mulai, 0, 5),
                    'waktu_selesai' => substr($sesi->waktu_selesai, 0, 5),
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat sesi susulan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat sesi: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => count($createdSesi) . ' sesi berhasil dibuat.',
            'sesi' => $createdSesi,
        ]);
    }

    /**
     * Step 3: Duplikasi jadwal ujian untuk susulan dengan tanggal dan durasi komprehensif.
     */
    public function duplicateJadwal(Request $request)
    {
        $validated = $request->validate([
            'jadwal_ids' => 'required|array|min:1',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'tanggal' => 'required|date',
            // Durasi bisa bulk (durasi_mode = bulk) atau per jadwal (durasi_mode = per_jadwal)
            'durasi_mode' => 'required|in:bulk,per_jadwal',
            'durasi_bulk' => 'required_if:durasi_mode,bulk|nullable|integer|min:1',
            'durasi_per_jadwal' => 'required_if:durasi_mode,per_jadwal|nullable|array',
            'durasi_per_jadwal.*' => 'nullable|integer|min:1',
        ]);

        $jadwalAsliList = JadwalUjian::with('tahunAjaran')
            ->whereIn('id', $validated['jadwal_ids'])
            ->get();

        $tahunAjaranId = $jadwalAsliList->first()?->tahun_ajaran_id;
        $paketUjianId = $jadwalAsliList->first()?->paket_ujian_id;

        if ($jadwalAsliList->contains(fn($jadwal) => $jadwal->tahunAjaran?->isReadOnly())) {
            return response()->json(['message' => 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.'], 422);
        }

        $duplicatedJadwal = [];

        DB::beginTransaction();
        try {
            foreach ($validated['jadwal_ids'] as $jadwalId) {
                $jadwalAsli = JadwalUjian::findOrFail($jadwalId);

                // Tentukan durasi
                if ($validated['durasi_mode'] === 'bulk') {
                    $durasi = (int) $validated['durasi_bulk'];
                } else {
                    $durasi = (int) ($validated['durasi_per_jadwal'][$jadwalId] ?? $jadwalAsli->durasi_menit);
                }

                $jadwalBaru = $jadwalAsli->replicate();
                $jadwalBaru->judul = 'Susulan - ' . $jadwalAsli->judul;
                $jadwalBaru->tanggal = $validated['tanggal'];
                $jadwalBaru->durasi_menit = $durasi;
                $jadwalBaru->status = 'aktif';
                $jadwalBaru->kode_ujian = 'S' . date('Ymd') . strtoupper(Str::random(5));
                $jadwalBaru->created_by = auth()->id();
                $jadwalBaru->tahun_ajaran_id = $tahunAjaranId;
                $jadwalBaru->paket_ujian_id = $paketUjianId;
                $jadwalBaru->auto_assign_sesi = false;
                $jadwalBaru->auto_enroll = false;
                $jadwalBaru->save();

                $duplicatedJadwal[] = [
                    'id' => $jadwalBaru->id,
                    'judul' => $jadwalBaru->judul,
                    'kode_ujian' => $jadwalBaru->kode_ujian,
                    'tanggal' => $jadwalBaru->tanggal->format('Y-m-d'),
                    'durasi_menit' => $jadwalBaru->durasi_menit,
                    'jadwal_asli_id' => $jadwalAsli->id,
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal duplikasi jadwal susulan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal duplikasi jadwal: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => count($duplicatedJadwal) . ' jadwal susulan berhasil dibuat.',
            'jadwal' => $duplicatedJadwal,
        ]);
    }

    /**
     * Step 4: Assign jadwal susulan ke sesi ruangan (drag & drop).
     * Otomatis assign siswa ke sesi dan enroll siswa eligible.
     */
    public function assignToSesi(Request $request)
    {
        $validated = $request->validate([
            'jadwal_baru_id' => 'required|exists:jadwal_ujian,id',
            'jadwal_asli_id' => 'required|exists:jadwal_ujian,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
        ]);

        DB::beginTransaction();
        try {
            $jadwalBaru = JadwalUjian::findOrFail($validated['jadwal_baru_id']);
            $sesi = SesiRuangan::findOrFail($validated['sesi_ruangan_id']);

            // Cek apakah jadwal sudah terhubung dengan sesi ini
            $alreadyAttached = $jadwalBaru->sesiRuangans()
                ->where('sesi_ruangan.id', $sesi->id)
                ->exists();

            if (!$alreadyAttached) {
                $jadwalBaru->sesiRuangans()->attach($sesi->id);
            }

            // Ambil semua siswa eligible (enrolled) dari jadwal asli
            $enrollments = EnrollmentUjian::where('jadwal_ujian_id', $validated['jadwal_asli_id'])
                ->where('status_enrollment', 'enrolled')
                ->with('siswa')
                ->get();

            $assignedCount = 0;
            $enrolledCount = 0;

            foreach ($enrollments as $enrollment) {
                $siswa = $enrollment->siswa;
                if (!$siswa) {
                    continue;
                }

                // Assign siswa ke sesi (sesi_ruangan_siswa) jika belum ada
                $sesiSiswaExists = SesiRuanganSiswa::where('sesi_ruangan_id', $sesi->id)
                    ->where('siswa_id', $siswa->id)
                    ->exists();

                if (!$sesiSiswaExists) {
                    SesiRuanganSiswa::create([
                        'sesi_ruangan_id' => $sesi->id,
                        'siswa_id' => $siswa->id,
                        'status_kehadiran' => 'tidak_hadir',
                    ]);
                    $assignedCount++;
                }

                // Enroll siswa ke jadwal baru jika belum ada
                $enrollmentExists = EnrollmentUjian::where('siswa_id', $siswa->id)
                    ->where('jadwal_ujian_id', $jadwalBaru->id)
                    ->exists();

                if (!$enrollmentExists) {
                    EnrollmentUjian::create([
                        'siswa_id' => $siswa->id,
                        'jadwal_ujian_id' => $jadwalBaru->id,
                        'sesi_ruangan_id' => $sesi->id,
                        'status_enrollment' => 'enrolled',
                        'catatan' => 'Ujian susulan',
                    ]);
                    $enrolledCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal assign jadwal susulan ke sesi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal assign jadwal ke sesi: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "Jadwal berhasil di-assign ke sesi. {$assignedCount} siswa di-assign, {$enrolledCount} siswa di-enroll.",
            'assigned_count' => $assignedCount,
            'enrolled_count' => $enrolledCount,
        ]);
    }

    /**
     * Lepaskan jadwal dari sesi (undo drag & drop).
     */
    public function detachFromSesi(Request $request)
    {
        $validated = $request->validate([
            'jadwal_baru_id' => 'required|exists:jadwal_ujian,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
        ]);

        DB::beginTransaction();
        try {
            $jadwalBaru = JadwalUjian::findOrFail($validated['jadwal_baru_id']);
            $sesi = SesiRuangan::findOrFail($validated['sesi_ruangan_id']);

            $jadwalBaru->sesiRuangans()->detach($sesi->id);

            // Hapus enrollment yang terkait dengan sesi ini untuk jadwal baru
            EnrollmentUjian::where('jadwal_ujian_id', $jadwalBaru->id)
                ->where('sesi_ruangan_id', $sesi->id)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal lepaskan jadwal dari sesi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal melepaskan jadwal: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Jadwal berhasil dilepaskan dari sesi.',
        ]);
    }

    /**
     * Finalisasi pembuatan ujian susulan.
     */
    public function finalize(Request $request)
    {
        $validated = $request->validate([
            'jadwal_baru_ids' => 'required|array|min:1',
            'jadwal_baru_ids.*' => 'exists:jadwal_ujian,id',
        ]);

        return response()->json([
            'message' => 'Ujian susulan berhasil dibuat untuk ' . count($validated['jadwal_baru_ids']) . ' jadwal.',
            'redirect' => route('naskah.jadwal.index'),
        ]);
    }
}