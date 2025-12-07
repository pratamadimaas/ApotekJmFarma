<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\DetailPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // Halaman Utama Laporan
    public function index()
    {
        return view('pages.laporan.index');
    }

    // Fungsi Pembantu untuk Mengatur Query dan Filter
    private function applyDateFilter(Request $request, $model, $dateColumn, $status = null)
    {
        // Jika filter tanggal tidak diisi, gunakan range 1 tahun ke belakang
        $tanggalDari = $request->get('tanggal_dari', Carbon::now()->subYear()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', Carbon::now()->format('Y-m-d'));

        $query = $model::query();

        if ($status) {
            $query->where('status', $status);
        }

        $query->whereDate($dateColumn, '>=', $tanggalDari)
              ->whereDate($dateColumn, '<=', $tanggalSampai);

        return [
            'query' => $query,
            'tanggalDari' => $tanggalDari,
            'tanggalSampai' => $tanggalSampai,
        ];
    }


    // ✅ Laporan Penjualan - FILTER KOREKSI
    public function penjualan(Request $request)
    {
        // Mendapatkan query dasar dan tanggal filter yang diperluas
        $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];

        // Total penjualan
        $totalPenjualan = (clone $query)->sum('grand_total');

        // Jumlah transaksi
        $jumlahTransaksi = (clone $query)->count();

        // Per hari
        $perHari = (clone $query)
                           ->select(
                               DB::raw('DATE(tanggal_penjualan) as tanggal'),
                               DB::raw('COUNT(*) as jumlah_transaksi'),
                               DB::raw('SUM(grand_total) as total')
                           )
                           ->groupBy('tanggal')
                           ->orderBy('tanggal', 'asc')
                           ->get();

        // Barang terlaris
        $barangTerlaris = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                         ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai])
                                         ->select(
                                             'detail_penjualan.barang_id',
                                             DB::raw('SUM(detail_penjualan.jumlah) as total_qty'),
                                             DB::raw('SUM(detail_penjualan.subtotal) as total_omzet')
                                         )
                                         ->groupBy('detail_penjualan.barang_id')
                                         ->orderBy('total_qty', 'desc')
                                         ->limit(10)
                                         ->with('barang')
                                         ->get();

        // Per metode pembayaran
        $perMetode = (clone $query)
                            ->select(
                                'metode_pembayaran',
                                DB::raw('COUNT(*) as jumlah'),
                                DB::raw('SUM(grand_total) as total')
                            )
                            ->groupBy('metode_pembayaran')
                            ->get();

        return view('pages.laporan.penjualan', compact(
            'tanggalDari',
            'tanggalSampai',
            'totalPenjualan',
            'jumlahTransaksi',
            'perHari',
            'barangTerlaris',
            'perMetode'
        ));
    }

    // ✅ Laporan Pembelian - FILTER KOREKSI
    public function pembelian(Request $request)
    {
        // Menggunakan status 'approved'
        $data = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];
        
        // Total pembelian
        $totalPembelian = (clone $query)->sum('grand_total');

        // Jumlah transaksi
        $jumlahTransaksi = (clone $query)->count();

        // Per hari
        $perHari = (clone $query)
                           ->select(
                               DB::raw('DATE(tanggal_pembelian) as tanggal'),
                               DB::raw('COUNT(*) as jumlah_transaksi'),
                               DB::raw('SUM(grand_total) as total')
                           )
                           ->groupBy('tanggal')
                           ->orderBy('tanggal', 'asc')
                           ->get();

        // Barang paling banyak dibeli
        $barangTerbanyak = DetailPembelian::join('pembelian', 'detail_pembelian.pembelian_id', '=', 'pembelian.id')
                                         ->whereBetween('pembelian.tanggal_pembelian', [$tanggalDari, $tanggalSampai])
                                         ->where('pembelian.status', 'approved')
                                         ->select(
                                             'detail_pembelian.barang_id',
                                             DB::raw('SUM(detail_pembelian.jumlah) as total_qty'),
                                             DB::raw('SUM(detail_pembelian.subtotal) as total_harga')
                                         )
                                         ->groupBy('detail_pembelian.barang_id')
                                         ->orderBy('total_qty', 'desc')
                                         ->limit(10)
                                         ->with('barang')
                                         ->get();

        // Per supplier
        $perSupplier = (clone $query)
                            ->select(
                                'supplier_id',
                                DB::raw('COUNT(*) as jumlah'),
                                DB::raw('SUM(grand_total) as total')
                            )
                            ->groupBy('supplier_id')
                            ->with('supplier')
                            ->orderBy('total', 'desc')
                            ->get();

        return view('pages.laporan.pembelian', compact(
            'tanggalDari',
            'tanggalSampai',
            'totalPembelian',
            'jumlahTransaksi',
            'perHari',
            'barangTerbanyak',
            'perSupplier'
        ));
    }

    // ✅ Laporan Laba Rugi - FILTER KOREKSI
    public function labaRugi(Request $request)
    {
        // Filter Penjualan (Pendapatan)
        $dataPenjualan = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
        $queryPenjualan = $dataPenjualan['query'];
        $tanggalDari = $dataPenjualan['tanggalDari']; // Ambil tanggal dari sini untuk HPP
        $tanggalSampai = $dataPenjualan['tanggalSampai'];
        
        // Total pendapatan (penjualan)
        $totalPendapatan = (clone $queryPenjualan)->sum('grand_total');

        // Filter Pembelian (Biaya, hanya approved)
        $queryPembelian = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved')['query'];
        
        // Total pembelian (biaya)
        $totalPembelian = (clone $queryPembelian)->sum('grand_total');

        // HPP (Harga Pokok Penjualan) - menggunakan tanggal dari filter penjualan
        $hpp = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                             ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                             ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai])
                             ->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'))
                             ->value('total_hpp') ?? 0;

        // Laba kotor
        $labaKotor = $totalPendapatan - $hpp;

        // Margin laba
        $marginLaba = $totalPendapatan > 0 ? ($labaKotor / $totalPendapatan) * 100 : 0;

        // Detail per item
        $detailPerItem = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                       ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                                       ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai])
                                       ->select(
                                           'detail_penjualan.barang_id',
                                           'barang.nama_barang',
                                           DB::raw('SUM(detail_penjualan.jumlah) as total_qty'),
                                           DB::raw('SUM(detail_penjualan.subtotal) as total_penjualan'),
                                           DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'),
                                           DB::raw('SUM(detail_penjualan.subtotal) - SUM(detail_penjualan.jumlah * barang.harga_beli) as laba')
                                       )
                                       ->groupBy('detail_penjualan.barang_id', 'barang.nama_barang')
                                       ->orderBy('laba', 'desc')
                                       ->get();

        return view('pages.laporan.laba-rugi', compact(
            'tanggalDari',
            'tanggalSampai',
            'totalPendapatan',
            'totalPembelian',
            'hpp',
            'labaKotor',
            'marginLaba',
            'detailPerItem'
        ));
    }

    // ✅ Laporan Stok Barang - KODE AMAN
    public function stok(Request $request)
    {
        $query = Barang::query();
        // ... (Filter logic) ...
        // ... (Perhitungan Nilai Stok) ...
        // ... (return view) ...
        
        // --- Karena kode stok logic Anda aman, saya biarkan ---
        
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'habis') {
                $query->where('stok', 0);
            } elseif ($request->filter === 'minimal') {
                $query->whereRaw('stok <= stok_minimum');
            }
        }

        $barang = $query->orderBy('nama_barang', 'asc')->get();

        // Total nilai stok
        $totalNilaiStok = $barang->sum(function($item) {
            return $item->stok * $item->harga_beli;
        });

        // Total nilai jual stok
        $totalNilaiJual = $barang->sum(function($item) {
            return $item->stok * $item->harga_jual;
        });

        // Potensial laba
        $potensialLaba = $totalNilaiJual - $totalNilaiStok;

        $kategoriList = Barang::select('kategori')
                              ->distinct()
                              ->whereNotNull('kategori')
                              ->pluck('kategori');

        return view('pages.laporan.stok', compact(
            'barang',
            'totalNilaiStok',
            'totalNilaiJual',
            'potensialLaba',
            'kategoriList'
        ));
    }

    // Export Laporan
    public function exportExcel(Request $request)
    {
        // Implementasi export Excel
        return back()->with('info', 'Fitur export Excel sedang dalam pengembangan');
    }

    public function exportPdf(Request $request)
    {
        // Implementasi export PDF
        return back()->with('info', 'Fitur export PDF sedang dalam pengembangan');
    }
}