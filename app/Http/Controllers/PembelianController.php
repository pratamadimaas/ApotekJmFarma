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

        $pembelian = $query->orderBy('tanggal_pembelian', 'desc')->paginate(20);
        $suppliers = Supplier::all();

        return view('pages.pembelian.index', compact('pembelian', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $barang = Barang::aktif()->get();

        return view('pages.pembelian.create', compact('suppliers', 'barang'));
    }

    // ✅ Store Pembelian - HANDLE MULTI SATUAN & UPDATE HARGA
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
            'items.*.harga_jual' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create([
                'nomor_pembelian' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total_pembelian' => $request->total_harga,
                'diskon' => $request->diskon ?? 0,
                'pajak' => $request->ppn ?? 0,
                'grand_total' => $request->total_bayar,
                'user_id' => Auth::id(),
                'status' => $request->status ?? 'approved',
                'keterangan' => $request->keterangan
            ]);

            // ✅ Simpan Detail & Update Harga Per Satuan
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                // Hitung qty dalam satuan terkecil
                $qtyDasar = $item['qty'];
                
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $item['qty'] * $konversi->jumlah_konversi;
                    }
                }

                // Simpan detail
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal' => $item['qty'] * $item['harga_beli'],
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'] ?? null,
                ]);

                // Tambah stok
                $barang->increment('stok', $qtyDasar);

                // ✅ UPDATE HARGA: Jika satuan bukan dasar, update di satuan_konversi
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    
                    if ($konversi) {
                        $konversi->update([
                            'harga_jual' => $item['harga_jual']
                        ]);
                    }
                } else {
                    // Update harga dasar di tabel barang
                    $barang->update([
                        'harga_beli' => $item['harga_beli'],
                        'harga_jual' => $item['harga_jual']
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('pembelian.show', $pembelian->id)
                             ->with('success', 'Pembelian berhasil disimpan!');

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

    // ✅ Update Pembelian - HANDLE MULTI SATUAN
    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'no_faktur' => 'required|string|unique:pembelian,nomor_pembelian,' . $id,
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id', // ✅ TAMBAHKAN validasi ini
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.harga_jual' => 'required|numeric|min:0', // ✅ TAMBAHKAN validasi ini
            'total_harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::findOrFail($id);

            // Kembalikan stok lama
            foreach ($pembelian->detailPembelian as $detail) {
                $barang = $detail->barang;
                
                $qtyDasar = $detail->jumlah;
                if ($detail->satuan !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $detail->satuan)
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $detail->jumlah * $konversi->jumlah_konversi;
                    }
                }
                
                $barang->decrement('stok', $qtyDasar);
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
                'status' => $request->status ?? 'approved',
                'keterangan' => $request->keterangan
            ]);

            // Simpan detail baru
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                $qtyDasar = $item['qty'];
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $item['qty'] * $konversi->jumlah_konversi;
                    }
                }

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal' => $item['qty'] * $item['harga_beli'],
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'] ?? null
                ]);

                $barang->increment('stok', $qtyDasar);
                
                // Update harga
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    if ($konversi) {
                        $konversi->update(['harga_jual' => $item['harga_jual']]);
                    }
                } else {
                    $barang->update([
                        'harga_beli' => $item['harga_beli'],
                        'harga_jual' => $item['harga_jual']
                    ]);
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

            // Kembalikan stok
            foreach ($pembelian->detailPembelian as $detail) {
                $barang = $detail->barang;
                
                $qtyDasar = $detail->jumlah;
                if ($detail->satuan !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $detail->satuan)
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $detail->jumlah * $konversi->jumlah_konversi;
                    }
                }
                
                $barang->decrement('stok', $qtyDasar);
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
}