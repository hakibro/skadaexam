<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                'action' => 'required|in:dismiss,finalize',
                'tindakan' => 'nullable|string',
                'catatan_pengawas' => 'nullable|string'
            ]);

            $supervisorName = $user->isAdmin() ? 'Administrator' : ($user->guru->nama ?? 'Pengawas');

            if ($request->action === 'dismiss') {
                $violation->update([
                    'is_dismissed' => true,
                    'catatan_pengawas' => 'Diabaikan oleh ' . $supervisorName
                ]);
            } else {
                $violation->update([
                    'is_finalized' => true,
                    'tindakan' => $request->tindakan,
                    'catatan_pengawas' => $request->catatan_pengawas
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Violation processed successfully'
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
}
