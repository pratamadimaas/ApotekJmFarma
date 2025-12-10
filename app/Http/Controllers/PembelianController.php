<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\SatuanKonversi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user', 'detailPembelian.barang']);

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
        $query = Pembelian::with(['supplier', 'user', 'detailPembelian.barang'])
                         ->where('status', 'pending');

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

            if ($pembelian->status !== 'pending') {
                return back()->with('error', 'Pembelian ini sudah diproses!');
            }

            foreach ($pembelian->detailPembelian as $detail) {
                $barang = $detail->barang;
                
                $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                $barang->increment('stok', $qtyDasar);

                $this->updateHargaJual($detail, $barang);
            }

            $pembelian->update(['status' => 'approved']);

            DB::commit();

            return redirect()->route('pembelian.show', $pembelian->id)
                           ->with('success', 'Pembelian berhasil diapprove dan stok telah ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $barang = Barang::aktif()->get();

        return view('pages.pembelian.create', compact('suppliers', 'barang'));
    }

    // 🔥 Store dengan handling satuan konversi
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
                'status' => $status,
                'keterangan' => $request->keterangan
            ]);

            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

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

                // 🔥 Update/Insert Satuan Konversi jika ada
                if (isset($item['satuan_konversi']) && is_array($item['satuan_konversi'])) {
                    $this->updateSatuanKonversi($barang->id, $item['satuan_konversi']);
                }

                // Update stok dan harga HANYA jika APPROVED
                if ($status === 'approved') {
                    $barang->increment('stok', $qtyDasar);
                    
                    // Update harga beli barang (satuan dasar)
                    if ($item['satuan'] === $barang->satuan_terkecil) {
                        $barang->update(['harga_beli' => $item['harga_beli']]);
                    }
                }
            }

            DB::commit();

            $message = $status === 'pending' 
                ? 'Pembelian berhasil disimpan sebagai PENDING!' 
                : 'Pembelian berhasil disimpan dan APPROVED!';

            return redirect()->route('pembelian.show', $pembelian->id)
                             ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['supplier', 'user', 'detailPembelian.barang'])->findOrFail($id);
        
        return view('pages.pembelian.show', compact('pembelian'));
    }

    public function edit($id)
    {
        $pembelian = Pembelian::with('detailPembelian')->findOrFail($id);
        $suppliers = Supplier::all();
        $barang = Barang::aktif()->get();

        return view('pages.pembelian.edit', compact('pembelian', 'suppliers', 'barang'));
    }

    // 🔥 Update dengan handling satuan konversi
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
            $statusLama = $pembelian->status;
            $statusBaru = $request->status ?? 'approved';

            // Kembalikan stok jika status lama APPROVED
            if ($statusLama === 'approved') {
                foreach ($pembelian->detailPembelian as $detail) {
                    $barang = $detail->barang;
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);
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

                // 🔥 Update Satuan Konversi
                if (isset($item['satuan_konversi']) && is_array($item['satuan_konversi'])) {
                    $this->updateSatuanKonversi($barang->id, $item['satuan_konversi']);
                }

                // Update stok jika status baru APPROVED
                if ($statusBaru === 'approved') {
                    $barang->increment('stok', $qtyDasar);
                    
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
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);

            if ($pembelian->status === 'approved') {
                foreach ($pembelian->detailPembelian as $detail) {
                    $barang = $detail->barang;
                    $qtyDasar = $this->convertToStokDasar($detail->jumlah, $detail->satuan, $barang);
                    $barang->decrement('stok', $qtyDasar);
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

    // 🔥 HELPER METHODS

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
     * 🔥 Update atau Insert Satuan Konversi
     */
    private function updateSatuanKonversi($barangId, $satuanData)
    {
        foreach ($satuanData as $satuan => $data) {
            // Skip jika data tidak lengkap
            if (empty($data['nama_satuan']) || empty($data['jumlah_konversi'])) {
                continue;
            }

            $dataToUpdate = [
                'barang_id' => $barangId,
                'nama_satuan' => $data['nama_satuan'],
                'jumlah_konversi' => $data['jumlah_konversi'],
                'harga_jual' => $data['harga_jual'] ?? 0,
                'is_default' => isset($data['is_default']) ? 1 : 0,
            ];

            // Jika ada ID, update; jika tidak, insert baru
            if (!empty($data['id'])) {
                SatuanKonversi::where('id', $data['id'])->update($dataToUpdate);
            } else {
                // Cek apakah satuan sudah ada
                $existing = SatuanKonversi::where('barang_id', $barangId)
                                         ->where('nama_satuan', $data['nama_satuan'])
                                         ->first();
                
                if ($existing) {
                    $existing->update($dataToUpdate);
                } else {
                    SatuanKonversi::create($dataToUpdate);
                }
            }
        }
    }
}