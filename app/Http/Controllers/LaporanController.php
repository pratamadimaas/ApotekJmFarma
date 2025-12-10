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
use App\Exports\PenjualanExport; 
use Maatwebsite\Excel\Facades\Excel; 
use PDF; 

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman utama Laporan (Menu Pilihan Laporan).
     */
    public function index()
    {
        return view('pages.laporan.index');
    }

    // =========================================================================
    // FUNGSI PEMBANTU
    // =========================================================================

    /**
     * Fungsi Pembantu untuk Mengatur Query dan Filter berdasarkan tanggal.
     * @param Request $request
     * @param string $model Nama class Model (misal: Penjualan::class)
     * @param string $dateColumn Nama kolom tanggal di tabel utama
     * @param string|null $status Opsional, untuk filter status (misal: 'approved' untuk pembelian)
     * @return array
     */
    private function applyDateFilter(Request $request, $model, $dateColumn, $status = null)
    {
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


    // =========================================================================
    // LAPORAN TRANSAKSI & KEUANGAN
    // =========================================================================

    /**
     * Laporan Penjualan.
     */
    public function penjualan(Request $request)
    {
        $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];

        $totalPenjualan = (clone $query)->sum('grand_total');
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
            'tanggalDari', 'tanggalSampai', 'totalPenjualan', 'jumlahTransaksi', 
            'perHari', 'barangTerlaris', 'perMetode'
        ));
    }

    /**
     * Laporan Pembelian.
     */
    public function pembelian(Request $request)
    {
        $data = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];
        
        $totalPembelian = (clone $query)->sum('grand_total');
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
            'tanggalDari', 'tanggalSampai', 'totalPembelian', 'jumlahTransaksi', 
            'perHari', 'barangTerbanyak', 'perSupplier'
        ));
    }


    /**
     * Laporan Laba Rugi (Gross Profit).
     */
    public function labaRugi(Request $request)
    {
        $dataPenjualan = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
        $queryPenjualan = $dataPenjualan['query'];
        $tanggalDari = $dataPenjualan['tanggalDari']; 
        $tanggalSampai = $dataPenjualan['tanggalSampai'];
        
        $totalPendapatan = (clone $queryPenjualan)->sum('grand_total');
        
        $queryPembelian = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved')['query'];
        $totalPembelian = (clone $queryPembelian)->sum('grand_total');

        // HPP (Harga Pokok Penjualan)
        $hpp = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                              ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                              ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai])
                              ->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'))
                              ->value('total_hpp') ?? 0;

        $labaKotor = $totalPendapatan - $hpp;
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
            'tanggalDari', 'tanggalSampai', 'totalPendapatan', 'totalPembelian', 'hpp', 
            'labaKotor', 'marginLaba', 'detailPerItem'
        ));
    }


    // =========================================================================
    // LAPORAN STOK
    // =========================================================================

    /**
     * Laporan Stok Barang (Nilai Inventori).
     */
    public function stok(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'habis') {
                $query->where('stok', 0);
            } elseif ($request->filter === 'minimal') {
                $query->whereRaw('stok <= stok_minimal'); 
            }
        }

        $barang = $query->orderBy('nama_barang', 'asc')->get();

        $totalNilaiStok = $barang->sum(fn($item) => $item->stok * $item->harga_beli);
        $totalNilaiJual = $barang->sum(fn($item) => $item->stok * $item->harga_jual);
        $potensialLaba = $totalNilaiJual - $totalNilaiStok;

        $kategoriList = Barang::select('kategori')
                              ->distinct()
                              ->whereNotNull('kategori')
                              ->pluck('kategori');

        return view('pages.laporan.stok', compact(
            'barang', 'totalNilaiStok', 'totalNilaiJual', 'potensialLaba', 'kategoriList'
        ));
    }

    /**
     * Laporan Kartu Stok (Mutasi Barang).
     */
   public function kartuStok(Request $request)
{
    $daftarBarang = Barang::orderBy('nama_barang')->get();
    
    $barangId = $request->barang_id;
    $tanggalDari = $request->tanggal_dari ?? now()->startOfMonth()->format('Y-m-d');
    $tanggalSampai = $request->tanggal_sampai ?? now()->format('Y-m-d');
    
    $barang = null;
    $kartuStok = collect();
    $stokAwal = 0;
    $stokAkhir = 0;
    
    if ($barangId) {
        $barang = Barang::with('satuanKonversi')->findOrFail($barangId);
        
        // Hitung stok awal (sebelum tanggal_dari)
        $stokAwal = $this->hitungStokAwal($barangId, $tanggalDari);
        
        // Ambil transaksi pembelian
        $pembelian = DetailPembelian::where('barang_id', $barangId)
            ->whereHas('pembelian', function($q) use ($tanggalDari, $tanggalSampai) {
                $q->where('status', 'approved')
                  ->whereBetween('tanggal_pembelian', [$tanggalDari, $tanggalSampai]);
            })
            ->with(['pembelian.supplier'])
            ->get()
            ->map(function($detail) use ($barang) {
                return [
                    'tanggal' => $detail->pembelian->tanggal_pembelian,
                    'nomor' => $detail->pembelian->nomor_pembelian,
                    'keterangan' => 'Pembelian dari ' . $detail->pembelian->supplier->nama_supplier,
                    'masuk' => $detail->jumlah . ' ' . $detail->satuan,
                    'keluar' => '-',
                    'sisa' => 0, // Will be calculated
                    'paraf' => '',
                    'ed' => $detail->tanggal_kadaluarsa ? \Carbon\Carbon::parse($detail->tanggal_kadaluarsa)->format('m/y') : '-',
                    'sort_date' => $detail->pembelian->tanggal_pembelian,
                    'qty_dasar' => $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang),
                    'type' => 'masuk'
                ];
            });
        
        // Ambil transaksi penjualan
        $penjualan = DetailPenjualan::where('barang_id', $barangId)
            ->whereHas('penjualan', function($q) use ($tanggalDari, $tanggalSampai) {
                $q->whereBetween('tanggal_penjualan', [$tanggalDari, $tanggalSampai]);
            })
            ->with(['penjualan'])
            ->get()
            ->map(function($detail) use ($barang) {
                return [
                    'tanggal' => $detail->penjualan->tanggal_penjualan,
                    'nomor' => $detail->penjualan->nomor_nota,
                    'keterangan' => 'Penjualan',
                    'masuk' => '-',
                    'keluar' => $detail->jumlah . ' ' . $detail->satuan,
                    'sisa' => 0,
                    'paraf' => '',
                    'ed' => '',
                    'sort_date' => $detail->penjualan->tanggal_penjualan,
                    'qty_dasar' => $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang),
                    'type' => 'keluar'
                ];
            });
        
        // Gabungkan dan urutkan berdasarkan tanggal
        $kartuStok = $pembelian->merge($penjualan)
            ->sortBy('sort_date')
            ->values();
        
        // Hitung sisa stok untuk setiap baris
        $sisa = $stokAwal;
        $kartuStok = $kartuStok->map(function($item) use (&$sisa) {
            if ($item['type'] === 'masuk') {
                $sisa += $item['qty_dasar'];
            } else {
                $sisa -= $item['qty_dasar'];
            }
            $item['sisa'] = $sisa;
            return $item;
        });
        
        $stokAkhir = $sisa;
    }
    
    return view('pages.laporan.kartu-stok', compact(
        'daftarBarang',
        'barang',
        'kartuStok',
        'stokAwal',
        'stokAkhir',
        'tanggalDari',
        'tanggalSampai'
    ));
}

// Helper: Hitung stok awal sebelum periode
private function hitungStokAwal($barangId, $tanggalDari)
{
    $barang = Barang::findOrFail($barangId);
    
    // Hitung total pembelian sebelum tanggal_dari
    $totalMasuk = DetailPembelian::where('barang_id', $barangId)
        ->whereHas('pembelian', function($q) use ($tanggalDari) {
            $q->where('status', 'approved')
              ->where('tanggal_pembelian', '<', $tanggalDari);
        })
        ->get()
        ->sum(function($detail) use ($barang) {
            return $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
        });
    
    // Hitung total penjualan sebelum tanggal_dari
    $totalKeluar = DetailPenjualan::where('barang_id', $barangId)
        ->whereHas('penjualan', function($q) use ($tanggalDari) {
            $q->where('tanggal_penjualan', '<', $tanggalDari);
        })
        ->get()
        ->sum(function($detail) use ($barang) {
            return $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
        });
    
    return $totalMasuk - $totalKeluar;
}

// Helper: Konversi ke satuan terkecil
private function convertToStokDasar($qty, $satuan, $barang)
{
    if ($satuan === $barang->satuan_terkecil) {
        return $qty;
    }
    
    $konversi = \App\Models\SatuanKonversi::where('barang_id', $barang->id)
        ->where('nama_satuan', $satuan)
        ->first();
    
    return $konversi ? ($qty * $konversi->jumlah_konversi) : $qty;
}


    // =========================================================================
    // EXPORT IMPLEMENTATION
    // =========================================================================

    /**
     * Export data laporan ke format Excel (Maatwebsite/Excel).
     */
    public function exportExcel(Request $request)
    {
        $reportType = $request->get('type', 'penjualan'); 
        
        switch ($reportType) {
            case 'penjualan':
                $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
                
                // --- KODE BARU: Mengambil DetailPenjualan untuk Export Rinci ---
                $detailPenjualan = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                                 ->whereDate('penjualan.tanggal_penjualan', '>=', $data['tanggalDari'])
                                                 ->whereDate('penjualan.tanggal_penjualan', '<=', $data['tanggalSampai'])
                                                 ->with(['penjualan.user', 'barang'])
                                                 ->select('detail_penjualan.*')
                                                 ->orderBy('penjualan.tanggal_penjualan', 'asc')
                                                 ->get();
                // -------------------------------------------------------------
                
                $fileName = "Laporan_Penjualan_{$data['tanggalDari']}_to_{$data['tanggalSampai']}.xlsx";
                return Excel::download(new PenjualanExport($detailPenjualan), $fileName);

            case 'pembelian':
                $dataPembelian = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved');
                $pembelian = $dataPembelian['query']->with('supplier')->get();
                
                // TODO: Pastikan Anda telah membuat class PembelianExport
                // return Excel::download(new PembelianExport($pembelian), "Laporan_Pembelian_{$dataPembelian['tanggalDari']}_to_{$dataPembelian['tanggalSampai']}.xlsx");
                return back()->with('warning', 'Export Pembelian belum diimplementasikan. Buat class PembelianExport terlebih dahulu.');

            // TODO: Tambahkan case 'stok', 'laba-rugi', dll.
            default:
                return back()->with('error', 'Jenis laporan tidak valid.');
        }
    }

    /**
     * Export data laporan ke format PDF (DomPDF).
     */
    public function exportPdf(Request $request)
    {
        $reportType = $request->get('type', 'penjualan'); 
        
        switch ($reportType) {
            case 'penjualan':
                $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
                // Mengambil data ringkasan untuk PDF (seperti di view penjualan)
                $penjualan = $data['query']->with('user')->get();
                $tanggalDari = $data['tanggalDari'];
                $tanggalSampai = $data['tanggalSampai'];
                
                $viewData = [
                    'penjualan' => $penjualan,
                    'tanggalDari' => $tanggalDari,
                    'tanggalSampai' => $tanggalSampai,
                    'title' => 'Laporan Penjualan',
                ];
                
                // Asumsi view pages.laporan.penjualan-pdf sudah dibuat
                $pdf = PDF::loadView('pages.laporan.penjualan-pdf', $viewData);
                $fileName = "Laporan_Penjualan_{$tanggalDari}_to_{$tanggalSampai}.pdf";
                
                return $pdf->download($fileName);

            // TODO: Tambahkan case 'pembelian', 'stok', dll.
            default:
                return back()->with('error', 'Jenis laporan tidak valid untuk PDF.');
        }
    }
}