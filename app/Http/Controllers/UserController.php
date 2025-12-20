<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('cabang')->orderBy('name')->paginate(15);
        return view('pages.users.index', compact('users'));
    }

    public function create()
    {
        $cabang = Cabang::aktif()->orderBy('nama_cabang')->get();
        return view('pages.users.create', compact('cabang'));
    }

    public function store(Request $request)
    {
        // Validasi dinamis berdasarkan role
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|min:4', 
            'role' => 'required|in:super_admin,admin_cabang,kasir',
            'aktif' => 'boolean'
        ];
        
        // Tambah validasi cabang_id hanya untuk admin_cabang dan kasir
        if (in_array($request->role, ['admin_cabang', 'kasir'])) {
            $rules['cabang_id'] = 'required|exists:cabang,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.min' => 'Password minimal 4 karakter', // ✅ Diperbaiki dari 'max' ke 'min'
            'role.required' => 'Role wajib dipilih',
            'cabang_id.required' => 'Cabang wajib dipilih untuk Admin Cabang dan Kasir',
            'cabang_id.exists' => 'Cabang tidak valid'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->except('password');
            
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $generatedPassword = null;
            } else {
                // Generate password random 5 karakter
                $generatedPassword = \Illuminate\Support\Str::random(5);
                $data['password'] = Hash::make($generatedPassword);
            }
            
            // ✅ Super admin tidak perlu cabang
            if ($request->role === 'super_admin') {
                $data['cabang_id'] = null;
            }
            
            // ✅ Set default aktif jika tidak ada
            $data['aktif'] = $request->has('aktif') ? (bool) $request->aktif : true;

            $user = User::create($data);

            // ✅ Tampilkan password yang di-generate
            $successMessage = 'User berhasil ditambahkan!';
            if ($generatedPassword) {
                $successMessage .= " Password: {$generatedPassword} (Catat password ini!)";
            }

            return redirect()->route('users.index')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambah user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(User $user)
    {
        $cabang = Cabang::aktif()->orderBy('nama_cabang')->get();
        return view('pages.users.edit', compact('user', 'cabang'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin_cabang,kasir',
            'cabang_id' => 'required_if:role,admin_cabang,kasir|exists:cabang,id',
            'password' => 'nullable|min:4',
            'aktif' => 'boolean'
        ], [
            'cabang_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang dan Kasir',
            'password.min' => 'Password minimal 4 karakter' // ✅ Tambahkan ini juga untuk konsistensi
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('password');
        
        // Super admin tidak perlu cabang
        if ($request->role === 'super_admin') {
            $data['cabang_id'] = null;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus user yang sedang login!');
        }

        try {
            // Cek apakah user memiliki data terkait
            $hasPenjualan = $user->penjualan()->exists();
            $hasPembelian = $user->pembelian()->exists();
            $hasShifts = $user->shifts()->exists();

            if ($hasPenjualan || $hasPembelian || $hasShifts) {
                // Jika ada data terkait, nonaktifkan saja (soft delete)
                $user->update(['aktif' => false]);
                
                return redirect()->route('users.index')
                    ->with('success', 'User berhasil dinonaktifkan karena masih memiliki data transaksi terkait!');
            } else {
                // Jika tidak ada data terkait, hapus permanen
                $user->delete();
                
                return redirect()->route('users.index')
                    ->with('success', 'User berhasil dihapus!');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('password123')
        ]);

        return redirect()->back()
            ->with('success', 'Password berhasil direset ke: password123');
    }

    public function showChangePasswordForm()
    {
        return view('pages.users.change-password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 4 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password lama tidak sesuai!');
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->back()
            ->with('success', 'Password berhasil diubah!');
    }
}