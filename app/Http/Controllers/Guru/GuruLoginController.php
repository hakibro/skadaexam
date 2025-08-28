<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruLoginController extends Controller
{
    // Menampilkan halaman login untuk Guru
    public function showLoginForm()
    {
        return view('auth.login-guru'); // login-guru.blade.php
    }

    // Proses login guru
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Login menggunakan guard guru
        if (Auth::guard('guru')->attempt($credentials)) {
            $request->session()->regenerate();

            $guru = Auth::guard('guru')->user();

            // Redirect berdasarkan role guru
            switch ($guru->role) {
                case 'data':
                    return redirect()->intended('/guru/data/dashboard');
                case 'ruangan':
                    return redirect()->intended('/guru/ruangan/dashboard');
                case 'pengawas':
                    return redirect()->intended('/guru/pengawas/dashboard');
                case 'koordinator':
                    return redirect()->intended('/guru/koordinator/dashboard');
                case 'naskah':
                    return redirect()->intended('/guru/naskah/dashboard');
                case 'guru':
                default:
                    return redirect()->intended('/guru/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Logout guru
    public function logout(Request $request)
    {
        Auth::guard('guru')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login/guru');
    }
}
