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
    use CabangFilterTrait, RecordsStokHistory; // ✅ GUNAKAN KEDUA TRAIT

    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user', 'detailPembelian.barang', 'cabang']);

        // ✅ APPLY FILTER CABANG
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

        // ✅ APPLY FILTER CABANG
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

            // ✅ CEK AKSES CABANG
            $cabangId = $this->getActiveCabangId();
            if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
                abort(403, 'Pembelian ini bukan milik cabang Anda.');
            }

            if ($pembelian->status !== 'pending') {
                return back()->with('error', 'Pembelian ini sudah diproses!');
            }

            foreach ($pembelian->detailPembelian as $detail) {
                $barang = $detail->barang;
                
                // ✅ VALIDASI: Barang harus dari cabang yang sama
                if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Barang {$barang->nama_barang} bukan milik cabang ini.");
                }
                
                $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                $barang->increment('stok', $qtyDasar);

                // ✅ CATAT RIWAYAT STOK
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
        
        // ✅ BARANG DENGAN FILTER CABANG
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
            // ✅ AMBIL CABANG ID
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
                'cabang_id' => $cabangId, // ✅ SIMPAN CABANG ID
                'status' => $status,
                'keterangan' => $request->keterangan
            ]);

            Log::info('Pembelian Created', [
                'pembelian_id' => $pembelian->id,
                'cabang_id' => $pembelian->cabang_id
            ]);

            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                // ✅ VALIDASI CABANG BARANG
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

                    // ✅ CATAT RIWAYAT STOK
                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'pembelian', 
                        jumlahPerubahan: $qtyDasar, 
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Pembelian dari " . Supplier::find($request->supplier_id)->nama_supplier,
                        nomorReferensi: $request->no_faktur,
                        cabangId: $cabangId
                    );
                                
                    // ✅ UPDATE HARGA BELI (satuan dasar)
                    if ($item['satuan'] === $barang->satuan_terkecil) {
                        $barang->update(['harga_beli' => $item['harga_beli']]);
                    }
                    
                    // ✅ UPDATE HARGA JUAL dari form (jika ada input harga jual untuk satuan dasar)
                    if (isset($item['satuan_konversi'][$barang->satuan_terkecil]['harga_jual'])) {
                        $barang->update(['harga_jual' => $item['satuan_konversi'][$barang->satuan_terkecil]['harga_jual']]);
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
        
        // ✅ CEK AKSES CABANG
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        return view('pages.pembelian.show', compact('pembelian'));
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with('detailPembelian')->findOrFail($id);
        
        // ✅ CEK AKSES CABANG
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        $suppliers = Supplier::all();
        
        // ✅ BARANG DENGAN FILTER CABANG
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
            
            // ✅ CEK AKSES CABANG
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
                    
                    // ✅ VALIDASI CABANG
                    if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                        throw new \Exception("Barang {$barang->nama_barang} bukan milik cabang ini.");
                    }
                    
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);

                    // ✅ CATAT RIWAYAT (kembalikan stok lama)
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
                // cabang_id tidak diubah (tetap cabang awal)
            ]);

            // Simpan detail baru
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);
                
                // ✅ VALIDASI CABANG
                if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
                    throw new \Exception("Barang '{$barang->nama_barang}' bukan milik cabang Anda.");
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

                // Update Satuan Konversi
                if (isset($item['satuan_konversi']) && is_array($item['satuan_konversi'])) {
                    $this->updateSatuanKonversi($barang->id, $item['satuan_konversi']);
                }

                // Update stok jika status baru APPROVED
                if ($statusBaru === 'approved') {
                    $barang->increment('stok', $qtyDasar);

                    // ✅ CATAT RIWAYAT STOK BARU
                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'pembelian',
                        jumlahPerubahan: $qtyDasar, 
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Update Pembelian dari " . Supplier::find($request->supplier_id)->nama_supplier,
                        nomorReferensi: $request->no_faktur,
                        cabangId: $cabangId
                    );
                    
                    if ($item['satuan'] === $barang->satuan_terkecil) {
                        $barang->update(['harga_beli' => $item['harga_beli']]);
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
        
        // ✅ CEK AKSES CABANG
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            abort(403, 'Pembelian ini bukan milik cabang Anda.');
        }
        
        return view('pages.pembelian.cetak-barcode', compact('pembelian'));
    }

    /**
     * Generate barcode untuk item tertentu (AJAX)
     */
    public function generateBarcode(Request $request)
    {
        $request->validate([
            'detail_pembelian_id' => 'required|exists:detail_pembelian,id',
            'jumlah_cetak' => 'required|integer|min:1|max:1000'
        ]);
        
        $detail = DetailPembelian::with('barang')->findOrFail($request->detail_pembelian_id);
        $barang = $detail->barang;
        
        // ✅ VALIDASI CABANG
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $barang->cabang_id != $cabangId) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        // Generate barcode data
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

    /**
     * Generate barcode untuk semua item dalam pembelian (AJAX)
     */
    public function generateBarcodeAll(Request $request, $id)
    {
        $request->validate([
            'mode' => 'required|in:qty,custom',
            'jumlah_custom' => 'required_if:mode,custom|nullable|integer|min:1|max:100'
        ]);
        
        $pembelian = Pembelian::with('detailPembelian.barang')->findOrFail($id);
        
        // ✅ CEK AKSES CABANG
        $cabangId = $this->getActiveCabangId();
        if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        
        $barcodes = [];
        
        foreach ($pembelian->detailPembelian as $detail) {
            $barang = $detail->barang;
            
            if (!$barang) continue;
            
            // Tentukan jumlah cetak
            $jumlahCetak = $request->mode === 'qty' 
                ? (int) $detail->jumlah 
                : (int) $request->jumlah_custom;
            
            // Generate barcode sebanyak jumlah yang diminta
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
            
            // ✅ CEK AKSES CABANG
            $cabangId = $this->getActiveCabangId();
            if (!$this->isSuperAdmin() && $pembelian->cabang_id != $cabangId) {
                abort(403, 'Pembelian ini bukan milik cabang Anda.');
            }

            if ($pembelian->status === 'approved') {
                foreach ($pembelian->detailPembelian as $detail) {
                    $barang = $detail->barang;
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);

                    // ✅ CATAT RIWAYAT STOK
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
        }

        return $konversi ? ($qty * $konversi->jumlah_konversi) : $qty;
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
        foreach ($satuanData as $satuan => $data) {
            if (empty($data['nama_satuan']) || empty($data['jumlah_konversi'])) {
                continue;
            }

            $dataToUpdate = [
                'jumlah_konversi' => $data['jumlah_konversi'],
                'harga_jual' => $data['harga_jual'] ?? 0,
                'is_default' => isset($data['is_default']) ? 1 : 0,
            ];

            // ✅ PERUBAHAN: Cek apakah ini satuan BARU atau UPDATE
            if (!empty($data['id'])) {
                // Update satuan yang sudah ada (berdasarkan ID)
                SatuanKonversi::where('id', $data['id'])
                             ->where('barang_id', $barangId)
                             ->update($dataToUpdate);
                
                Log::info('Update Satuan Konversi (by ID)', [
                    'satuan_id' => $data['id'],
                    'barang_id' => $barangId,
                    'data' => $dataToUpdate
                ]);
            } else {
                // Cek apakah satuan dengan nama ini sudah ada
                $existing = SatuanKonversi::where('barang_id', $barangId)
                                         ->where('nama_satuan', $data['nama_satuan'])
                                         ->first();
                
                if ($existing) {
                    // ✅ UPDATE (jangan insert baru)
                    $existing->update($dataToUpdate);
                    
                    Log::info('Update Satuan Konversi (by name)', [
                        'satuan_id' => $existing->id,
                        'nama_satuan' => $data['nama_satuan'],
                        'data' => $dataToUpdate
                    ]);
                } else {
                    // Insert baru HANYA jika satuan benar-benar belum ada
                    $dataToUpdate['barang_id'] = $barangId;
                    $dataToUpdate['nama_satuan'] = $data['nama_satuan'];
                    
                    SatuanKonversi::create($dataToUpdate);
                    
                    Log::info('Insert Satuan Konversi Baru', [
                        'barang_id' => $barangId,
                        'nama_satuan' => $data['nama_satuan']
                    ]);
                }
            }
        }
    }
}