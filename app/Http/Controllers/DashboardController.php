<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\Shift;
use App\Models\DetailPenjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Penjualan Hari Ini
        $penjualanHariIni = Penjualan::today()->sum('grand_total');
        
        // Total Transaksi Hari Ini
        $transaksiHariIni = Penjualan::today()->count();
        
        // Total Penjualan Bulan Ini
        $penjualanBulanIni = Penjualan::thisMonth()->sum('grand_total');
        
        // ✅ BARU: Laba Hari Ini
        $labaHariIni = $this->hitungLaba('today');
        
        // ✅ BARU: Laba Bulan Ini
        $labaBulanIni = $this->hitungLaba('thisMonth');
        
        // Jumlah Barang dengan Stok Minimum
        $barangStokMinimum = Barang::stokMinimum()->count();
        
        // ✅ Shift Aktif User Login
        $shiftAktif = Shift::where('user_id', Auth::id())
            ->whereNull('waktu_tutup')
            ->first();
        
        // ✅ BARU: Semua Shift Aktif (untuk ditampilkan)
        $shiftAktifSemua = Shift::whereNull('waktu_tutup')
            ->with('user')
            ->orderBy('waktu_buka', 'desc')
            ->get();
        
        // Top 5 Barang Terlaris Bulan Ini
        $barangTerlaris = DB::table('detail_penjualan')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            ->join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->whereMonth('penjualan.tanggal_penjualan', now()->month)
            ->whereYear('penjualan.tanggal_penjualan', now()->year)
            ->select(
                'barang.nama_barang',
                DB::raw('SUM(detail_penjualan.jumlah) as total_terjual'),
                DB::raw('SUM(detail_penjualan.subtotal) as total_pendapatan')
            )
            ->groupBy('barang.id', 'barang.nama_barang')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();
        
        // Grafik Penjualan 7 Hari Terakhir
        $penjualan7Hari = Penjualan::whereBetween('tanggal_penjualan', [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay()
            ])
            ->select(
                DB::raw('DATE(tanggal_penjualan) as tanggal'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        // Barang dengan Stok Hampir Habis
        $barangHabis = Barang::stokMinimum()->aktif()->limit(10)->get();
        
        return view('pages.dashboard', compact(
            'penjualanHariIni',
            'transaksiHariIni',
            'penjualanBulanIni',
            'labaHariIni',          // ✅ BARU
            'labaBulanIni',         // ✅ BARU
            'barangStokMinimum',
            'shiftAktif',
            'shiftAktifSemua',      // ✅ BARU
            'barangTerlaris',
            'penjualan7Hari',
            'barangHabis'
        ));
    }
    
    /**
     * ✅ FUNGSI BARU: Menghitung Laba Berdasarkan Period
     * 
     * @param string $period 'today' atau 'thisMonth'
     * @return float
     */
    private function hitungLaba($period)
    {
        $query = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id');
        
        // Filter berdasarkan periode
        if ($period === 'today') {
            $query->whereDate('penjualan.tanggal_penjualan', Carbon::today());
        } elseif ($period === 'thisMonth') {
            $query->whereMonth('penjualan.tanggal_penjualan', now()->month)
                  ->whereYear('penjualan.tanggal_penjualan', now()->year);
        }
        
        // Hitung: Total Penjualan - Total HPP
        $hasil = $query->select(
            DB::raw('SUM(detail_penjualan.subtotal) as total_penjualan'),
            DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp')
        )->first();
        
        $totalPenjualan = $hasil->total_penjualan ?? 0;
        $totalHpp = $hasil->total_hpp ?? 0;
        
        return $totalPenjualan - $totalHpp;
    }
}