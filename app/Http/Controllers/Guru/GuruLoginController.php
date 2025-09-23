<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuruLoginController extends Controller
{
    // Menampilkan halaman login untuk Guru
    public function showLoginForm()
    {
        return view('auth.login-guru'); // login-guru.blade.php
    }

    // Proses login guru - now using web guard
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Login menggunakan guard web
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            DB::table('sessions')
                ->where('id', $request->session()->getId())
                ->update([
                    'guard' => 'web',
                ]);

            $user = Auth::guard('web')->user();

            // Check if user is a guru (has guru role)
            if (
                $user->isGuru() ||
                $user->canManageData() ||
                $user->canManageNaskah() ||
                $user->canManageRuangan() ||
                $user->canSupervise() ||
                $user->canCoordinate()
            ) {

                // Redirect based on user role
                return redirect()->intended(route($user->getRedirectRoute()));
            }

            // If not a guru, logout and show error
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'User tidak memiliki akses guru.',
            ]);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Logout - now using web guard
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login/guru');
    }
}
