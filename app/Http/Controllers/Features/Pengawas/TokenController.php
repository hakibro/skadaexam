<?php

namespace App\Http\Controllers\Features\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\SesiRuangan;
use App\Models\JadwalUjianSesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TokenController extends Controller
{
    /**
     * Show the token generation form
     */
    public function showTokenForm($id)
    {
        try {
            $sesiRuangan = SesiRuangan::with(['ruangan', 'tahunAjaran', 'jadwalUjians', 'jadwalUjians.mapel', 'sesiRuanganSiswa'])
                ->findOrFail($id);

            // Filter jadwal ujians to only show current/future exams (not past ones)
            $today = Carbon::today();
            $sesiRuangan->setRelation('jadwalUjians', $sesiRuangan->jadwalUjians->filter(function ($jadwal) use ($today) {
                $jadwalDate = Carbon::parse($jadwal->tanggal);
                // Include today's exams and future exams, exclude past exams
                return $jadwalDate->isToday() || $jadwalDate->isFuture();
            }));

            // Check if current guru is assigned to this sesi ruangan
            $user = Auth::user();
            $guru = $user->guru;

            if (!$guru) {
                return redirect()->route('pengawas.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
            }

            // Check if the guru is assigned as pengawas in any of the associated jadwal ujian
            $isAuthorized = JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesiRuangan->id)
                ->where('pengawas_id', $guru->id)
                ->exists();

            if (!$isAuthorized) {
                return redirect()->route('pengawas.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
            }

            return view('features.pengawas.token', compact('sesiRuangan'));
        } catch (\Exception $e) {
            Log::error('Error in showTokenForm: ' . $e->getMessage(), [
                'sesi_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate a token for student login
     */
    public function generateToken(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::with(['tahunAjaran', 'jadwalUjians', 'jadwalUjians.mapel'])
            ->findOrFail($id);

        if ($sesiRuangan->tahunAjaran?->isReadOnly()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi pada tahun ajaran arsip hanya dapat dilihat.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

        // Filter jadwal ujians to only show current/future exams (not past ones)
        $today = Carbon::today();
        $sesiRuangan->setRelation('jadwalUjians', $sesiRuangan->jadwalUjians->filter(function ($jadwal) use ($today) {
            $jadwalDate = Carbon::parse($jadwal->tanggal);
            // Include today's exams and future exams, exclude past exams
            return $jadwalDate->isToday() || $jadwalDate->isFuture();
        }));

        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru && !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke sesi ruangan ini'
                ], 403);
            }
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Check if the guru is assigned as pengawas in any of the associated jadwal ujian (skip for admin)
        if (!$user->isAdmin()) {
            $isAuthorized = JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesiRuangan->id)
                ->where('pengawas_id', $guru->id)
                ->exists();

            if (!$isAuthorized) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke sesi ruangan ini'
                    ], 403);
                }
                return redirect()->route('pengawas.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
            }
        }

        // Set expiry time from the request or use default (10 minutes for AJAX, 5 for form)
        $expiryMinutes = (int) $request->input('expiry_minutes', $request->expectsJson() ? 10 : 5);

        try {
            // Generate token
            $token = $sesiRuangan->generateToken();

            // Override token expiration
            $sesiRuangan->token_expired_at = now()->addMinutes($expiryMinutes);
            $sesiRuangan->save();

            // Log token generation
            Log::info('Token generated', [
                'sesi_id' => $sesiRuangan->id,
                'pengawas_id' => $guru?->id ?? 'admin',
                'token' => $token,
                'expires_at' => $sesiRuangan->token_expired_at
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token berhasil dibuat',
                    'data' => [
                        'token' => $token,
                        'expires_at' => $sesiRuangan->token_expired_at->format('Y-m-d H:i:s'),
                        'expires_at_formatted' => $sesiRuangan->token_expired_at->format('H:i')
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Token berhasil dibuat dan akan berlaku hingga ' .
                $sesiRuangan->token_expired_at->format('H:i'));
        } catch (\Exception $e) {
            Log::error('Token generation failed', [
                'sesi_id' => $sesiRuangan->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat token: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal membuat token: ' . $e->getMessage());
        }
    }
}
