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
use illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Exports\PenjualanExport;
use App\Exports\PembelianExport;
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

        $cabangId = $this->getActiveCabangId();
        
        // 🟢 (PERUBAHAN) LOGIKA HPP dan LABA KOTOR DARI LABA RUGI:
        
        // 1. Hitung HPP barang yang terjual (bukan return)
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
        
        $totalHPP = $hppQuery->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'))
                             ->value('total_hpp') ?? 0;
                             
        // 2. Hitung Nilai Return Penjualan (nilai yang dikembalikan ke pelanggan)
        $totalReturnQuery = DetailPenjualan::where('is_return', true)
                                         ->whereDate('return_date', '>=', $tanggalDari)
                                         ->whereDate('return_date', '<=', $tanggalSampai);
        
        if ($cabangId !== null) {
            $totalReturnQuery->whereHas('penjualan', function($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId);
            });
        }
        
        $totalNilaiReturn = $totalReturnQuery->sum('subtotal') ?? 0;

        // 3. Hitung Pendapatan Bersih (Total Penjualan - Total Nilai Return)
        $pendapatanBersih = $totalPenjualan - $totalNilaiReturn;
        
        // 4. Hitung Laba Kotor (Pendapatan Bersih - HPP) dengan minimum 0
        $labaKotor = max(0, $pendapatanBersih - $totalHPP);
        // ----------------------------------------------------------------------

        // 🟢 (MODIFIKASI) Hitung penjualan per hari dengan laba kotor minimum 0
        $perHari = (clone $query)
                        ->select(
                            DB::raw('DATE(tanggal_penjualan) as tanggal'),
                            DB::raw('COUNT(*) as jumlah_transaksi'),
                            DB::raw('SUM(grand_total) as total')
                        )
                        ->groupBy('tanggal')
                        ->orderBy('tanggal', 'asc')
                        ->get()
                        ->map(function($item) use ($cabangId) {
                            // Hitung HPP untuk tanggal ini
                            $hppQuery = DetailPenjualan::join('penjualan', 'detail_penjualan.penjualan_id', '=', 'penjualan.id')
                                        ->join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
                                        ->whereDate('penjualan.tanggal_penjualan', $item->tanggal)
                                        ->where(function($q) {
                                            $q->where('detail_penjualan.is_return', false)
                                              ->orWhereNull('detail_penjualan.is_return');
                                        });
                            
                            if ($cabangId !== null) {
                                $hppQuery->where('penjualan.cabang_id', $cabangId);
                            }
                            
                            $hpp = $hppQuery->select(DB::raw('SUM(detail_penjualan.jumlah * barang.harga_beli) as total_hpp'))
                                           ->value('total_hpp') ?? 0;
                            
                            // Hitung nilai return untuk tanggal ini
                            $returnQuery = DetailPenjualan::whereDate('return_date', $item->tanggal)
                                                    ->where('is_return', true);
                            
                            if ($cabangId !== null) {
                                $returnQuery->whereHas('penjualan', function($q) use ($cabangId) {
                                    $q->where('cabang_id', $cabangId);
                                });
                            }
                            
                            $nilaiReturn = $returnQuery->sum('subtotal') ?? 0;
                            
                            // 🟢 PERUBAHAN: Laba kotor minimum 0 (tidak boleh minus)
                            $item->laba_kotor = max(0, ($item->total - $nilaiReturn) - $hpp);
                            
                            return $item;
                        });

        // Ambil barang terlaris seperti sebelumnya
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
            'perHari', 'barangTerlaris', 'perMetode', 'labaKotor'
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

        $cabangId = $this->getActiveCabangId();
        
        // Pembelian per hari
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
        $barangTerbeliQuery = DetailPembelian::join('pembelian', 'detail_pembelian.pembelian_id', '=', 'pembelian.id')
                                             ->whereBetween('pembelian.tanggal_pembelian', [$tanggalDari, $tanggalSampai])
                                             ->where('pembelian.status', 'approved');
        
        if ($cabangId !== null) {
            $barangTerbeliQuery->where('pembelian.cabang_id', $cabangId);
        }
        
        $barangTerbeli = $barangTerbeliQuery->select(
                                             'detail_pembelian.barang_id',
                                             DB::raw('SUM(detail_pembelian.jumlah) as total_qty'),
                                             DB::raw('SUM(detail_pembelian.subtotal) as total_nilai')
                                           )
                                             ->groupBy('detail_pembelian.barang_id')
                                             ->orderBy('total_nilai', 'desc')
                                             ->limit(10)
                                             ->with('barang')
                                             ->get();

        // Pembelian per supplier
        $perSupplierQuery = Pembelian::join('suppliers', 'pembelian.supplier_id', '=', 'suppliers.id')
                        ->whereDate('pembelian.tanggal_pembelian', '>=', $tanggalDari)
                        ->whereDate('pembelian.tanggal_pembelian', '<=', $tanggalSampai)
                        ->where('pembelian.status', 'approved');
        
        if ($cabangId !== null) {
            $perSupplierQuery->where('pembelian.cabang_id', $cabangId);
        }
        
        $perSupplier = $perSupplierQuery->select(
                            'suppliers.nama_supplier',
                            DB::raw('COUNT(pembelian.id) as jumlah'),
                            DB::raw('SUM(pembelian.grand_total) as total')
                        )
                        ->groupBy('suppliers.nama_supplier')
                        ->orderBy('total', 'desc')
                        ->get();

        return view('pages.laporan.pembelian', compact(
            'tanggalDari', 'tanggalSampai', 'totalPembelian', 'jumlahTransaksi', 
            'perHari', 'barangTerbeli', 'perSupplier'
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

        $totalReturnQuery = DetailPenjualan::where('is_return', true)
                                    ->whereDate('return_date', '>=', $tanggalDari)
                                    ->whereDate('return_date', '<=', $tanggalSampai);
        
        if ($cabangId !== null) {
            $totalReturnQuery->whereHas('penjualan', function($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId);
            });
        }
        
        $totalReturn = $totalReturnQuery->sum('jumlah_return') ?? 0;

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

        $pendapatanBersih = $totalPendapatan - $totalReturn;
        $hppBersih = $hpp;
        // 🟢 PERUBAHAN: Laba kotor minimum 0
        $labaKotor = max(0, $pendapatanBersih - $hppBersih);
        $marginLaba = $pendapatanBersih > 0 ? ($labaKotor / $pendapatanBersih) * 100 : 0;

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
                                            // 🟢 PERUBAHAN: Gunakan GREATEST untuk laba minimum 0
                                            DB::raw('GREATEST(0, (SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.subtotal ELSE 0 END) - SUM(CASE WHEN (detail_penjualan.is_return = false OR detail_penjualan.is_return IS NULL) THEN detail_penjualan.jumlah * barang.harga_beli ELSE 0 END) - SUM(CASE WHEN detail_penjualan.is_return = true THEN detail_penjualan.jumlah_return ELSE 0 END))) as laba')
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
    // Ambil daftar barang sesuai cabang
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
        
        // Validasi akses cabang
        $cabangId = $this->getActiveCabangId();
        if ($cabangId !== null && $barang->cabang_id != $cabangId) {
            abort(403, 'Barang ini bukan milik cabang yang sedang diakses.');
        }
        
        // ✅ HITUNG STOK AWAL (dari riwayat_stok sebelum periode)
        $stokAwal = $this->hitungStokAwalRiwayat($barangId, $tanggalDari);
        
        // ✅ TAMBAHKAN BARIS STOK AWAL
        $stokAwalEntry = [
            'tanggal' => Carbon::parse($tanggalDari)->subDay()->format('Y-m-d'),
            'nomor' => '-',
            'keterangan' => 'Saldo Awal Periode',
            'masuk' => '-',
            'keluar' => '-',
            'sisa' => $stokAwal,
            'paraf' => '-',
            'ed' => '-',
            'sort_date' => Carbon::parse($tanggalDari)->subDay(),
            'qty_dasar' => $stokAwal,
            'type' => 'saldo_awal'
        ];
        
        // ✅ AMBIL RIWAYAT STOK (dari tabel riwayat_stok)
        $riwayatQuery = \App\Models\RiwayatStok::where('barang_id', $barangId)
            ->whereDate('tanggal', '>=', $tanggalDari)
            ->whereDate('tanggal', '<=', $tanggalSampai);
        
        if ($cabangId !== null) {
            $riwayatQuery->where('cabang_id', $cabangId);
        }
        
        $riwayat = $riwayatQuery->with('user')
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'tanggal' => $item->tanggal->format('Y-m-d'),
                    'nomor' => $item->nomor_referensi ?? '-',
                    'keterangan' => $item->keterangan ?? ucwords(str_replace('_', ' ', $item->tipe_transaksi)),
                    'masuk' => $item->jumlah_perubahan > 0 ? abs($item->jumlah_perubahan) . ' ' . $item->satuan : '-',
                    'keluar' => $item->jumlah_perubahan < 0 ? abs($item->jumlah_perubahan) . ' ' . $item->satuan : '-',
                    'sisa' => 0, // Akan dihitung running balance
                    'paraf' => $item->user->name ?? '-',
                    'ed' => '-',
                    'sort_date' => $item->tanggal,
                    'qty_dasar' => $item->jumlah_perubahan,
                    'type' => $item->jumlah_perubahan > 0 ? 'masuk' : 'keluar'
                ];
            });
        
        // ✅ GABUNGKAN: Stok Awal + Riwayat
        $kartuStok = collect([$stokAwalEntry])
            ->concat($riwayat)
            ->values();
        
        // ✅ HITUNG RUNNING BALANCE
        $sisa = 0;
        $kartuStok = $kartuStok->map(function($item) use (&$sisa) {
            if ($item['type'] === 'saldo_awal') {
                $sisa = $item['qty_dasar'];
            } else {
                $sisa += $item['qty_dasar'];
            }
            
            $item['sisa'] = $sisa;
            return $item;
        });
        
        $stokAkhir = $sisa;
        
        Log::info('Kartu Stok Generated', [
            'barang_id' => $barangId,
            'periode' => "$tanggalDari - $tanggalSampai",
            'stok_awal' => $stokAwal,
            'stok_akhir' => $stokAkhir,
            'total_transaksi' => $riwayat->count()
        ]);
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

/**
 * ✅ Hitung stok awal dari riwayat_stok
 */
    private function hitungStokAwalRiwayat($barangId, $tanggalDari)
{
    $cabangId = $this->getActiveCabangId();
    
    // Ambil riwayat terakhir SEBELUM tanggal_dari
    $riwayatTerakhir = \App\Models\RiwayatStok::where('barang_id', $barangId)
        ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
        ->where('tanggal', '<', $tanggalDari)
        ->orderBy('tanggal', 'desc')
        ->orderBy('id', 'desc')
        ->first();
    
    if ($riwayatTerakhir) {
        Log::info('Stok Awal dari Riwayat', [
            'barang_id' => $barangId,
            'tanggal_dari' => $tanggalDari,
            'riwayat_id' => $riwayatTerakhir->id,
            'stok_sesudah' => $riwayatTerakhir->stok_sesudah
        ]);
        
        return $riwayatTerakhir->stok_sesudah;
    }
    
    // ✅ PERBAIKAN: Jika tidak ada riwayat sebelum periode,
    // gunakan stok real-time dikurangi transaksi dalam periode
    Log::warning('Tidak ada riwayat sebelum periode, hitung dari stok sekarang', [
        'barang_id' => $barangId,
        'tanggal_dari' => $tanggalDari
    ]);
    
    $barang = \App\Models\Barang::find($barangId);
    if (!$barang) {
        return 0;
    }
    
    // Hitung total perubahan DALAM periode
    $totalPerubahanDalamPeriode = \App\Models\RiwayatStok::where('barang_id', $barangId)
        ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
        ->whereDate('tanggal', '>=', $tanggalDari)
        ->sum('jumlah_perubahan');
    
    // Stok Awal = Stok Sekarang - Total Perubahan Dalam Periode
    $stokAwal = $barang->stok - $totalPerubahanDalamPeriode;
    
    Log::info('Stok Awal Dihitung dari Stok Real-time', [
        'stok_sekarang' => $barang->stok,
        'total_perubahan_periode' => $totalPerubahanDalamPeriode,
        'stok_awal' => $stokAwal
    ]);
    
    return $stokAwal;
}

    // =========================================================================
    // EXPORT
    // =========================================================================

    public function exportExcel(Request $request)
    {
        $jenis = $request->get('jenis', 'penjualan');
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