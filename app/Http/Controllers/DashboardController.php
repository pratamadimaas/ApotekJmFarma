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
        
        if ($cabangId) {
            $cabang = Cabang::find($cabangId);
            $cabangName = $cabang ? $cabang->nama_cabang : 'Cabang Tidak Ditemukan';
        } else {
            $cabangName = 'Semua Cabang';
        }

        // 1. STATISTIK DENGAN FILTER CABANG (EXCLUDE RETURN)
        
        // Total Penjualan Hari Ini (sudah dikurangi return)
        $penjualanKotor = Penjualan::whereDate('tanggal_penjualan', today())
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->sum('grand_total');

        $returnHariIni = DetailPenjualan::where('is_return', true)
            ->whereDate('return_date', today())
            ->when($cabangId, fn($q) => $q->whereHas('penjualan', fn($pq) => $pq->where('cabang_id', $cabangId)))
            ->sum('jumlah_return') ?? 0;

        $penjualanHariIni = $penjualanKotor - $returnHariIni;

        // Total Penjualan Bulan Ini (sudah dikurangi return)
        $penjualanKotorBulan = Penjualan::whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year)
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->sum('grand_total');

        $returnBulanIni = DetailPenjualan::where('is_return', true)
            ->whereMonth('return_date', now()->month)
            ->whereYear('return_date', now()->year)
            ->when($cabangId, fn($q) => $q->whereHas('penjualan', fn($pq) => $pq->where('cabang_id', $cabangId)))
            ->sum('jumlah_return') ?? 0;

        $penjualanBulanIni = $penjualanKotorBulan - $returnBulanIni;

        // Total Transaksi Hari Ini
        $transaksiHariIni = Penjualan::whereDate('tanggal_penjualan', today())
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->count();

        // Jumlah Barang dengan Stok Minimum
        $barangStokMinimum = Barang::whereColumn('stok', '<=', 'stok_minimal')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->count();
        
        // 🔒 LABA: HANYA UNTUK ADMIN & SUPER ADMIN
        $labaHariIni = 0;
        $labaBulanIni = 0;
        
        if (!$user->isKasir()) {
            $labaHariIni = $this->hitungLaba('today', $cabangId);
            $labaBulanIni = $this->hitungLaba('thisMonth', $cabangId);
        }

        // 2. DATA SHIFT
        $shiftAktif = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->whereNull('waktu_tutup')
            ->first();
        
        $shiftAktifSemua = Shift::where('status', 'open')
            ->whereNull('waktu_tutup')
            ->with('user')
            ->when($cabangId, fn($q) => $q->whereHas('user', fn($q_user) => $q_user->where('cabang_id', $cabangId)))
            ->orderBy('waktu_buka', 'desc')
            ->get()
            ->map(function($shift) {
                $totalPenjualan = Penjualan::where('shift_id', $shift->id)->sum('grand_total');
                $jumlahTransaksi = Penjualan::where('shift_id', $shift->id)->count();
                
                $shift->total_penjualan_realtime = $totalPenjualan;
                $shift->jumlah_transaksi_realtime = $jumlahTransaksi;
                
                return $shift;
            });

        // 3. GRAFIK PENJUALAN 7 HARI TERAKHIR (EXCLUDE RETURN)
        $grafikPenjualan = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = Carbon::today()->subDays($i);
    
    $totalKotor = Penjualan::whereDate('tanggal_penjualan', $tanggal)
        ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
        ->sum('grand_total');
    
    // ✅ Kurangi return di hari tersebut
    $totalReturn = DetailPenjualan::where('is_return', true)
        ->whereDate('return_date', $tanggal)
        ->when($cabangId, fn($q) => $q->whereHas('penjualan', fn($pq) => $pq->where('cabang_id', $cabangId)))
        ->sum('jumlah_return') ?? 0;
    
    $total = $totalKotor - $totalReturn;
    
    // ✅ PERBAIKAN: Gunakan format yang konsisten untuk JavaScript
    $grafikPenjualan[] = [
        'tanggal' => $tanggal->format('Y-m-d'), // Format ISO standar
        'label' => $tanggal->format('d/m'), // Label untuk display
        'hari' => $tanggal->isoFormat('dddd'),
        'total' => $total
    ];
}

        // 4. TOP 5 BARANG TERLARIS BULAN INI (EXCLUDE RETURN)
        $topBarang = DB::table('detail_penjualan')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            ->join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->whereMonth('penjualan.tanggal_penjualan', now()->month)
            ->whereYear('penjualan.tanggal_penjualan', now()->year)
            ->where(function($q) {
                $q->where('detail_penjualan.is_return', false)
                  ->orWhereNull('detail_penjualan.is_return');
            })
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
        
        // 5. USER STATS
        $totalUser = null;
        if ($user->role === 'super_admin' || $user->role === 'admin_cabang') {
            $totalUser = User::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                ->count();
        }

        // 6. Barang dengan Stok Hampir Habis
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
     * Menghitung Laba Berdasarkan Period dan Cabang (EXCLUDE RETURN)
     */
    private function hitungLaba($period, $cabangId = null)
    {
        $query = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
            ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            // ✅ EXCLUDE barang yang sudah di-return
            ->where(function($q) {
                $q->where('detail_penjualan.is_return', false)
                  ->orWhereNull('detail_penjualan.is_return');
            });
        
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
        
        // ✅ Kurangi dengan total return
        $returnQuery = DetailPenjualan::where('is_return', true);
        
        if ($period === 'today') {
            $returnQuery->whereDate('return_date', Carbon::today());
        } elseif ($period === 'thisMonth') {
            $returnQuery->whereMonth('return_date', now()->month)
                        ->whereYear('return_date', now()->year);
        }
        
        if ($cabangId) {
            $returnQuery->whereHas('penjualan', function($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId);
            });
        }
        
        $totalReturn = $returnQuery->sum('jumlah_return') ?? 0;
        
        return ($totalPenjualan - $totalReturn) - $totalHpp;
    }
}