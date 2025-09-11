<?php

namespace App\Http\Controllers\Features\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\SesiRuangan;
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
        $sesiRuangan = SesiRuangan::with(['ruangan', 'jadwalUjians', 'jadwalUjians.mapel', 'sesiRuanganSiswa'])
            ->findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru || $sesiRuangan->pengawas_id !== $guru->id) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        return view('features.pengawas.token', compact('sesiRuangan'));
    }

    /**
     * Generate a token for student login
     */
    public function generateToken(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru || $sesiRuangan->pengawas_id !== $guru->id) {
            return redirect()->route('pengawas.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
        }

        // Set expiry time from the request or use default (4 hours)
        $expiryHours = $request->input('expiry_hours', 4);

        try {
            // Generate token
            $token = $sesiRuangan->generateToken();

            // Override token expiration if specified in request
            if ($request->has('expiry_hours')) {
                $sesiRuangan->token_expired_at = now()->addHours($expiryHours);
                $sesiRuangan->save();
            }

            // Log token generation
            Log::info('Token generated', [
                'sesi_id' => $sesiRuangan->id,
                'pengawas_id' => $guru->id,
                'token' => $token,
                'expires_at' => $sesiRuangan->token_expired_at
            ]);

            return redirect()->back()->with('success', 'Token berhasil dibuat dan akan berlaku hingga ' .
                $sesiRuangan->token_expired_at->format('H:i'));
        } catch (\Exception $e) {
            Log::error('Token generation failed', [
                'sesi_id' => $sesiRuangan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal membuat token: ' . $e->getMessage());
        }
    }
}
