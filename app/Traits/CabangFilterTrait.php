<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Cabang;

trait CabangFilterTrait
{
    /**
     * Ambil Cabang ID yang sedang aktif
     * ✅ DENGAN CACHE CLEAR
     */
    protected function getActiveCabangId()
    {
        // ✅ Clear cache dulu
        $this->clearCabangCache();
        
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('getActiveCabangId: User not authenticated');
            return null;
        }
        
        // Super Admin: baca dari session
        if ($this->isSuperAdmin()) {
            $selectedCabangId = Session::get('selected_cabang_id');
            
            Log::info('getActiveCabangId: Super Admin', [
                'user_id' => $user->id,
                'session_cabang_id' => $selectedCabangId
            ]);
            
            return $selectedCabangId;
        }
        
        // User biasa: ambil dari cabang_id
        $cabangId = $user->cabang_id;
        
        Log::info('getActiveCabangId: Regular User', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'cabang_id' => $cabangId
        ]);
        
        if ($cabangId === null) {
            Log::error('getActiveCabangId: cabang_id is NULL!', [
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
        }
        
        return $cabangId;
    }

    /**
     * ✅ CLEAR CACHE METHOD
     */
    protected function clearCabangCache()
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        // Tentukan cabang yang akan di-clear cache-nya
        $cabangIds = [];
        
        if ($this->isSuperAdmin()) {
            $selectedCabangId = Session::get('selected_cabang_id');
            if ($selectedCabangId) {
                $cabangIds[] = $selectedCabangId;
            }
            // Juga clear cache "all cabang" untuk super admin
            $cabangIds[] = 'all';
        } else {
            $cabangIds[] = $user->cabang_id;
        }
        
        // Clear cache untuk setiap cabang
        foreach ($cabangIds as $cabangId) {
            Cache::forget('barang_list_' . $cabangId);
            Cache::forget('barang_kategori_' . $cabangId);
            Cache::forget('cabang_data_' . $cabangId);
        }
        
        // Clear query cache MySQL (jika support)
        try {
            DB::unprepared('RESET QUERY CACHE');
        } catch (\Exception $e) {
            // Ignore jika tidak support
        }
    }

    /**
     * Ambil nama cabang yang sedang aktif
     */
    public function getActiveCabangName()
    {
        $cabangId = $this->getActiveCabangId();
        
        if (!$cabangId) {
            return 'Semua Cabang';
        }
        
        $cabang = Cabang::find($cabangId);
        
        if (!$cabang) {
            Log::warning('getActiveCabangName: Cabang not found', [
                'cabang_id' => $cabangId
            ]);
            return 'Cabang Tidak Ditemukan';
        }
        
        return $cabang->nama_cabang;
    }

    /**
     * Apply filter cabang ke query builder
     */
    protected function applyCabangFilter($query)
    {
        $cabangId = $this->getActiveCabangId();
        
        Log::info('applyCabangFilter', [
            'cabang_id' => $cabangId,
            'is_super_admin' => $this->isSuperAdmin()
        ]);
        
        if ($cabangId === null) {
            Log::info('applyCabangFilter: No filter (showing all)');
            return $query;
        }
        
        Log::info('applyCabangFilter: Filtering by cabang_id', [
            'cabang_id' => $cabangId
        ]);
        
        return $query->where('cabang_id', $cabangId);
    }

    /**
     * Cek akses ke cabang tertentu
     */
    protected function hasAccessToCabang($cabangId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $user->cabang_id == $cabangId;
    }

    /**
     * Validasi akses cabang atau abort 403
     */
    protected function validateCabangAccess($cabangId)
    {
        if (!$this->hasAccessToCabang($cabangId)) {
            abort(403, 'Akses ditolak. Data ini bukan milik cabang Anda.');
        }
    }

    /**
     * Role checkers
     */
    protected function isSuperAdmin()
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Cek via method jika ada
        if (method_exists($user, 'isSuperAdmin')) {
            return $user->isSuperAdmin();
        }
        
        // Fallback: cek role langsung
        return $user->role === 'super_admin';
    }

    protected function isAdminCabang()
    {
        $user = auth()->user();
        return $user && $user->role === 'admin_cabang';
    }

    protected function isKasir()
    {
        $user = auth()->user();
        return $user && $user->role === 'kasir';
    }
}