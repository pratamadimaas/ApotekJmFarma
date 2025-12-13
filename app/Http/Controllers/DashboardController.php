<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\User;
use App\Models\Shift;
use App\Models\DetailPenjualan;
use App\Models\Cabang;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use CabangFilterTrait;

    public function index()
    {
        $user = auth()->user();
        $cabangId = $this->getActiveCabangId();
        
        // ✅ WORKAROUND: Ambil nama cabang langsung tanpa method trait
        if ($cabangId) {
            $cabang = Cabang::find($cabangId);
            $cabangName = $cabang ? $cabang->nama_cabang : 'Cabang Tidak Ditemukan';
        } else {
            $cabangName = 'Semua Cabang'; // Untuk Super Admin
        }

        // 1. STATISTIK DENGAN FILTER CABANG
        
        // Total Penjualan Hari Ini
        $penjualanHariIni = Penjualan::whereDate('tanggal_penjualan', today())
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->sum('grand_total');

        // Total Penjualan Bulan Ini
        $penjualanBulanIni = Penjualan::whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year)
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->sum('grand_total');

        // Total Transaksi Hari Ini
        $transaksiHariIni = Penjualan::whereDate('tanggal_penjualan', today())
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->count();

        // Jumlah Barang dengan Stok Minimum (Stok Menipis)
        $barangStokMinimum = Barang::whereColumn('stok', '<=', 'stok_minimal')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->count();
        
        // 🔒 LABA: HANYA UNTUK ADMIN & SUPER ADMIN (KASIR TIDAK BISA LIHAT)
        $labaHariIni = 0;
        $labaBulanIni = 0;
        
        if (!$user->isKasir()) {
            $labaHariIni = $this->hitungLaba('today', $cabangId);
            $labaBulanIni = $this->hitungLaba('thisMonth', $cabangId);
        }

        // 2. DATA SHIFT (Untuk tampilan Kasir/Admin Cabang)
        $shiftAktif = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->whereNull('waktu_tutup')
            ->first();
        
        // ✅ PERBAIKAN: Semua Shift Aktif dengan TOTAL PENJUALAN REAL-TIME
        $shiftAktifSemua = Shift::where('status', 'open')
            ->whereNull('waktu_tutup')
            ->with('user')
            ->when($cabangId, fn($q) => $q->whereHas('user', fn($q_user) => $q_user->where('cabang_id', $cabangId)))
            ->orderBy('waktu_buka', 'desc')
            ->get()
            ->map(function($shift) {
                // ✅ HITUNG TOTAL PENJUALAN PER SHIFT (REAL-TIME)
                $totalPenjualan = Penjualan::where('shift_id', $shift->id)->sum('grand_total');
                $jumlahTransaksi = Penjualan::where('shift_id', $shift->id)->count();
                
                // Tambahkan data ke object shift
                $shift->total_penjualan_realtime = $totalPenjualan;
                $shift->jumlah_transaksi_realtime = $jumlahTransaksi;
                
                return $shift;
            });

        // 3. GRAFIK PENJUALAN 7 HARI TERAKHIR
        $grafikPenjualan = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            
            $total = Penjualan::whereDate('tanggal_penjualan', $tanggal)
                ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                ->sum('grand_total');
            
            $grafikPenjualan[] = [
                'tanggal' => $tanggal->format('d/m'),
                'hari' => $tanggal->isoFormat('dddd'),
                'total' => $total
            ];
        }

        // 4. TOP 5 BARANG TERLARIS BULAN INI
        $topBarang = DB::table('detail_penjualan')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            ->join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->whereMonth('penjualan.tanggal_penjualan', now()->month)
            ->whereYear('penjualan.tanggal_penjualan', now()->year)
            ->when($cabangId, fn($q) => $q->where('penjualan.cabang_id', $cabangId))
            ->select(
                'barang.nama_barang',
                'barang.stok',
                DB::raw('SUM(detail_penjualan.jumlah) as total_terjual'),
                DB::raw('SUM(detail_penjualan.subtotal) as total_pendapatan')
            )
            ->groupBy('barang.id', 'barang.nama_barang', 'barang.stok')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $barangTerlaris = $topBarang;
        
        // 5. USER STATS (Admin Only)
        $totalUser = null;
        if ($user->role === 'super_admin' || $user->role === 'admin_cabang') {
            $totalUser = User::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                ->count();
        }

        // 6. Barang dengan Stok Hampir Habis (Top 10)
        $barangHabis = Barang::whereColumn('stok', '<=', 'stok_minimal')
            ->where('aktif', 1)
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('stok', 'asc')
            ->limit(10)
            ->get();
        
        $penjualan7Hari = $grafikPenjualan;

        return view('pages.dashboard', compact(
            'penjualanHariIni',
            'transaksiHariIni',
            'penjualanBulanIni',
            'labaHariIni',
            'labaBulanIni',
            'barangStokMinimum',
            'shiftAktif',
            'shiftAktifSemua',
            'topBarang',
            'barangTerlaris',
            'grafikPenjualan',
            'penjualan7Hari',
            'barangHabis',
            'totalUser',
            'cabangName'
        ));
    }
    
    /**
     * Menghitung Laba Berdasarkan Period dan Cabang
     */
    private function hitungLaba($period, $cabangId = null)
    {
        $query = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id');
        
        if ($period === 'today') {
            $query->whereDate('penjualan.tanggal_penjualan', Carbon::today());
        } elseif ($period === 'thisMonth') {
            $query->whereMonth('penjualan.tanggal_penjualan', now()->month)
                  ->whereYear('penjualan.tanggal_penjualan', now()->year);
        }
        
        if ($cabangId) {
            $query->where('penjualan.cabang_id', $cabangId);
        }
        
        $hasil = $query->select(
            DB::raw('SUM(detail_penjualan.subtotal) as total_penjualan'),
            DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp')
        )->first();
        
        $totalPenjualan = $hasil->total_penjualan ?? 0;
        $totalHpp = $hasil->total_hpp ?? 0;
        
        return $totalPenjualan - $totalHpp;
    }
}