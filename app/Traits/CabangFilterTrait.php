<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Cabang;

trait CabangFilterTrait
{
    /**
     * Ambil Cabang ID yang sedang aktif untuk user saat ini
     * 
     * Logic:
     * - Super Admin: baca dari session 'selected_cabang_id'
     *   - Jika ada session: gunakan cabang tersebut
     *   - Jika tidak ada: return null (tampilkan semua cabang)
     * - Admin Cabang / Kasir: return cabang_id dari user (tidak bisa diganti)
     * 
     * @return int|null
     */
    protected function getActiveCabangId()
    {
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('getActiveCabangId: User not authenticated');
            return null;
        }
        
        // ✅ FIXED: Super Admin bisa filter via session
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $selectedCabangId = Session::get('selected_cabang_id');
            
            Log::info('getActiveCabangId: Super Admin', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'session_cabang_id' => $selectedCabangId
            ]);
            
            // Return session value (bisa null = semua cabang, bisa ID tertentu)
            return $selectedCabangId;
        }
        
        // Fallback: cek langsung dari role
        if ($user->role === 'super_admin') {
            $selectedCabangId = Session::get('selected_cabang_id');
            
            Log::info('getActiveCabangId: Super Admin by role', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'session_cabang_id' => $selectedCabangId
            ]);
            
            return $selectedCabangId;
        }
        
        // ✅ User biasa (Admin Cabang / Kasir): ambil dari field cabang_id
        $cabangId = $user->cabang_id;
        
        Log::info('getActiveCabangId: Regular User', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'cabang_id' => $cabangId
        ]);
        
        // ⚠️ VALIDASI: Pastikan cabang_id tidak null untuk user non-super_admin
        if ($cabangId === null) {
            Log::error('getActiveCabangId: User cabang_id is NULL!', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role
            ]);
        }
        
        return $cabangId;
    }

    /**
     * Ambil nama cabang yang sedang aktif
     * 
     * @return string
     */
    public function getActiveCabangName()
    {
        $cabangId = $this->getActiveCabangId();
        
        // Untuk Super Admin tanpa filter (session kosong)
        if (!$cabangId) {
            return 'Semua Cabang';
        }
        
        // Cari nama cabang berdasarkan ID
        $cabang = Cabang::find($cabangId);
        
        if (!$cabang) {
            Log::warning('getActiveCabangName: Cabang not found', [
                'cabang_id' => $cabangId,
                'user_id' => auth()->id()
            ]);
            return 'Cabang Tidak Ditemukan';
        }
        
        return $cabang->nama_cabang;
    }

    /**
     * Apply filter cabang ke query builder
     * 
     * PENTING: Sekarang menggunakan session untuk Super Admin!
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCabangFilter($query)
    {
        $cabangId = $this->getActiveCabangId();
        
        Log::info('applyCabangFilter called', [
            'cabang_id' => $cabangId,
            'user_id' => auth()->id(),
            'is_super_admin' => auth()->user()->isSuperAdmin() ?? false
        ]);
        
        // Jika cabangId = null (Super Admin tanpa filter), tidak ada filter
        if ($cabangId === null) {
            Log::info('applyCabangFilter: No filter applied (showing all cabang)');
            return $query;
        }
        
        // Filter berdasarkan cabang yang dipilih
        Log::info('applyCabangFilter: Filtering by cabang_id', ['cabang_id' => $cabangId]);
        return $query->where('cabang_id', $cabangId);
    }

    /**
     * Cek apakah user memiliki akses ke cabang tertentu
     * 
     * @param int $cabangId
     * @return bool
     */
    protected function hasAccessToCabang($cabangId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Super Admin punya akses ke semua cabang
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        
        // Fallback: cek langsung dari role
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // User biasa: hanya akses ke cabang sendiri
        return $user->cabang_id == $cabangId;
    }

    /**
     * Validasi akses cabang atau abort 403
     * 
     * @param int $cabangId
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateCabangAccess($cabangId)
    {
        if (!$this->hasAccessToCabang($cabangId)) {
            abort(403, 'Akses ditolak. Data ini bukan milik cabang Anda.');
        }
    }

    /**
     * Cek apakah user adalah Super Admin
     * 
     * @return bool
     */
    protected function isSuperAdmin()
    {
        $user = auth()->user();
        return $user && $user->role === 'super_admin';
    }

    /**
     * Cek apakah user adalah Admin Cabang
     * 
     * @return bool
     */
    protected function isAdminCabang()
    {
        $user = auth()->user();
        return $user && $user->role === 'admin_cabang';
    }

    /**
     * Cek apakah user adalah Kasir
     * 
     * @return bool
     */
    protected function isKasir()
    {
        $user = auth()->user();
        return $user && $user->role === 'kasir';
    }
}