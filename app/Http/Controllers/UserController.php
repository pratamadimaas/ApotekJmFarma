<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('cabang')->orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $cabang = Cabang::aktif()->orderBy('nama_cabang')->get();
        return view('users.create', compact('cabang'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,admin_cabang,kasir',
            'cabang_id' => 'required_if:role,admin_cabang,kasir|exists:cabang,id',
            'aktif' => 'boolean'
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'role.required' => 'Role wajib dipilih',
            'cabang_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang dan Kasir'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        
        // Super admin tidak perlu cabang
        if ($request->role === 'super_admin') {
            $data['cabang_id'] = null;
        }

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $cabang = Cabang::aktif()->orderBy('nama_cabang')->get();
        return view('users.edit', compact('user', 'cabang'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin_cabang,kasir',
            'cabang_id' => 'required_if:role,admin_cabang,kasir|exists:cabang,id',
            'password' => 'nullable|min:6',
            'aktif' => 'boolean'
        ], [
            'cabang_id.required_if' => 'Cabang wajib dipilih untuk Admin Cabang dan Kasir'
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

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
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
        return view('users.change-password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
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