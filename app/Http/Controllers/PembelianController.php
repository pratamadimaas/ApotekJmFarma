<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\SatuanKonversi;
use App\Traits\CabangFilterTrait; 
use App\Traits\RecordsStokHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PembelianController extends Controller
{
    use CabangFilterTrait, RecordsStokHistory;

    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user', 'detailPembelian.barang', 'cabang']);
        $query = $this->applyCabangFilter($query);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pembelian', '>=', $request->tanggal_dari); 
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pembelian', '<=', $request->tanggal_sampai); 
        }

        if ($request->filled('no_faktur')) {
            $query->where('nomor_pembelian', 'LIKE', "%{$request->no_faktur}%"); 
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereNotIn('status', ['pending']);
        }

        $pembelian = $query->orderBy('tanggal_pembelian', 'desc')->paginate(20);
        $suppliers = Supplier::all();

        return view('pages.pembelian.index', compact('pembelian', 'suppliers'));
    }

    public function pending(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user', 'detailPembelian.barang', 'cabang'])
                         ->where('status', 'pending');

        $query = $this->applyCabangFilter($query);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pembelian', '>=', $request->tanggal_dari); 
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pembelian', '<=', $request->tanggal_sampai); 
        }

        if ($request->filled('no_faktur')) {
            $query->where('nomor_pembelian', 'LIKE', "%{$request->no_faktur}%"); 
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $pembelian = $query->orderBy('tanggal_pembelian', 'desc')->paginate(20);
        $suppliers = Supplier::all();

        return view('pages.pembelian.pending', compact('pembelian', 'suppliers'));
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::with('detailPembelian.barang')->findOrFail($id);

            $cabangId = $this->getActiveCabangId();
            if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
                abort(403, 'Pembelian ini bukan milik cabang Anda.');
            }

            if ($pembelian->status !== 'pending') {
                return back()->with('error', 'Pembelian ini sudah diproses!');
            }

            foreach ($pembelian->detailPembelian as $detail) {
                $barang = $detail->barang;
                
                if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Barang {$barang->nama_barang} bukan milik cabang ini.");
                }
                
                $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                $barang->increment('stok', $qtyDasar);

                $this->catatRiwayatStok(
                    barangId: $barang->id,
                    tipeTransaksi: 'pembelian',
                    jumlahPerubahan: $qtyDasar,
                    satuan: $barang->satuan_terkecil,
                    keterangan: "Pembelian Approved dari " . $pembelian->supplier->nama_supplier,
                    nomorReferensi: $pembelian->nomor_pembelian,
                    cabangId: $pembelian->cabang_id
                );

                $this->updateHargaJual($detail, $barang);
            }

            $pembelian->update(['status' => 'approved']);

            DB::commit();

            return redirect()->route('pembelian.show', $pembelian->id)
                           ->with('success', 'Pembelian berhasil diapprove dan stok telah ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve Pembelian Error', [
                'pembelian_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $barangQuery = Barang::aktif();
        $barang = $this->applyCabangFilter($barangQuery)->get();
        
        Log::info('Pembelian Create - Barang List', [
            'user_id' => Auth::id(),
            'cabang_id' => $this->getActiveCabangId(),
            'barang_count' => $barang->count()
        ]);

        return view('pages.pembelian.create', compact('suppliers', 'barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'no_faktur' => 'required|string|unique:pembelian,nomor_pembelian',
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,approved',
        ]);

        DB::beginTransaction();
        try {
            $cabangId = $this->getActiveCabangId();
            
            if (!$this->isSuperAdmin() && !$cabangId) {
                throw new \Exception('Akun Anda belum ditugaskan ke cabang. Hubungi Super Admin.');
            }

            Log::info('Store Pembelian - Start', [
                'user_id' => Auth::id(),
                'cabang_id' => $cabangId,
                'items_count' => count($request->items)
            ]);

            $status = $request->status ?? 'approved';

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_pembelian' => $request->total_harga,
                'diskon' => $request->diskon ?? 0,
                'pajak' => $request->ppn ?? 0,
                'grand_total' => $request->total_bayar,
                'user_id' => Auth::id(),
                'cabang_id' => $cabangId,
                'status' => $status,
                'keterangan' => $request->keterangan
            ]);

            Log::info('Pembelian Created', [
                'pembelian_id' => $pembelian->id,
                'cabang_id' => $pembelian->cabang_id
            ]);

            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Barang '{$barang->nama_barang}' bukan milik cabang Anda. Akses ditolak.");
                }

                Log::info('Processing Item', [
                    'barang_id' => $barang->id,
                    'barang_nama' => $barang->nama_barang,
                    'barang_cabang_id' => $barang->cabang_id,
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan']
                ]);

                // Update satuan konversi jika ada
                if (isset($item['satuan_konversi']) && is_array($item['satuan_konversi'])) {
                    $this->updateSatuanKonversi($barang->id, $item['satuan_konversi']);
                    Log::info('Satuan Konversi Updated for Barang', [
                        'barang_id' => $barang->id,
                        'konversi_count' => count($item['satuan_konversi'])
                    ]);
                }

                // Konversi qty ke satuan terkecil
                $qtyDasar = $this->convertToStokDasar($item['qty'], $item['satuan'], $barang);

                // Simpan detail pembelian
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal' => $item['qty'] * $item['harga_beli'],
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'] ?? null,
                ]);

                // Update stok dan harga HANYA jika APPROVED
                if ($status === 'approved') {
                    $barang->increment('stok', $qtyDasar);

                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'pembelian', 
                        jumlahPerubahan: $qtyDasar, 
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Pembelian dari " . Supplier::find($request->supplier_id)->nama_supplier,
                        nomorReferensi: $request->no_faktur,
                        cabangId: $cabangId
                    );
                                
                    // ✅ UPDATE HARGA BELI & HARGA JUAL (Satuan Dasar)
                    if ($item['satuan'] === $barang->satuan_terkecil) {
                        $barang->update([
                            'harga_beli' => $item['harga_beli']
                        ]);
                        
                        // Update harga jual satuan dasar jika ada di satuan_konversi
                        if (isset($item['satuan_konversi'])) {
                            foreach ($item['satuan_konversi'] as $namaSatuan => $dataKonversi) {
                                if ($namaSatuan === $barang->satuan_terkecil && isset($dataKonversi['harga_jual'])) {
                                    $barang->update(['harga_jual' => $dataKonversi['harga_jual']]);
                                }
                            }
                        }
                    } 
                    // ✅ UPDATE HARGA JUAL untuk Satuan Konversi (Bukan Satuan Dasar)
                    else {
                        // Update harga_jual di tabel satuan_konversi
                        if (isset($item['satuan_konversi'][$item['satuan']]['harga_jual'])) {
                            SatuanKonversi::where('barang_id', $barang->id)
                                ->where('nama_satuan', $item['satuan'])
                                ->update(['harga_jual' => $item['satuan_konversi'][$item['satuan']]['harga_jual']]);
                        }
                    }
                }
            }

            DB::commit();

            Log::info('Store Pembelian - Success', [
                'pembelian_id' => $pembelian->id,
                'status' => $status
            ]);

            $message = $status === 'pending' 
                ? 'Pembelian berhasil disimpan sebagai PENDING!' 
                : 'Pembelian berhasil disimpan dan APPROVED!';

            return redirect()->route('pembelian.show', $pembelian->id)
                             ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Pembelian - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'user', 'detailPembelian.barang', 'cabang'])->findOrFail($id);
        
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        return view('pages.pembelian.show', compact('pembelian'));
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with('detailPembelian')->findOrFail($id);
        
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        $suppliers = Supplier::all();
        $barangQuery = Barang::aktif();
        $barang = $this->applyCabangFilter($barangQuery)->get();

        return view('pages.pembelian.edit', compact('pembelian', 'suppliers', 'barang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'no_faktur' => 'required|string|unique:pembelian,nomor_pembelian,' . $id,
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,approved',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);
            
            $cabangId = $this->getActiveCabangId();
            if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
                abort(403, 'Pembelian ini bukan milik cabang Anda.');
            }
            
            $statusLama = $pembelian->status;
            $statusBaru = $request->status ?? 'approved';

            // Kembalikan stok jika status lama APPROVED
            if ($statusLama === 'approved') {
                foreach ($pembelian->detailPembelian as $detail) {
                    $barang = $detail->barang;
                    
                    if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                        throw new \Exception("Barang {$barang->nama_barang} bukan milik cabang ini.");
                    }
                    
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);

                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'penyesuaian',
                        jumlahPerubahan: -$qtyDasar,
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Edit Pembelian (kembalikan stok lama)",
                        nomorReferensi: $pembelian->nomor_pembelian,
                        cabangId: $cabangId
                    );
                }
            }

            // Hapus detail lama
            $pembelian->detailPembelian()->delete();

            // Update pembelian
            $pembelian->update([
                'nomor_pembelian' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_pembelian' => $request->total_harga,
                'diskon' => $request->diskon ?? 0,
                'pajak' => $request->ppn ?? 0,
                'grand_total' => $request->total_bayar ?? $request->total_harga,
                'status' => $statusBaru,
                'keterangan' => $request->keterangan
            ]);

            // Simpan detail baru
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);
                
                if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Barang '{$barang->nama_barang}' bukan milik cabang Anda.");
                }
                
                // Update satuan konversi
                if (isset($item['satuan_konversi']) && is_array($item['satuan_konversi'])) {
                    $this->updateSatuanKonversi($barang->id, $item['satuan_konversi']);
                    Log::info('Satuan Konversi Updated for Barang', [
                        'barang_id' => $barang->id,
                        'konversi_count' => count($item['satuan_konversi'])
                    ]);
                }
                
                $qtyDasar = $this->convertToStokDasar($item['qty'], $item['satuan'], $barang);

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal' => $item['qty'] * $item['harga_beli'],
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'] ?? null,
                ]);

                // Update stok jika status baru APPROVED
                if ($statusBaru === 'approved') {
                    $barang->increment('stok', $qtyDasar);

                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'pembelian',
                        jumlahPerubahan: $qtyDasar, 
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Update Pembelian dari " . Supplier::find($request->supplier_id)->nama_supplier,
                        nomorReferensi: $request->no_faktur,
                        cabangId: $cabangId
                    );
                    
                    // ✅ UPDATE HARGA BELI & HARGA JUAL (Satuan Dasar)
                    if ($item['satuan'] === $barang->satuan_terkecil) {
                        $barang->update([
                            'harga_beli' => $item['harga_beli']
                        ]);
                        
                        // Update harga jual satuan dasar jika ada di satuan_konversi
                        if (isset($item['satuan_konversi'])) {
                            foreach ($item['satuan_konversi'] as $namaSatuan => $dataKonversi) {
                                if ($namaSatuan === $barang->satuan_terkecil && isset($dataKonversi['harga_jual'])) {
                                    $barang->update(['harga_jual' => $dataKonversi['harga_jual']]);
                                }
                            }
                        }
                    }
                    // ✅ UPDATE HARGA JUAL untuk Satuan Konversi (Bukan Satuan Dasar)
                    else {
                        // Update harga_jual di tabel satuan_konversi
                        if (isset($item['satuan_konversi'][$item['satuan']]['harga_jual'])) {
                            SatuanKonversi::where('barang_id', $barang->id)
                                ->where('nama_satuan', $item['satuan'])
                                ->update(['harga_jual' => $item['satuan_konversi'][$item['satuan']]['harga_jual']]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('pembelian.show', $pembelian->id)
                             ->with('success', 'Pembelian berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Pembelian Error', [
                'pembelian_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function cetakBarcode($id)
    {
        $pembelian = Pembelian::with(['detailPembelian.barang', 'cabang', 'supplier'])->findOrFail($id);
        
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        return view('pages.pembelian.cetak-barcode', compact('pembelian'));
    }

    public function generateBarcode(Request $request)
    {
        $request->validate([
            'detail_pembelian_id' => 'required|exists:detail_pembelian,id',
            'jumlah_cetak' => 'required|integer|min:1|max:1000'
        ]);
        
        $detail = DetailPembelian::with('barang')->findOrFail($request->detail_pembelian_id);
        $barang = $detail->barang;
        
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        $barcodes = [];
        for ($i = 0; $i < $request->jumlah_cetak; $i++) {
            $barcodes[] = [
                'barcode' => $barang->barcode ?? $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'harga' => $barang->harga_jual,
                'kode' => $barang->kode_barang,
            ];
        }
        
        return response()->json([
            'success' => true,
            'barcodes' => $barcodes
        ]);
    }

    public function generateBarcodeAll(Request $request, $id)
    {
        $request->validate([
            'mode' => 'required|in:qty,custom',
            'jumlah_custom' => 'required_if:mode,custom|nullable|integer|min:1|max:100'
        ]);
        
        $pembelian = Pembelian::with('detailPembelian.barang')->findOrFail($id);
        
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        $barcodes = [];
        
        foreach ($pembelian->detailPembelian as $detail) {
            $barang = $detail->barang;
            
            if (!$barang) continue;
            
            $jumlahCetak = $request->mode === 'qty' 
                ? (int) $detail->jumlah 
                : (int) $request->jumlah_custom;
            
            for ($i = 0; $i < $jumlahCetak; $i++) {
                $barcodes[] = [
                    'barcode' => $barang->barcode ?? $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'harga' => $barang->harga_jual,
                    'kode' => $barang->kode_barang,
                    'qty_asli' => $detail->jumlah,
                    'satuan' => $detail->satuan,
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'barcodes' => $barcodes,
            'total_items' => count($barcodes)
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);
            
            $cabangId = $this->getActiveCabangId();
            if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
                abort(403, 'Pembelian ini bukan milik cabang Anda.');
            }

            if ($pembelian->status === 'approved') {
                foreach ($pembelian->detailPembelian as $detail) {
                    $barang = $detail->barang;
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);

                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'penyesuaian',
                        jumlahPerubahan: -$qtyDasar,
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Hapus Pembelian " . $pembelian->nomor_pembelian,
                        nomorReferensi: $pembelian->nomor_pembelian,
                        cabangId: $cabangId
                    );

                    $this->updateHargaJual($detail, $barang);
                }
            }

            $pembelian->delete();

            DB::commit();

            return redirect()->route('pembelian.index')
                             ->with('success', 'Pembelian berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Konversi qty ke satuan terkecil (stok dasar)
     */
    private function convertToStokDasar($qty, $satuan, $barang)
    {
        if ($satuan === $barang->satuan_terkecil) {
            return $qty;
        }

        $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                 ->where('nama_satuan', $satuan)
                                 ->first();

        if (!$konversi) {
            Log::warning('Satuan konversi tidak ditemukan', [
                'barang_id' => $barang->id,
                'satuan' => $satuan
            ]);
            return $qty;
        }

        return $qty * $konversi->jumlah_konversi;
    }

    /**
     * Update harga jual berdasarkan detail pembelian
     */
    private function updateHargaJual($detail, $barang)
    {
        if (!isset($detail->harga_jual_baru)) {
            return;
        }

        if ($detail->satuan === $barang->satuan_terkecil) {
            $barang->update([
                'harga_beli' => $detail->harga_beli,
                'harga_jual' => $detail->harga_jual_baru
            ]);
        } else {
            $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                     ->where('nama_satuan', $detail->satuan)
                                     ->first();
            
            if ($konversi) {
                $konversi->update(['harga_jual' => $detail->harga_jual_baru]);
            }
        }
    }

    /**
     * Update atau Insert Satuan Konversi
     */
    private function updateSatuanKonversi($barangId, $satuanData)
    {
        Log::info('updateSatuanKonversi called', [
            'barang_id' => $barangId,
            'satuan_data' => $satuanData
        ]);

        $barang = Barang::findOrFail($barangId);

        foreach ($satuanData as $namaSatuan => $data) {
            // Skip jika data tidak lengkap
            if (empty($data['nama_satuan']) || empty($data['jumlah_konversi'])) {
                Log::warning('Skipping incomplete satuan data', ['key' => $namaSatuan, 'data' => $data]);
                continue;
            }

            // ✅ Jika ini satuan dasar/terkecil, update di tabel barang
            if ($data['nama_satuan'] === $barang->satuan_terkecil) {
                $updateData = [];
                
                // Update harga jual jika ada
                if (isset($data['harga_jual']) && $data['harga_jual'] > 0) {
                    $updateData['harga_jual'] = $data['harga_jual'];
                }
                
                if (!empty($updateData)) {
                    $barang->update($updateData);
                    
                    Log::info('Harga Barang (Satuan Dasar) UPDATED', [
                        'barang_id' => $barangId,
                        'satuan' => $data['nama_satuan'],
                        'data' => $updateData
                    ]);
                }
                
                // Skip, tidak perlu insert ke satuan_konversi
                continue;
            }

            // ✅ Untuk satuan konversi (bukan satuan dasar)
            $dataToSave = [
                'jumlah_konversi' => $data['jumlah_konversi'],
                'harga_jual' => $data['harga_jual'] ?? 0,
                'is_default' => isset($data['is_default']) ? 1 : 0,
            ];

            // Cari satuan yang sudah ada berdasarkan nama
            $existing = SatuanKonversi::where('barang_id', $barangId)
                                     ->where('nama_satuan', $data['nama_satuan'])
                                     ->first();
            
            if ($existing) {
                // ✅ UPDATE satuan yang sudah ada
                $existing->update($dataToSave);
                
                Log::info('Satuan Konversi UPDATED', [
                    'id' => $existing->id,
                    'barang_id' => $barangId,
                    'nama_satuan' => $data['nama_satuan'],
                    'data' => $dataToSave
                ]);
            } else {
                // ✅ INSERT satuan baru
                $dataToSave['barang_id'] = $barangId;
                $dataToSave['nama_satuan'] = $data['nama_satuan'];
                
                $newKonversi = SatuanKonversi::create($dataToSave);
                
                Log::info('Satuan Konversi CREATED', [
                    'id' => $newKonversi->id,
                    'barang_id' => $barangId,
                    'nama_satuan' => $data['nama_satuan'],
                    'data' => $dataToSave
                ]);
            }
        }
    }
}