<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Menampilkan halaman login untuk Admin/Guru
    public function showLoginForm()
    {
        return view('auth.login'); // login.blade.php
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Coba login sebagai Admin (guard web)
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan role user
            switch ($user->role) {
                case 'admin':
                    return redirect()->intended('/admin/dashboard');
                case 'siswa':
                    return redirect()->intended('/siswa/dashboard');
                case 'guru':
                    // Jika guru login melalui form admin, logout dan redirect ke login guru
                    Auth::logout();
                    return redirect('/login/guru')->with('message', 'Silakan login melalui halaman guru');
                default:
                    Auth::logout();
                    return back()->withErrors(['email' => 'Role tidak valid.']);
            }
        }

        // Coba login sebagai Guru (guard guru)
        if (Auth::guard('guru')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/guru/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    // Logout (akan logout dari kedua guard)
    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('guru')->check()) {
            Auth::guard('guru')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
