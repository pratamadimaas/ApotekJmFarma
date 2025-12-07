<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        
        // Jumlah Barang dengan Stok Minimum
        $barangStokMinimum = Barang::stokMinimum()->count();
        
        // ✅ PERBAIKAN: Shift Aktif - ubah 'open' menjadi 'aktif' atau gunakan whereNull
        $shiftAktif = Shift::where('user_id', Auth::id())
            ->whereNull('waktu_tutup')  // ✅ Cara terbaik: cek waktu_tutup NULL
            ->first();
        
        // Alternative jika pakai kolom status:
        // $shiftAktif = Shift::where('user_id', Auth::id())
        //     ->where('status', 'aktif')  // ✅ Sesuai dengan ShiftController
        //     ->first();
        
        // Top 5 Barang Terlaris Bulan Ini
        $barangTerlaris = DB::table('detail_penjualan')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            ->join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->whereMonth('penjualan.tanggal_penjualan', now()->month)
            ->whereYear('penjualan.tanggal_penjualan', now()->year)
            ->select(
                'barang.nama_barang',
                DB::raw('SUM(detail_penjualan.jumlah) as total_terjual'),  // ✅ Ubah qty ke jumlah
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
            'barangStokMinimum',
            'shiftAktif',
            'barangTerlaris',
            'penjualan7Hari',
            'barangHabis'
        ));
    }
}