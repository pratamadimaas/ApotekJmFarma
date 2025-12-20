<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ], [
            'username.required' => 'Nama wajib diisi',
            'password.required' => 'Password wajib diisi'
        ]);

       
        $user = User::whereRaw('LOWER(name) = ?', [strtolower($request->username)])
                    ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            
            // Cek apakah user aktif
            if (!$user->aktif) {
                return back()
                    ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.')
                    ->onlyInput('username');
            }

            // Login manual
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . $user->name . '!');
        }

        // Jika gagal login
        return back()
            ->with('error', 'Nama atau password salah!')
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }

    public function profile()
    {
        return view('pages.profile.index', [
            'user' => Auth::user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Profil berhasil diupdate!');
    }
}