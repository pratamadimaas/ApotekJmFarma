<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Check if user is active
            if (!Auth::user()->aktif) {
                Auth::logout();
                return back()->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name);
        }

        return back()->with('error', 'Email atau password salah!')->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    /**
     * Menampilkan halaman profil.
     * Mengubah view dari 'auth.profile' menjadi 'pages.profile.index'.
     */
    public function profile()
    {
        return view('pages.profile.index', [ // ✅ Mengubah view path
            'user' => Auth::user()
        ]);
    }

    /**
     * Memperbarui data profil (Nama & Email).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validasi HANYA untuk nama dan email
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Profil berhasil diupdate!');
    }
    
    /**
     * Catatan: Metode changePassword untuk Self-Service Password
     * harus didefinisikan di UserController (seperti yang telah kita buat sebelumnya)
     * atau di sini, sesuai preferensi Anda.
     */
}