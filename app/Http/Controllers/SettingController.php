<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    // Halaman Setting Aplikasi
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        
        return view('pages.setting.index', compact('settings'));
    }

    // Update Setting Aplikasi
    public function update(Request $request)
    {
        $request->validate([
            'nama_apotek' => 'required|string|max:255',
            'alamat' => 'required|string',
            'telepon' => 'required|string|max:20',
            'email' => 'nullable|email',
        ]);

        Setting::updateOrCreate(['key' => 'nama_apotek'], ['value' => $request->nama_apotek]);
        Setting::updateOrCreate(['key' => 'alamat'], ['value' => $request->alamat]);
        Setting::updateOrCreate(['key' => 'telepon'], ['value' => $request->telepon]);
        Setting::updateOrCreate(['key' => 'email'], ['value' => $request->email]);
        Setting::updateOrCreate(['key' => 'footer_struk'], ['value' => $request->footer_struk]);

        return back()->with('success', 'Setting berhasil diupdate!');
    }

    // Halaman User Management
    public function users()
    {
        $users = User::orderBy('name', 'asc')->get();
        
        return view('pages.setting.users', compact('users'));
    }

    // Halaman Form Tambah User
    public function createUser()
    {
        return view('pages.setting.create-user');
    }

    // Proses Tambah User
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,kasir',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('setting.users')
                       ->with('success', 'User berhasil ditambahkan!');
    }

    // Halaman Edit User
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        
        return view('pages.setting.edit-user', compact('user'));
    }

    // Proses Update User
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,kasir',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('setting.users')
                       ->with('success', 'User berhasil diupdate!');
    }

    // Hapus User
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // Tidak bisa hapus diri sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        // Tidak bisa hapus admin terakhir
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus admin terakhir!');
        }

        $user->delete();

        return redirect()->route('setting.users')
                       ->with('success', 'User berhasil dihapus!');
    }

    // Halaman Profil User
    public function profile()
    {
        $user = Auth::user();
        
        return view('pages.setting.profile', compact('user'));
    }

    // Update Profil
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profil berhasil diupdate!');
    }

    // Update Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai!');
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diupdate!');
    }

    // Backup Database (opsional)
    public function backup()
    {
        // Implementasi backup database
        // Bisa menggunakan command mysqldump atau library backup
        
        return back()->with('info', 'Fitur backup sedang dalam pengembangan');
    }
}