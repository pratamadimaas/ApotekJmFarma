<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanKonversi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    // ------------------------------------
    // CRUD Barang (Index, Create, Store, Show, Edit, Update, Destroy)
    // ------------------------------------

    public function index(Request $request)
{
    $query = Barang::query();
    
    // 1. Pencarian
    if ($request->filled('search')) {
        $search = $request->search;
        $query->search($search);
    }
    
    // 2. Filter Kategori
    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    // 3. Filter Stok (Disesuaikan untuk menerima nilai numerik)
    if ($request->filled('stok_filter')) {
        $filterValue = $request->stok_filter;
        
        if ($filterValue === 'rendah') {
            $query->stokRendah(); // Menggunakan scopeStokRendah()
        } elseif (is_numeric($filterValue) && $filterValue > 0) {
            // Filter stok kurang dari nilai yang diberikan (e.g., < 50, < 20)
            $query->where('stok', '<', (float) $filterValue);
        }
    }
    
    $barang = $query->orderBy('nama_barang', 'asc')->paginate(20);
    $kategoriList = Barang::select('kategori')->distinct()->pluck('kategori');
    
    // Opsi Filter Stok Baru untuk dikirim ke View
    $stokFilterOptions = [
        10 => '< 10 Unit',
        20 => '< 20 Unit',
        50 => '< 50 Unit',
    ];
    
    return view('pages.barang.index', compact('barang', 'kategoriList', 'stokFilterOptions'));
}

    public function create()
    {
        return view('pages.barang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|unique:barang,kode_barang',
            'barcode' => 'nullable|string|max:50|unique:barang,barcode',
            'nama_barang' => 'required|string',
            'kategori' => 'required|string',
            'satuan_dasar' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            'satuan_konversi.*.nama_satuan' => 'nullable|string',
            'satuan_konversi.*.jumlah_konversi' => 'nullable|integer|min:1',
            'satuan_konversi.*.harga_jual' => 'nullable|numeric|min:0',
            'satuan_konversi.*.is_default' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $barang = Barang::create([
                'kode_barang' => $request->kode_barang,
                'barcode' => $request->barcode,
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
            'barcode' => 'nullable|string|max:50|unique:barang,barcode,' . $id,
            'nama_barang' => 'required|string',
            'kategori' => 'required|string',
            'satuan_dasar' => 'required|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            'satuan_konversi.*.nama_satuan' => 'nullable|string',
            'satuan_konversi.*.jumlah_konversi' => 'nullable|integer|min:1',
            'satuan_konversi.*.harga_jual' => 'nullable|numeric|min:0',
            'satuan_konversi.*.is_default' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($id);
            
            $barang->update([
                'kode_barang' => $request->kode_barang,
                'barcode' => $request->barcode,
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

            $barang->satuanKonversi()->delete();

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

    // ------------------------------------
    // FUNGSI KHUSUS
    // ------------------------------------

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

    // ✅ API Search untuk Stok Opname
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $barang = Barang::where('nama_barang', 'LIKE', "%{$query}%")
            ->orWhere('kode_barang', 'LIKE', "%{$query}%")
            ->orWhere('barcode', 'LIKE', "%{$query}%")
            ->select('id', 'nama_barang', 'kode_barang', 'barcode', 'stok', 'lokasi_rak')
            ->limit(20)
            ->get();

        return response()->json($barang);
    }

    // Daftar Harga Satuan
    public function hargaSatuan(Request $request)
    {
        // 1. Inisialisasi Query
        $query = Barang::with('satuanKonversi')->where('aktif', 1);


        if ($request->filled('search')) {
            $search = $request->search;
            $query->search($search); // Menggunakan scopeSearch dari model Barang
        }


        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }


        if ($request->filled('stok_filter')) {
            $stokLimit = (int) $request->stok_filter;
            if ($stokLimit > 0) {
                $query->where('stok', '<', $stokLimit);
            }
        }
        
        // Menambahkan filter stok rendah/minimal secara default (jika relevan)
        if ($request->get('stok_rendah') == 'true') {
            $query->stokRendah();
        }

        // 5. Eksekusi Query dan Pagination
        $barang = $query->orderBy('nama_barang', 'asc')->paginate(20);

        // 6. Ambil daftar kategori unik untuk filter
        $kategoriList = Barang::select('kategori')->distinct()->pluck('kategori');

        // 7. Data untuk filter stok
        $stokFilterOptions = [
            10 => '< 10',
            15 => '< 15',
            20 => '< 20',
            50 => '< 50',
        ];

        return view('pages.barang.harga-satuan', compact('barang', 'kategoriList', 'stokFilterOptions'));
    }
    // AJAX Get Satuan Konversi
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

    // ✅ Cari Barang by Barcode (Future Use)
    public function getByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        
        if (!$barcode) {
            return response()->json(['error' => 'Barcode tidak boleh kosong'], 400);
        }

        $barang = Barang::with('satuanKonversi')
                        ->where('barcode', $barcode)
                        ->first();

        if (!$barang) {
            return response()->json(['error' => 'Barang dengan barcode tersebut tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'barcode' => $barang->barcode,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->kategori,
                'satuan_terkecil' => $barang->satuan_terkecil,
                'harga_jual' => $barang->harga_jual,
                'stok' => $barang->stok,
                'satuan_konversi' => $barang->satuanKonversi,
            ]
        ]);
    }
}