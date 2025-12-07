<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanKonversi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'LIKE', "%{$search}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        $barang = $query->orderBy('nama_barang', 'asc')->paginate(20);
        $kategoriList = Barang::select('kategori')->distinct()->pluck('kategori');
        return view('pages.barang.index', compact('barang', 'kategoriList'));
    }

    public function create()
    {
        return view('pages.barang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|unique:barang,kode_barang',
            'nama_barang' => 'required|string',
            'kategori' => 'required|string',
            'satuan_dasar' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            // ✅ Validasi untuk satuan konversi
            'satuan_konversi.*.nama_satuan' => 'nullable|string',
            'satuan_konversi.*.jumlah_konversi' => 'nullable|integer|min:1',
            'satuan_konversi.*.harga_jual' => 'nullable|numeric|min:0',
            'satuan_konversi.*.is_default' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $barang = Barang::create([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'kategori' => $request->kategori,
                'satuan_terkecil' => $request->satuan_dasar,
                'harga_beli' => $request->harga_beli,
                'harga_jual' => $request->harga_jual,
                'stok' => $request->stok,
                'stok_minimal' => $request->stok_minimal,
                'lokasi_rak' => $request->lokasi_rak,
                'deskripsi' => $request->deskripsi
            ]);

            // ✅ Simpan satuan konversi dengan struktur baru
            if ($request->filled('satuan_konversi')) {
                foreach ($request->satuan_konversi as $konversi) {
                    if (!empty($konversi['nama_satuan']) && !empty($konversi['jumlah_konversi'])) {
                        SatuanKonversi::create([
                            'barang_id' => $barang->id,
                            'nama_satuan' => $konversi['nama_satuan'],
                            'jumlah_konversi' => $konversi['jumlah_konversi'],
                            'harga_jual' => $konversi['harga_jual'] ?? 0,
                            'is_default' => $konversi['is_default'] ?? false
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $barang = Barang::with('satuanKonversi')->findOrFail($id);
        return view('pages.barang.show', compact('barang'));
    }

    public function edit($id)
    {
        $barang = Barang::with('satuanKonversi')->findOrFail($id);
        return view('pages.barang.edit', compact('barang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_barang' => 'required|string|unique:barang,kode_barang,' . $id,
            'nama_barang' => 'required|string',
            'kategori' => 'required|string',
            'satuan_dasar' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($id);
            
            $barang->update([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'kategori' => $request->kategori,
                'satuan_terkecil' => $request->satuan_dasar,
                'harga_beli' => $request->harga_beli,
                'harga_jual' => $request->harga_jual,
                'stok' => $request->stok,
                'stok_minimal' => $request->stok_minimal,
                'lokasi_rak' => $request->lokasi_rak,
                'deskripsi' => $request->deskripsi
            ]);

            // Hapus satuan konversi lama
            $barang->satuanKonversi()->delete();

            // ✅ Simpan satuan konversi baru
            if ($request->filled('satuan_konversi')) {
                foreach ($request->satuan_konversi as $konversi) {
                    if (!empty($konversi['nama_satuan']) && !empty($konversi['jumlah_konversi'])) {
                        SatuanKonversi::create([
                            'barang_id' => $barang->id,
                            'nama_satuan' => $konversi['nama_satuan'],
                            'jumlah_konversi' => $konversi['jumlah_konversi'],
                            'harga_jual' => $konversi['harga_jual'] ?? 0,
                            'is_default' => $konversi['is_default'] ?? false
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('barang.index')->with('success', 'Barang berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $barang = Barang::findOrFail($id);
            
            if ($barang->detailPenjualan()->count() > 0 || $barang->detailPembelian()->count() > 0) {
                return back()->with('error', 'Barang tidak bisa dihapus karena sudah ada transaksi!');
            }

            $barang->delete();
            return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function stokMinimal()
    {
        $barang = Barang::whereRaw('stok <= stok_minimal')
                         ->orderBy('stok', 'asc')
                         ->get();
        return view('pages.barang.stok-minimal', compact('barang'));
    }

    public function adjustmenForm($id)
    {
        $barang = Barang::findOrFail($id);
        return view('pages.barang.adjustment', compact('barang'));
    }

    // ✅ AJAX Get Satuan - SESUAI STRUKTUR BARU
    public function getSatuan($id)
    {
        $barang = Barang::with('satuanKonversi')->findOrFail($id);
        
        $satuanKonversi = $barang->satuanKonversi->map(function($konv) {
            return [
                'nama_satuan' => $konv->nama_satuan,
                'jumlah_konversi' => $konv->jumlah_konversi,
                'harga_jual' => $konv->harga_jual,
                'is_default' => $konv->is_default,
            ];
        });

        return response()->json([
            'satuan_dasar' => $barang->satuan_terkecil,
            'harga_jual_dasar' => $barang->harga_jual,
            'konversi' => $satuanKonversi,
        ]);
    }

    public function adjustmenStore(Request $request, $id)
    {
        $request->validate([
            'tipe' => 'required|in:tambah,kurang',
            'qty' => 'required|numeric|min:1',
            'keterangan' => 'required|string'
        ]);

        $barang = Barang::findOrFail($id);

        if ($request->tipe === 'tambah') {
            $barang->increment('stok', $request->qty);
        } else {
            if ($barang->stok < $request->qty) {
                return back()->with('error', 'Stok tidak mencukupi!');
            }
            $barang->decrement('stok', $request->qty);
        }
        
        return redirect()->route('barang.index')->with('success', 'Adjustment stok berhasil!');
    }
}