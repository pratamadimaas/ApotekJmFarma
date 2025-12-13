<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Cabang;

class CabangFilterController extends Controller
{
    /**
     * Set filter cabang ke session (untuk Super Admin)
     * Admin Cabang dan Kasir tidak bisa mengubah filter (fixed ke cabang mereka)
     */
    public function setCabangFilter(Request $request)
    {
        $user = auth()->user();

        // Hanya Super Admin yang bisa mengatur filter
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda tidak memiliki akses untuk mengubah filter cabang'
            ], 403);
        }

        $request->validate([
            'cabang_id' => 'nullable|exists:cabang,id'
        ]);

        $cabangId = $request->cabang_id;
        
        // Set atau hapus session filter cabang
        if ($cabangId) {
            Session::put('selected_cabang_id', $cabangId);
            
            $cabang = Cabang::find($cabangId);
            $message = 'Filter diatur ke: ' . ($cabang ? $cabang->nama_cabang : 'Cabang');
        } else {
            Session::forget('selected_cabang_id');
            $message = 'Menampilkan semua cabang';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'cabang_id' => $cabangId
        ]);
    }

    /**
     * Get filter cabang ID yang aktif
     * Logic:
     * - Super Admin: bisa pilih cabang via session, atau null (semua cabang)
     * - Admin Cabang & Kasir: selalu cabang mereka sendiri
     */
    public static function getFilteredCabangId()
    {
        $user = auth()->user();

        // Super Admin bisa filter via session
        if ($user->isSuperAdmin()) {
            return Session::get('selected_cabang_id'); // null = semua cabang
        }

        // Admin Cabang & Kasir hanya bisa lihat cabangnya sendiri
        return $user->cabang_id;
    }

    /**
     * Get nama cabang yang sedang difilter
     */
    public static function getFilteredCabangName()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $cabangId = Session::get('selected_cabang_id');
            
            if (!$cabangId) {
                return 'Semua Cabang';
            }
            
            $cabang = Cabang::find($cabangId);
            return $cabang ? $cabang->nama_cabang : 'Cabang Tidak Ditemukan';
        }

        // Admin Cabang & Kasir
        $cabang = $user->cabang;
        return $cabang ? $cabang->nama_cabang : 'Tanpa Cabang';
    }

    /**
     * Check apakah sedang melihat cabang spesifik
     * True jika: Super Admin memilih cabang tertentu, atau user bukan Super Admin
     */
    public static function isViewingSpecificCabang()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return Session::has('selected_cabang_id');
        }

        // Non-super admin selalu lihat cabang tertentu (milik mereka)
        return true;
    }

    /**
     * Get current filter info untuk API
     */
    public function getCabangFilter()
    {
        $cabangId = self::getFilteredCabangId();
        $cabangName = self::getFilteredCabangName();
        
        return response()->json([
            'success' => true,
            'cabang_id' => $cabangId,
            'cabang_name' => $cabangName,
            'is_viewing_specific' => self::isViewingSpecificCabang()
        ]);
    }

    /**
     * Clear filter cabang (hanya Super Admin)
     */
    public function clearCabangFilter()
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized'
            ], 403);
        }

        Session::forget('selected_cabang_id');
        
        return response()->json([
            'success' => true,
            'message' => 'Filter cabang berhasil dihapus. Menampilkan semua cabang.'
        ]);
    }
}