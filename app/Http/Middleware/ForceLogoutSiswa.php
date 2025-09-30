<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ForceLogoutSiswa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('siswa')->user();

        if ($user) {
            $isForceLogout = \App\Models\SesiRuanganSiswa::where('siswa_id', $user->id)
                ->where('keterangan', 'force_logout')
                ->exists();

            if ($isForceLogout) {
                Log::info('Siswa Force Logout Karena Sesi sudah habis', ['User ID' => $user->id]);
                Auth::guard('siswa')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::info('redirect to login siswa after force logout');
                return redirect('/login/siswa')->with('error', 'Sesi Anda telah berakhir.');
            }
        }

        return $next($request);
    }
}
