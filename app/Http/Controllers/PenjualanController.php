<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Barang;
use App\Models\Shift;
use App\Models\SatuanKonversi;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini di-import jika fungsi export digunakan

class PenjualanController extends Controller
{
    use CabangFilterTrait; // Menggunakan trait untuk memfilter berdasarkan cabang aktif pengguna

    /**
     * Halaman Kasir POS.
     * Memastikan shift sudah dibuka dan memuat barang berdasarkan cabang aktif.
     */
    public function index()
    {
        // 1. Cek Status Shift
        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')->with('error', 'Anda harus membuka shift terlebih dahulu!');
        }
        
        // 2. Ambil Cabang ID untuk Filter
        $cabangId = $this->getActiveCabangId();
        
        // Cek izin/penugasan
        if (!Auth::user()->isSuperAdmin() && !$cabangId) {
             return redirect()->route('dashboard')->with('error', 'Akun Anda belum ditugaskan ke cabang. Hubungi Super Admin.');
        }

        // 3. Ambil barang dengan filter cabang, stok > 0, dan aktif = 1
        $query = Barang::with('satuanKonversi')
                       ->where('stok', '>', 0)
                       ->where('aktif', 1)
                       ->orderBy('nama_barang', 'asc');
        
        // Terapkan filter cabang dari CabangFilterTrait
        $barang = $this->applyCabangFilter($query)->get();
        
        return view('pages.penjualan.kasir', compact('shift', 'barang'));
    }

    /**
     * Cari barang untuk autocomplete/barcode scanner.
     * Filter berdasarkan keyword (nama, kode, barcode) dan cabang aktif.
     */
    public function cariBarang(Request $request)
    {
        $keyword = $request->get('q');
        
        $query = Barang::with('satuanKonversi')
                             ->where('stok', '>', 0)
                             ->where('aktif', 1)
                             ->where(function($q) use ($keyword) {
                                 $q->where('nama_barang', 'LIKE', "%{$keyword}%")
                                   ->orWhere('kode_barang', 'LIKE', "%{$keyword}%")
                                   ->orWhere('barcode', 'LIKE', "%{$keyword}%");
                             });

        // Terapkan filter cabang
        $barang = $this->applyCabangFilter($query)
                       ->limit(10)
                       ->get();

        return response()->json($barang);
    }

    /**
     * Ambil detail barang untuk keranjang (termasuk satuan konversi).
     * Memastikan barang tersedia di cabang aktif.
     */
    public function getBarang($id)
    {
        $barang = Barang::with('satuanKonversi')->findOrFail($id);
        
        // Cek akses cabang
        $cabangId = $this->getActiveCabangId();
        
        if (!Auth::user()->isSuperAdmin() && $barang->cabang_id != $cabangId) {
             return response()->json(['error' => 'Barang tidak tersedia di cabang ini.'], 403);
        }

        $satuanKonversi = $barang->satuanKonversi->map(function($konv) {
            return [
                'nama_satuan' => $konv->nama_satuan,
                'jumlah_konversi' => $konv->jumlah_konversi,
                'harga_jual' => $konv->harga_jual,
                'is_default' => $konv->is_default
            ];
        });

        return response()->json([
            'id' => $barang->id,
            'kode_barang' => $barang->kode_barang,  
            'nama_barang' => $barang->nama_barang,
            'harga_jual' => $barang->harga_jual,
            'stok' => $barang->stok,
            'satuan_dasar' => $barang->satuan_terkecil,
            'satuan_konversi' => $satuanKonversi,
        ]);
    }

    /**
     * Simpan transaksi penjualan.
     * Melakukan validasi, cek shift, simpan ke Penjualan dan DetailPenjualan, serta kurangi stok.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0', // Grand Total (setelah diskon)
            'uang_dibayar' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|in:cash,debit,credit,qris,transfer',
            'nomor_referensi' => 'nullable|string|max:100',
        ]);

        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return response()->json(['error' => 'Shift belum dibuka!'], 400);
        }
        
        // Ambil Cabang ID untuk Disimpan
        $cabangId = $this->getActiveCabangId();
        
        if (!Auth::user()->isSuperAdmin() && !$cabangId) {
             return response()->json(['error' => 'User belum ditugaskan ke cabang'], 403);
        }

        DB::beginTransaction();
        try {
            $nomorNota = Penjualan::generateNomorNota(); 

            $diskon = $request->diskon ?? 0;
            $grandTotal = $request->total_bayar;
            // Total penjualan sebelum diskon
            $totalPenjualan = $grandTotal + $diskon; 

            $kembalian = $request->uang_dibayar - $grandTotal;

            // Simpan header penjualan
            $penjualan = Penjualan::create([
                'nomor_nota' => $nomorNota,
                'user_id' => Auth::id(),
                'shift_id' => $shift->id,
                'cabang_id' => $cabangId, // SIMPAN CABANG ID
                'tanggal_penjualan' => now(),
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_penjualan' => $totalPenjualan, 
                'diskon' => $diskon,
                'pajak' => 0,
                'grand_total' => $grandTotal,
                'jumlah_bayar' => $request->uang_dibayar,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $request->metode_pembayaran ?? 'cash',
                'nomor_referensi' => $request->nomor_referensi,
                'keterangan' => $request->keterangan,
            ]);

            // Simpan Detail & Kurangi Stok
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                // CEK AKSES BARANG & CABANG
                 if (!Auth::user()->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Akses ditolak: Barang {$barang->nama_barang} bukan milik cabang ini.");
                }

                // Hitung qty dalam satuan terkecil (satuan dasar)
                $qtyDasar = $item['qty'];
                
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $item['qty'] * $konversi->jumlah_konversi;
                    }
                }

                // Cek stok
                if ($barang->stok < $qtyDasar) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak cukup! Tersedia: {$barang->stok} {$barang->satuan_terkecil}");
                }

                // Simpan detail
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_jual' => $item['harga'],
                    'subtotal' => $item['qty'] * $item['harga']
                ]);

                // Kurangi stok (dalam satuan terkecil)
                $barang->decrement('stok', $qtyDasar);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'invoice' => $nomorNota,
                'penjualan_id' => $penjualan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error($e->getMessage()); // Baik untuk di-log di aplikasi nyata
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Riwayat Penjualan.
     * Menampilkan riwayat transaksi dengan filter cabang, tanggal, dan nomor nota.
     */
    public function riwayat(Request $request)
    {
        $query = Penjualan::with(['user', 'detailPenjualan.barang', 'shift', 'cabang']); 

        // Terapkan filter cabang
        $query = $this->applyCabangFilter($query);
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        // Filter nomor nota
        if ($request->filled('nomor_nota')) {
            $query->where('nomor_nota', 'LIKE', "%{$request->nomor_nota}%");
        }

        $penjualan = $query->orderBy('tanggal_penjualan', 'desc')->paginate(20);
        
        $cabangName = $this->getActiveCabangName(); 

        return view('pages.penjualan.riwayat', compact('penjualan', 'cabangName'));
    }

    /**
     * Tampilkan detail penjualan.
     * Memastikan penjualan milik cabang yang sedang diakses.
     */
    public function show($id)
    {
        $penjualan = Penjualan::with(['user', 'shift', 'detailPenjualan.barang', 'cabang'])->findOrFail($id);
        
        // Cek Akses Cabang untuk Detail
        if (!Auth::user()->isSuperAdmin() && $penjualan->cabang_id != $this->getActiveCabangId()) {
            abort(403, 'Akses ditolak. Penjualan ini bukan milik cabang Anda.');
        }

        return view('pages.penjualan.detail', compact('penjualan'));
    }

    /**
     * Cetak struk penjualan.
     * Memastikan penjualan milik cabang yang sedang diakses.
     */
    public function printStruk($id)
    {
        $penjualan = Penjualan::with(['detailPenjualan.barang', 'user', 'cabang'])->findOrFail($id);
        
        // Cek Akses Cabang untuk Print
        if (!Auth::user()->isSuperAdmin() && $penjualan->cabang_id != $this->getActiveCabangId()) {
            abort(403, 'Akses ditolak. Penjualan ini bukan milik cabang Anda.');
        }

        return view('pages.penjualan.struk', compact('penjualan'));
    }

    /**
     * Cari nota berdasarkan nomor nota untuk fitur return.
     * Filter berdasarkan cabang aktif.
     */
    public function cariNota($nomorNota)
    {
        $query = Penjualan::with(['detailPenjualan.barang'])
                             ->where('nomor_nota', $nomorNota);
        
        // Terapkan filter cabang
        $penjualan = $this->applyCabangFilter($query)->first();
        
        if (!$penjualan) {
            return response()->json(['success' => false, 'message' => 'Nota tidak ditemukan atau tidak tersedia di cabang ini'], 404);
        }
        
        $items = $penjualan->detailPenjualan->map(function($detail) {
            return [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->barang->nama_barang,
                'jumlah' => $detail->jumlah,
                'satuan' => $detail->satuan,
                'harga_jual' => $detail->harga_jual,
                'subtotal' => $detail->subtotal
            ];
        });
        
        return response()->json([
            'success' => true,
            'penjualan_id' => $penjualan->id,
            'items' => $items
        ]);
    }

    /**
     * Proses pengembalian (return) barang.
     * Mengembalikan stok dan menandai detail penjualan sebagai return.
     */
    public function prosesReturn(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'required|exists:detail_penjualan,id'
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($request->items as $detailId) {
                $detail = DetailPenjualan::with('barang')->findOrFail($detailId);
                
                $barang = Barang::findOrFail($detail->barang_id);
                $cabangId = $this->getActiveCabangId();

                // Pastikan barang yang di-return adalah milik cabang yang sedang diakses
                if (!Auth::user()->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                     throw new \Exception("Return ditolak: Barang {$barang->nama_barang} bukan milik cabang yang sedang diakses.");
                }

                // Hitung qty dalam satuan dasar untuk pengembalian stok
                $qtyDasar = $detail->jumlah;
                
                if ($detail->satuan !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $detail->satuan)
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $detail->jumlah * $konversi->jumlah_konversi;
                    }
                }
                
                // Tambah stok kembali
                $barang->increment('stok', $qtyDasar);
                
                // Tandai detail sebagai return
                $detail->update([
                    'is_return' => true,
                    'return_date' => now()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Return berhasil diproses'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

   /**
 * Laporan Return Barang (TERPISAH PER CABANG)
 */
public function laporanReturn(Request $request)
{
    $cabangId = $this->getActiveCabangId();
    
    // ✅ VALIDASI: User non-Super Admin HARUS punya cabang_id
    if (!Auth::user()->isSuperAdmin() && !$cabangId) {
        abort(403, 'Akses ditolak. Akun Anda belum ditugaskan ke cabang.');
    }
    
    $query = DetailPenjualan::with(['penjualan.user', 'penjualan.cabang', 'barang'])
                            ->where('is_return', true);
    
    // ✅ FILTER CABANG WAJIB (bahkan untuk Super Admin jika ada session filter)
    if ($cabangId !== null) {
        $query->whereHas('penjualan', function($q) use ($cabangId) {
            $q->where('cabang_id', $cabangId);
        });
    }
    
    // Filter Tanggal
    $tanggalDari = $request->get('tanggal_dari', now()->subMonth()->format('Y-m-d'));
    $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));
    
    if ($request->filled('tanggal_dari')) {
        $query->whereDate('return_date', '>=', $tanggalDari);
    }
    
    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('return_date', '<=', $tanggalSampai);
    }
    
    $returns = $query->orderBy('return_date', 'desc')->paginate(20);
    
    // Summary
    $totalReturnQuery = DetailPenjualan::where('is_return', true)
        ->whereDate('return_date', '>=', $tanggalDari)
        ->whereDate('return_date', '<=', $tanggalSampai);
    
    if ($cabangId !== null) {
        $totalReturnQuery->whereHas('penjualan', function($q) use ($cabangId) {
            $q->where('cabang_id', $cabangId);
        });
    }
    
    $totalReturn = $totalReturnQuery->sum('subtotal');
    $totalItem = $totalReturnQuery->sum('jumlah');
    
    $cabangName = $this->getActiveCabangName();
    
    return view('pages.penjualan.laporan-return', compact(
        'returns', 
        'totalReturn', 
        'totalItem', 
        'cabangName',
        'tanggalDari',
        'tanggalSampai'
    ));
}

/**
 * Laporan Invoice Per Transaksi (TERPISAH PER CABANG)
 */
public function laporanInvoice(Request $request)
{
    $cabangId = $this->getActiveCabangId();
    
    // ✅ VALIDASI: User non-Super Admin HARUS punya cabang_id
    if (!Auth::user()->isSuperAdmin() && !$cabangId) {
        abort(403, 'Akses ditolak. Akun Anda belum ditugaskan ke cabang.');
    }
    
    $query = Penjualan::with(['user', 'shift', 'cabang', 'detailPenjualan.barang']);
    
    // ✅ FILTER CABANG WAJIB
    if ($cabangId !== null) {
        $query->where('cabang_id', $cabangId);
    }
    
    // Filter Tanggal (default: bulan ini)
    $tanggalDari = $request->get('tanggal_dari', now()->startOfMonth()->format('Y-m-d'));
    $tanggalSampai = $request->get('tanggal_sampai', now()->format('Y-m-d'));
    
    if ($request->filled('tanggal_dari')) {
        $query->whereDate('tanggal_penjualan', '>=', $tanggalDari);
    }
    
    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('tanggal_penjualan', '<=', $tanggalSampai);
    }
    
    // Filter Metode Pembayaran
    if ($request->filled('metode_pembayaran')) {
        $query->where('metode_pembayaran', $request->metode_pembayaran);
    }
    
    $penjualan = $query->orderBy('tanggal_penjualan', 'desc')->paginate(20);
    
    // Summary (Total data sebelum pagination)
    $summaryQuery = Penjualan::query();
    
    if ($cabangId !== null) {
        $summaryQuery->where('cabang_id', $cabangId);
    }
    
    $summaryQuery->whereDate('tanggal_penjualan', '>=', $tanggalDari)
                 ->whereDate('tanggal_penjualan', '<=', $tanggalSampai);
    
    if ($request->filled('metode_pembayaran')) {
        $summaryQuery->where('metode_pembayaran', $request->metode_pembayaran);
    }
    
    $totalTransaksi = $summaryQuery->count();
    $totalPendapatan = $summaryQuery->sum('grand_total');
    
    $cabangName = $this->getActiveCabangName();
    
    return view('pages.penjualan.laporan-invoice', compact(
        'penjualan', 
        'totalTransaksi', 
        'totalPendapatan',
        'cabangName',
        'tanggalDari',
        'tanggalSampai'
    ));
}

/**
 * Export Laporan Invoice ke Excel (TERPISAH PER CABANG)
 */
public function exportInvoiceExcel(Request $request)
{
    $cabangId = $this->getActiveCabangId();
    
    // ✅ VALIDASI: User non-Super Admin HARUS punya cabang_id
    if (!Auth::user()->isSuperAdmin() && !$cabangId) {
        abort(403, 'Akses ditolak. Akun Anda belum ditugaskan ke cabang.');
    }
    
    $query = Penjualan::with(['user', 'shift', 'cabang', 'detailPenjualan.barang']);
    
    // ✅ FILTER CABANG WAJIB
    if ($cabangId !== null) {
        $query->where('cabang_id', $cabangId);
    }
    
    // Filter Tanggal
    if ($request->filled('tanggal_dari')) {
        $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_dari);
    }
    
    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_sampai);
    }
    
    if ($request->filled('metode_pembayaran')) {
        $query->where('metode_pembayaran', $request->metode_pembayaran);
    }
    
    $penjualan = $query->orderBy('tanggal_penjualan', 'desc')->get();
    
    $cabangName = $this->getActiveCabangName();
    $fileName = 'Laporan_Invoice_' . str_replace(' ', '_', $cabangName) . '_' . date('Y-m-d') . '.xlsx';
    
    return Excel::download(new \App\Exports\InvoicePenjualanExport($penjualan, $cabangName), $fileName);
}
}