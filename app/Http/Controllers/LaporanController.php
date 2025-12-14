<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\DetailPembelian;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\PenjualanExport;
use App\Exports\PembelianExport; // ✅ IMPORT CLASS BARU
use Maatwebsite\Excel\Facades\Excel; 
use PDF; 

class LaporanController extends Controller
{
    use CabangFilterTrait;

    public function index()
    {
        return view('pages.laporan.index');
    }

    // =========================================================================
    // FUNGSI PEMBANTU
    // =========================================================================

    private function applyDateFilter(Request $request, $model, $dateColumn, $status = null)
    {
        $tanggalDari = $request->get('tanggal_dari', Carbon::now()->subYear()->format('Y-m-d'));
        $tanggalSampai = $request->get('tanggal_sampai', Carbon::now()->format('Y-m-d'));

        $query = $model::query();
        $query = $this->applyCabangFilter($query);

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

    public function penjualan(Request $request)
    {
        $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];

        $totalPenjualan = (clone $query)->sum('grand_total');
        $jumlahTransaksi = (clone $query)->count();

        $perHari = (clone $query)
                        ->select(
                            DB::raw('DATE(tanggal_penjualan) as tanggal'),
                            DB::raw('COUNT(*) as jumlah_transaksi'),
                            DB::raw('SUM(grand_total) as total')
                        )
                        ->groupBy('tanggal')
                        ->orderBy('tanggal', 'asc')
                        ->get();

        $cabangId = $this->getActiveCabangId();
        
        $barangTerlarisQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                         ->whereBetween('penjualan.tanggal_penjualan', [$tanggalDari, $tanggalSampai]);
        
        if ($cabangId !== null) {
            $barangTerlarisQuery->where('penjualan.cabang_id', $cabangId);
        }
        
        $barangTerlaris = $barangTerlarisQuery->select(
                                             'detail_penjualan.barang_id',
                                             DB::raw('SUM(detail_penjualan.jumlah) as total_qty'),
                                             DB::raw('SUM(detail_penjualan.subtotal) as total_omzet')
                                         )
                                         ->groupBy('detail_penjualan.barang_id')
                                         ->orderBy('total_qty', 'desc')
                                         ->limit(10)
                                         ->with('barang')
                                         ->get();

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

    public function pembelian(Request $request)
    {
        $data = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved');
        $query = $data['query'];
        $tanggalDari = $data['tanggalDari'];
        $tanggalSampai = $data['tanggalSampai'];
        
        $totalPembelian = (clone $query)->sum('grand_total');
        $jumlahTransaksi = (clone $query)->count();

        $perHari = (clone $query)
                        ->select(
                            DB::raw('DATE(tanggal_pembelian) as tanggal'),
                            DB::raw('COUNT(*) as jumlah_transaksi'),
                            DB::raw('SUM(grand_total) as total')
                        )
                        ->groupBy('tanggal')
                        ->orderBy('tanggal', 'asc')
                        ->get();

        $cabangId = $this->getActiveCabangId();
        
        $barangTerbanyakQuery = DetailPembelian::join('pembelian', 'detail_pembelian.pembelian_id', '=', 'pembelian.id')
                                         ->whereBetween('pembelian.tanggal_pembelian', [$tanggalDari, $tanggalSampai])
                                         ->where('pembelian.status', 'approved');
        
        if ($cabangId !== null) {
            $barangTerbanyakQuery->where('pembelian.cabang_id', $cabangId);
        }
        
        $barangTerbanyak = $barangTerbanyakQuery->select(
                                             'detail_pembelian.barang_id',
                                             DB::raw('SUM(detail_pembelian.jumlah) as total_qty'),
                                             DB::raw('SUM(detail_pembelian.subtotal) as total_harga')
                                         )
                                         ->groupBy('detail_pembelian.barang_id')
                                         ->orderBy('total_qty', 'desc')
                                         ->limit(10)
                                         ->with('barang')
                                         ->get();

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

    public function labaRugi(Request $request)
{
    $dataPenjualan = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
    $queryPenjualan = $dataPenjualan['query'];
    $tanggalDari = $dataPenjualan['tanggalDari']; 
    $tanggalSampai = $dataPenjualan['tanggalSampai'];
    
    $totalPendapatan = (clone $queryPenjualan)->sum('grand_total');
    
    $queryPembelian = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved')['query'];
    $totalPembelian = (clone $queryPembelian)->sum('grand_total');

    $cabangId = $this->getActiveCabangId();
    
    // ✅ HPP dari penjualan (hanya yang TIDAK di-return)
    $hppQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                          ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                          ->whereDate('penjualan.tanggal_penjualan', '>=', $tanggalDari)
                          ->whereDate('penjualan.tanggal_penjualan', '<=', $tanggalSampai)
                          ->where(function($q) {
                              $q->where('detail_penjualan.is_return', false)
                                ->orWhereNull('detail_penjualan.is_return');
                          });
    
    if ($cabangId !== null) {
        $hppQuery->where('penjualan.cabang_id', $cabangId);
    }
    
    $hpp = $hppQuery->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'))
                    ->value('total_hpp') ?? 0;

    // ✅ Total return berdasarkan KAPAN return terjadi (pakai whereDate)
    $totalReturnQuery = DetailPenjualan::where('is_return', true)
                                ->whereDate('return_date', '>=', $tanggalDari)
                                ->whereDate('return_date', '<=', $tanggalSampai);
    
    if ($cabangId !== null) {
        $totalReturnQuery->whereHas('penjualan', function($q) use ($cabangId) {
            $q->where('cabang_id', $cabangId);
        });
    }
    
    $totalReturn = $totalReturnQuery->sum('jumlah_return') ?? 0;

    // ✅ HPP dari barang yang di-return dalam periode ini
    $hppReturnQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                                ->where('detail_penjualan.is_return', true)
                                ->whereDate('detail_penjualan.return_date', '>=', $tanggalDari)
                                ->whereDate('detail_penjualan.return_date', '<=', $tanggalSampai);
    
    if ($cabangId !== null) {
        $hppReturnQuery->where('penjualan.cabang_id', $cabangId);
    }
    
    $hppReturn = $hppReturnQuery->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp_return'))
                                ->value('total_hpp_return') ?? 0;

    // ✅ Hitung pendapatan bersih (pendapatan - return)
    $pendapatanBersih = $totalPendapatan - $totalReturn;
    
    // ✅ Hitung HPP bersih (hpp dari barang yang tidak di-return)
    $hppBersih = $hpp;

    // ✅ Laba kotor = Pendapatan Bersih - HPP Bersih
    $labaKotor = $pendapatanBersih - $hppBersih;
    $marginLaba = $pendapatanBersih > 0 ? ($labaKotor / $pendapatanBersih) * 100 : 0;

    // ✅ Detail per item
    $detailPerItemQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                    ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                                    ->whereDate('penjualan.tanggal_penjualan', '>=', $tanggalDari)
                                    ->whereDate('penjualan.tanggal_penjualan', '<=', $tanggalSampai);
    
    if ($cabangId !== null) {
        $detailPerItemQuery->where('penjualan.cabang_id', $cabangId);
    }
    
    $detailPerItem = $detailPerItemQuery->select(
                                        'detail_penjualan.barang_id',
                                        'barang.nama_barang',
                                        DB::raw('SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.jumlah ELSE 0 END) as total_qty'),
                                        DB::raw('SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.subtotal ELSE 0 END) as total_penjualan'),
                                        DB::raw('SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.jumlah * barang.harga_beli ELSE 0 END) as total_hpp'),
                                        DB::raw('SUM(CASE WHEN detail_penjualan.is_return = true THEN detail_penjualan.jumlah_return ELSE 0 END) as total_return'),
                                        DB::raw('(SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.subtotal ELSE 0 END) - SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.jumlah * barang.harga_beli ELSE 0 END) - SUM(CASE WHEN detail_penjualan.is_return = true THEN detail_penjualan.jumlah_return ELSE 0 END)) as laba')
                                    )
                                    ->groupBy('detail_penjualan.barang_id', 'barang.nama_barang')
                                    ->orderBy('laba', 'desc')
                                    ->get();

    return view('pages.laporan.laba-rugi', compact(
        'tanggalDari', 'tanggalSampai', 'totalPendapatan', 'totalPembelian', 'hpp', 
        'labaKotor', 'marginLaba', 'detailPerItem', 'totalReturn', 'pendapatanBersih', 'hppBersih', 'hppReturn'
    ));
}

    // =========================================================================
    // LAPORAN STOK
    // =========================================================================

    public function stok(Request $request)
    {
        $query = Barang::query();
        $query = $this->applyCabangFilter($query);

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

        $cabangId = $this->getActiveCabangId();
        $kategoriListQuery = Barang::select('kategori')
                              ->distinct()
                              ->whereNotNull('kategori');
        
        if ($cabangId !== null) {
            $kategoriListQuery->where('cabang_id', $cabangId);
        }
        
        $kategoriList = $kategoriListQuery->pluck('kategori');

        return view('pages.laporan.stok', compact(
            'barang', 'totalNilaiStok', 'totalNilaiJual', 'potensialLaba', 'kategoriList'
        ));
    }

    public function kartuStok(Request $request)
    {
        $daftarBarangQuery = Barang::orderBy('nama_barang');
        $daftarBarang = $this->applyCabangFilter($daftarBarangQuery)->get();
        
        $barangId = $request->barang_id;
        $tanggalDari = $request->tanggal_dari ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalSampai = $request->tanggal_sampai ?? now()->format('Y-m-d');
        
        $barang = null;
        $kartuStok = collect();
        $stokAwal = 0;
        $stokAkhir = 0;
        
        if ($barangId) {
            $barang = Barang::with('satuanKonversi')->findOrFail($barangId);
            
            $cabangId = $this->getActiveCabangId();
            if ($cabangId !== null && $barang->cabang_id != $cabangId) {
                abort(403, 'Barang ini bukan milik cabang yang sedang diakses.');
            }
            
            $stokAwal = $this->hitungStokAwal($barangId, $tanggalDari);
            
            $pembelianQuery = DetailPembelian::where('barang_id', $barangId)
                ->whereHas('pembelian', function($q) use ($tanggalDari, $tanggalSampai, $cabangId) {
                    $q->where('status', 'approved')
                      ->whereDate('tanggal_pembelian', '>=', $tanggalDari) 
                      ->whereDate('tanggal_pembelian', '<=', $tanggalSampai); 
                    
                    if ($cabangId !== null) {
                        $q->where(function($subQ) use ($cabangId) {
                            $subQ->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
                        });
                    }
                });
            
            $pembelian = $pembelianQuery->with(['pembelian.supplier', 'pembelian.user'])
                ->get()
                ->map(function($detail) use ($barang) {
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    
                    return [
                        'tanggal' => $detail->pembelian->tanggal_pembelian,
                        'nomor' => $detail->pembelian->nomor_pembelian,
                        'keterangan' => 'Pembelian dari ' . $detail->pembelian->supplier->nama_supplier,
                        'masuk' => $detail->jumlah . ' ' . $detail->satuan,
                        'keluar' => '-',
                        'sisa' => 0,
                        'paraf' => $detail->pembelian->user->name ?? '-',
                        'ed' => $detail->tanggal_kadaluarsa ? \Carbon\Carbon::parse($detail->tanggal_kadaluarsa)->format('m/y') : '-',
                        'sort_date' => $detail->pembelian->tanggal_pembelian,
                        'qty_dasar' => $qtyDasar,
                        'type' => 'masuk'
                    ];
                });
            
            $penjualanQuery = DetailPenjualan::where('barang_id', $barangId)
                ->whereHas('penjualan', function($q) use ($tanggalDari, $tanggalSampai, $cabangId) {
                    $q->whereDate('tanggal_penjualan', '>=', $tanggalDari)
                      ->whereDate('tanggal_penjualan', '<=', $tanggalSampai);
                    
                    if ($cabangId !== null) {
                        $q->where(function($subQ) use ($cabangId) {
                            $subQ->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
                        });
                    }
                });
            
            $penjualan = $penjualanQuery->with(['penjualan.user'])
                ->get()
                ->map(function($detail) use ($barang) {
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    
                    return [
                        'tanggal' => $detail->penjualan->tanggal_penjualan,
                        'nomor' => $detail->penjualan->nomor_nota,
                        'keterangan' => 'Penjualan' . ($detail->penjualan->nama_pelanggan ? ' - ' . $detail->penjualan->nama_pelanggan : ''),
                        'masuk' => '-',
                        'keluar' => $detail->jumlah . ' ' . $detail->satuan,
                        'sisa' => 0,
                        'paraf' => $detail->penjualan->user->name ?? '-',
                        'ed' => '-',
                        'sort_date' => $detail->penjualan->tanggal_penjualan,
                        'qty_dasar' => $qtyDasar,
                        'type' => 'keluar'
                    ];
                });
            
            $kartuStok = $pembelian->concat($penjualan)
                ->sortBy('sort_date')
                ->values();
            
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

    private function hitungStokAwal($barangId, $tanggalDari)
    {
        $barang = Barang::findOrFail($barangId);
        $cabangId = $this->getActiveCabangId();
        
        $totalMasukQuery = DetailPembelian::where('barang_id', $barangId)
            ->whereHas('pembelian', function($q) use ($tanggalDari, $cabangId) {
                $q->where('status', 'approved')
                  ->where('tanggal_pembelian', '<', $tanggalDari);
                
                if ($cabangId !== null) {
                    $q->where(function($subQ) use ($cabangId) {
                        $subQ->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
                    });
                }
            });
        
        $totalMasuk = $totalMasukQuery->get()
            ->sum(function($detail) use ($barang) {
                return $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
            });
        
        $totalKeluarQuery = DetailPenjualan::where('barang_id', $barangId)
            ->whereHas('penjualan', function($q) use ($tanggalDari, $cabangId) {
                $q->where('tanggal_penjualan', '<', $tanggalDari);
                
                if ($cabangId !== null) {
                    $q->where(function($subQ) use ($cabangId) {
                        $subQ->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
                    });
                }
            });
        
        $totalKeluar = $totalKeluarQuery->get()
            ->sum(function($detail) use ($barang) {
                return $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
            });
        
        return $totalMasuk - $totalKeluar;
    }

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
    // EXPORT
    // =========================================================================

    public function exportExcel(Request $request)
    {
        $jenis = $request->get('jenis', 'penjualan'); // ✅ Ubah dari 'type' ke 'jenis' sesuai dengan view
        $cabangId = $this->getActiveCabangId();
        
        switch ($jenis) {
            case 'penjualan':
                $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
                
                $detailPenjualanQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                                 ->whereDate('penjualan.tanggal_penjualan', '>=', $data['tanggalDari'])
                                                 ->whereDate('penjualan.tanggal_penjualan', '<=', $data['tanggalSampai']);
                
                if ($cabangId !== null) {
                    $detailPenjualanQuery->where('penjualan.cabang_id', $cabangId);
                }
                
                $detailPenjualan = $detailPenjualanQuery->with(['penjualan.user', 'barang'])
                                                 ->select('detail_penjualan.*')
                                                 ->orderBy('penjualan.tanggal_penjualan', 'asc')
                                                 ->get();
                
                $fileName = "Laporan_Penjualan_{$data['tanggalDari']}_to_{$data['tanggalSampai']}.xlsx";
                return Excel::download(new PenjualanExport($detailPenjualan), $fileName);

            case 'pembelian':
                // ✅ IMPLEMENTASI EXPORT PEMBELIAN
                $data = $this->applyDateFilter($request, Pembelian::class, 'tanggal_pembelian', 'approved');
                
                $detailPembelianQuery = DetailPembelian::join('pembelian', 'detail_pembelian.pembelian_id', '=', 'pembelian.id')
                                                 ->whereDate('pembelian.tanggal_pembelian', '>=', $data['tanggalDari'])
                                                 ->whereDate('pembelian.tanggal_pembelian', '<=', $data['tanggalSampai'])
                                                 ->where('pembelian.status', 'approved');
                
                if ($cabangId !== null) {
                    $detailPembelianQuery->where('pembelian.cabang_id', $cabangId);
                }
                
                $detailPembelian = $detailPembelianQuery->with(['pembelian.supplier', 'pembelian.user', 'pembelian.cabang', 'barang'])
                                                 ->select('detail_pembelian.*')
                                                 ->orderBy('pembelian.tanggal_pembelian', 'asc')
                                                 ->get();
                
                $fileName = "Laporan_Pembelian_{$data['tanggalDari']}_to_{$data['tanggalSampai']}.xlsx";
                return Excel::download(new PembelianExport($detailPembelian), $fileName);

            default:
                return back()->with('error', 'Jenis laporan tidak valid.');
        }
    }

    public function exportPdf(Request $request)
    {
        $reportType = $request->get('type', 'penjualan'); 
        
        switch ($reportType) {
            case 'penjualan':
                $data = $this->applyDateFilter($request, Penjualan::class, 'tanggal_penjualan');
                $penjualan = $data['query']->with('user')->get();
                $tanggalDari = $data['tanggalDari'];
                $tanggalSampai = $data['tanggalSampai'];
                
                $viewData = [
                    'penjualan' => $penjualan,
                    'tanggalDari' => $tanggalDari,
                    'tanggalSampai' => $tanggalSampai,
                    'title' => 'Laporan Penjualan',
                ];
                
                $pdf = PDF::loadView('pages.laporan.penjualan-pdf', $viewData);
                $fileName = "Laporan_Penjualan_{$tanggalDari}_to_{$tanggalSampai}.pdf";
                
                return $pdf->download($fileName);

            default:
                return back()->with('error', 'Jenis laporan tidak valid untuk PDF.');
        }
    }
}