<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
        }
        
        $users = $query->orderBy('name', 'asc')->paginate(10);
        
        return view('pages.users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah user baru.
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * Menyimpan user baru ke database (termasuk user kasir).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'password' => 'nullable|string|min:8', // Password opsional, bisa digenerate
            'aktif' => 'required|boolean',
        ]);

        $password = $request->password 
                    ? $request->password 
                    : Str::random(10); // Generate password jika kosong

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'aktif' => $request->aktif,
            'password' => Hash::make($password), // Harus di-hash
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan! Password awal: ' . $password);
    }

    /**
     * Menampilkan detail user (Opsional, sering dihandle oleh view index/edit).
     */
    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    /**
     * Menampilkan form edit user.
     */
    public function edit(User $user)
    {
        return view('pages.users.edit', compact('user'));
    }

    /**
     * Memperbarui data user di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'aktif' => 'required|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'aktif' => $request->aktif,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        // Pencegahan: Jangan biarkan user menghapus dirinya sendiri
        if (Auth::user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }
        
        // Pencegahan: Hapus relasi terkait jika ada (opsional)
        // $user->penjualan()->delete(); 

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    // --- FITUR TAMBAHAN ---

    /**
     * Reset password user tertentu (Admin only).
     */
    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return back()->with('success', "Password untuk user {$user->name} berhasil di-reset. Password baru: **{$newPassword}**");
    }

    /**
     * Menampilkan form ubah password user (Self-Service).
     */
    public function showChangePasswordForm()
    {
        return view('pages.users.change_password');
    }

    /**
     * Memproses permintaan ubah password user (Self-Service).
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password Anda berhasil diubah!');
    }
}