<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PelanggaranController extends Controller
{
    /**
     * Get violations for a specific session or all sessions assigned to the pengawas
     */
    public function getViolations(Request $request, $sesiRuanganId = null)
    {
        try {
            $user = Auth::user();

            if (!$user->canSupervise() && !$user->isAdmin()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Base query to get violations
            $query = PelanggaranUjian::with([
                'siswa',
                'hasilUjian',
                'jadwalUjian.mapel',
                'sesiRuangan.ruangan'
            ])->orderBy('waktu_pelanggaran', 'desc');

            // If a specific session is requested, filter by that session
            if ($sesiRuanganId) {
                $query->where('sesi_ruangan_id', $sesiRuanganId);
            } else {
                // For admin, show all today's violations
                if ($user->isAdmin()) {
                    $today = now()->format('Y-m-d');
                    $query->whereDate('waktu_pelanggaran', $today);
                } else {
                    // For pengawas, get violations from their assigned sessions
                    $guru = $user->guru;
                    if (!$guru) {
                        return response()->json(['success' => true, 'violations' => []]);
                    }

                    // Get assigned session IDs from pivot table
                    $assignedSessionIds = \App\Models\JadwalUjianSesiRuangan::where('pengawas_id', $guru->id)
                        ->join('jadwal_ujian', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id', '=', 'jadwal_ujian.id')
                        ->whereDate('jadwal_ujian.tanggal', now()->format('Y-m-d'))
                        ->pluck('sesi_ruangan_id')
                        ->toArray();

                    $query->whereIn('sesi_ruangan_id', $assignedSessionIds);
                }
            }

            // Get the violations
            $violations = $query->get();

            return response()->json([
                'success' => true,
                'violations' => $violations
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting violations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'sesi_ruangan_id' => $sesiRuanganId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get violations'
            ], 500);
        }
    }

    /**
     * Process a violation (dismiss or finalize)
     */
    public function processViolation(Request $request, $violationId)
    {
        try {
            $user = Auth::user();

            if (!$user->canSupervise() && !$user->isAdmin()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $violation = PelanggaranUjian::findOrFail($violationId);

            // Validate the action
            $request->validate([
                'action' => 'required|in:dismiss,warning,suspend,remove',
                'tindakan' => 'nullable|string',
                'catatan_pengawas' => 'nullable|string'
            ]);

            $supervisorName = $user->isAdmin() ? 'Administrator' : ($user->guru->nama ?? 'Pengawas');

            switch ($request->action) {
                case 'dismiss':
                    // Abaikan pelanggaran - siswa dapat melanjutkan ujian
                    $violation->update([
                        'is_dismissed' => true,
                        'catatan_pengawas' => 'Diabaikan oleh ' . $supervisorName . ': ' . ($request->catatan_pengawas ?? 'Tidak ada catatan')
                    ]);
                    $message = 'Pelanggaran berhasil diabaikan. Siswa dapat melanjutkan ujian.';
                    break;

                case 'warning':
                    // Beri peringatan - siswa dapat melanjutkan ujian
                    $violation->update([
                        'is_finalized' => true,
                        'tindakan' => 'peringatan',
                        'catatan_pengawas' => 'Peringatan dari ' . $supervisorName . ': ' . ($request->catatan_pengawas ?? 'Diberi peringatan untuk tidak mengulangi pelanggaran')
                    ]);
                    $message = 'Peringatan berhasil diberikan. Siswa dapat melanjutkan ujian.';
                    break;

                case 'suspend':
                    // Hentikan sementara - logout siswa dari ujian
                    $violation->update([
                        'is_finalized' => true,
                        'tindakan' => 'hentikan_sementara',
                        'catatan_pengawas' => 'Dihentikan sementara oleh ' . $supervisorName . ': ' . ($request->catatan_pengawas ?? 'Siswa di-logout dari ujian')
                    ]);

                    // Logout siswa dari ujian
                    $this->logoutStudent($violation->siswa_id);
                    $message = 'Siswa berhasil di-logout dari ujian.';
                    break;

                case 'remove':
                    // Keluarkan dari ujian - hapus enrollment
                    $violation->update([
                        'is_finalized' => true,
                        'tindakan' => 'keluarkan',
                        'catatan_pengawas' => 'Dikeluarkan dari ujian oleh ' . $supervisorName . ': ' . ($request->catatan_pengawas ?? 'Enrollment dihapus karena pelanggaran serius')
                    ]);

                    // Hapus enrollment dan logout siswa
                    $this->removeStudentFromExam($violation);
                    $message = 'Siswa berhasil dikeluarkan dari ujian dan enrollment dihapus.';
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Action tidak valid'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $request->action
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing violation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'violation_id' => $violationId,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process violation'
            ], 500);
        }
    }

    /**
     * Logout student from exam
     */
    private function logoutStudent($siswaId)
    {
        try {
            // Invalidate all sessions for this student
            DB::table('sessions')
                ->where('user_id', $siswaId)
                ->where('guard', 'siswa')
                ->delete();

            // Clear any current enrollment session data
            \App\Models\EnrollmentUjian::where('siswa_id', $siswaId)
                ->where('status', 'aktif')
                ->update(['status' => 'suspended_by_supervisor']);

            Log::info('Student logged out by supervisor', [
                'siswa_id' => $siswaId,
                'supervisor_id' => Auth::id()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error logging out student', [
                'siswa_id' => $siswaId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove student from exam (delete enrollment)
     */
    private function removeStudentFromExam($violation)
    {
        try {
            // First logout the student
            $this->logoutStudent($violation->siswa_id);

            // Find and delete the enrollment
            $enrollment = \App\Models\EnrollmentUjian::where('siswa_id', $violation->siswa_id)
                ->where('jadwal_ujian_id', $violation->jadwal_ujian_id)
                ->where('sesi_ruangan_id', $violation->sesi_ruangan_id)
                ->first();

            if ($enrollment) {
                // Mark hasil ujian as terminated if exists
                \App\Models\HasilUjian::where('enrollment_ujian_id', $enrollment->id)
                    ->where('siswa_id', $violation->siswa_id)
                    ->update([
                        'status' => 'terminated_by_supervisor',
                        'is_final' => true,
                        'waktu_selesai' => now()
                    ]);

                // Delete the enrollment
                $enrollment->delete();

                Log::info('Student enrollment removed by supervisor', [
                    'siswa_id' => $violation->siswa_id,
                    'enrollment_id' => $enrollment->id,
                    'supervisor_id' => Auth::id()
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error removing student from exam', [
                'violation_id' => $violation->id,
                'siswa_id' => $violation->siswa_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
