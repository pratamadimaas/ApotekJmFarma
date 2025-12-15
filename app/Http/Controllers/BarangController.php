<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanKonversi;
use App\Models\Cabang;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarangController extends Controller
{
    use CabangFilterTrait;

    // ------------------------------------
    // CRUD Barang (Index, Create, Store, Show, Edit, Update, Destroy)
    // ------------------------------------

    public function index(Request $request)
    {
        // === 🔍 DEBUG START ===
        Log::info('=== BARANG INDEX DEBUG START ===');
        Log::info('Auth User Info:', [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role,
            'cabang_id' => auth()->user()->cabang_id,
        ]);

        $cabangId = $this->getActiveCabangId();
        Log::info('Active Cabang ID from Trait:', ['cabang_id' => $cabangId]);

        // Cek total data di database
        $totalBarangAll = Barang::count();
        $totalBarangThisCabang = Barang::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))->count();
        
        Log::info('Database Stats:', [
            'total_all_barang' => $totalBarangAll,
            'total_barang_cabang_' . $cabangId => $totalBarangThisCabang,
        ]);

        // Cek 5 data terakhir tanpa filter
        $latestBarang = Barang::orderBy('id', 'desc')->limit(5)->get(['id', 'kode_barang', 'nama_barang', 'cabang_id']);
        Log::info('Latest 5 Barang (all cabang):', $latestBarang->toArray());

        // Cek data di cabang ini
        $barangCabangIni = Barang::where('cabang_id', $cabangId)->orderBy('id', 'desc')->limit(5)->get(['id', 'kode_barang', 'nama_barang', 'cabang_id']);
        Log::info('Latest 5 Barang (cabang ' . $cabangId . '):', $barangCabangIni->toArray());
        // === 🔍 DEBUG END ===
        
        $query = Barang::query();
        
        // ✅ FILTER CABANG - Wajib diterapkan
        $query->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId));
        
        // 1. Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            Log::info('Search filter applied:', ['search' => $search]);
            $query->search($search);
        }
        
        // 2. Filter Kategori
        if ($request->filled('kategori')) {
            Log::info('Kategori filter applied:', ['kategori' => $request->kategori]);
            $query->where('kategori', $request->kategori);
        }

        // 3. Filter Stok
        if ($request->filled('stok_filter')) {
            $filterValue = $request->stok_filter;
            Log::info('Stok filter applied:', ['stok_filter' => $filterValue]);
            
            if ($filterValue === 'rendah') {
                $query->stokRendah();
            } elseif (is_numeric($filterValue) && $filterValue > 0) {
                $query->where('stok', '<', (float) $filterValue);
            }
        }

        // === 🔍 DEBUG: Log SQL Query ===
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        Log::info('Final SQL Query:', [
            'sql' => $sql,
            'bindings' => $bindings
        ]);
        
        $barang = $query->orderBy('nama_barang', 'asc')->paginate(20);

        Log::info('Query Results:', [
            'total_found' => $barang->total(),
            'per_page' => $barang->perPage(),
            'current_page' => $barang->currentPage(),
            'items_count' => $barang->count(),
        ]);

        if ($barang->count() > 0) {
            Log::info('Barang Found (first 3):', $barang->take(3)->map(function($b) {
                return [
                    'id' => $b->id,
                    'kode' => $b->kode_barang,
                    'nama' => $b->nama_barang,
                    'cabang_id' => $b->cabang_id
                ];
            })->toArray());
        } else {
            Log::warning('⚠️ NO BARANG FOUND WITH CURRENT FILTERS!');
        }

        Log::info('=== BARANG INDEX DEBUG END ===');
        
        // ✅ Kategori list juga harus di-filter per cabang
        $kategoriList = Barang::select('kategori')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->distinct()
            ->pluck('kategori');
        
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
        Log::info('=== STORE BARANG DEBUG START ===');
        Log::info('Request Data:', $request->except(['_token']));
        
        // ✅ TENTUKAN CABANG_ID DULU
        $cabangId = null;
        
        if (auth()->user()->isSuperAdmin()) {
            $cabangId = $request->cabang_id;
        } else {
            $cabangId = auth()->user()->cabang_id;
        }
        
        // ✅ VALIDASI FINAL: cabang_id HARUS ada!
        if (!$cabangId) {
            return back()->with('error', 'Error: Cabang tidak valid. Silakan pilih cabang atau hubungi administrator.')->withInput();
        }
        
        // ✅ VALIDASI TANPA UNIQUE - Izinkan duplikat kode dan barcode
        $rules = [
            'kode_barang' => 'required|string|max:50',
            'barcode' => 'nullable|string|max:50',
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
        ];

        // ✅ Untuk Super Admin, cabang_id wajib dari form
        if (auth()->user()->isSuperAdmin()) {
            $rules['cabang_id'] = 'required|exists:cabang,id';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $barangData = [
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
                'deskripsi' => $request->deskripsi,
                'cabang_id' => $cabangId
            ];

            Log::info('Barang Data to Insert:', $barangData);

            $barang = Barang::create($barangData);
            
            Log::info('✅ Barang Created Successfully!', [
                'barang_id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'cabang_id' => $barang->cabang_id
            ]);

            // Satuan Konversi
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
                        Log::info('Satuan Konversi Created:', $konversi);
                    }
                }
            }

            DB::commit();
            Log::info('=== STORE BARANG DEBUG END - SUCCESS ===');
            
            $cabangName = Cabang::find($cabangId)->nama_cabang ?? 'cabang yang tidak diketahui';
            return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan ke ' . $cabangName . '!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== STORE BARANG DEBUG END - ERROR ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::with('satuanKonversi')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
            
        return view('pages.barang.show', compact('barang'));
    }

    public function edit($id)
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::with('satuanKonversi')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
            
        return view('pages.barang.edit', compact('barang'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $cabangId = $this->getActiveCabangId();
            
            $barang = Barang::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                ->findOrFail($id);
            
            // ✅ VALIDASI TANPA UNIQUE - Izinkan duplikat kode dan barcode
            $request->validate([
                'kode_barang' => 'required|string|max:50',
                'barcode' => 'nullable|string|max:50',
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
            $cabangId = $this->getActiveCabangId();
            
            $barang = Barang::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                ->findOrFail($id);
            
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
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::whereRaw('stok <= stok_minimal')
                             ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                             ->orderBy('stok', 'asc')
                             ->get();
                             
        return view('pages.barang.stok-minimal', compact('barang'));
    }

    public function adjustmenForm($id)
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
            
        return view('pages.barang.adjustment', compact('barang'));
    }
    
    public function adjustmenStore(Request $request, $id)
    {
        $request->validate([
            'tipe' => 'required|in:tambah,kurang',
            'qty' => 'required|numeric|min:1',
            'keterangan' => 'required|string'
        ]);

        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);

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

    // ✅ API Search untuk Stok Opname (dengan filter cabang)
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $cabangId = $this->getActiveCabangId();

        $barang = Barang::where(function($q) use ($query) {
                $q->where('nama_barang', 'LIKE', "%{$query}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$query}%")
                  ->orWhere('barcode', 'LIKE', "%{$query}%");
            })
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->select('id', 'nama_barang', 'kode_barang', 'barcode', 'stok', 'lokasi_rak')
            ->limit(20)
            ->get();

        return response()->json($barang);
    }

    // ✅ API Search untuk Kasir/POS (dengan filter cabang)
    public function cariBarang(Request $request)
    {
        $keyword = $request->get('q', '');
        
        if (strlen($keyword) < 1) {
            return response()->json([]);
        }

        $cabangId = $this->getActiveCabangId();

        // 🔥 PENTING: Eager load satuanKonversi dan filter cabang
        $barang = Barang::with('satuanKonversi')
                     ->where('stok', '>', 0)
                     ->where('aktif', 1)
                     ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                     ->where(function($query) use ($keyword) {
                         $query->where('nama_barang', 'LIKE', "%{$keyword}%")
                               ->orWhere('kode_barang', 'LIKE', "%{$keyword}%")
                               ->orWhere('barcode', 'LIKE', "%{$keyword}%");
                     })
                     ->select('id', 'kode_barang', 'barcode', 'nama_barang', 'kategori', 
                              'satuan_terkecil', 'harga_jual', 'stok', 'stok_minimal')
                     ->limit(10)
                     ->get();

        return response()->json($barang);
    }

    // ✅ Get Detail Barang untuk Kasir (dengan filter cabang)
    public function getBarang($id)
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::with('satuanKonversi')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
        
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
            'barcode' => $barang->barcode,
            'nama_barang' => $barang->nama_barang,
            'harga_jual' => $barang->harga_jual,
            'stok' => $barang->stok,
            'satuan_dasar' => $barang->satuan_terkecil,
            'satuan_konversi' => $satuanKonversi,
        ]);
    }

    // Daftar Harga Satuan (dengan filter cabang)
    public function hargaSatuan(Request $request)
    {
        $cabangId = $this->getActiveCabangId();
        
        $query = Barang::with('satuanKonversi')
            ->where('aktif', 1)
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->search($search);
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
        
        if ($request->get('stok_rendah') == 'true') {
            $query->stokRendah();
        }

        $barang = $query->orderBy('nama_barang', 'asc')->paginate(20);
        
        $kategoriList = Barang::select('kategori')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->distinct()
            ->pluck('kategori');

        $stokFilterOptions = [
            10 => '< 10',
            15 => '< 15',
            20 => '< 20',
            50 => '< 50',
        ];

        return view('pages.barang.harga-satuan', compact('barang', 'kategoriList', 'stokFilterOptions'));
    }

    // AJAX Get Satuan Konversi (dengan filter cabang)
    public function getSatuan($id)
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::with('satuanKonversi')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
        
        $satuanKonversi = $barang->satuanKonversi->map(function($konv) {
            return [
                'id' => $konv->id,
                'nama_satuan' => $konv->nama_satuan,
                'jumlah_konversi' => $konv->jumlah_konversi,
                'harga_jual' => $konv->harga_jual,
                'is_default' => $konv->is_default,
            ];
        });

        return response()->json([
            'satuan_dasar' => $barang->satuan_terkecil,
            'harga_beli' => $barang->harga_beli,
            'harga_jual' => $barang->harga_jual,
            'konversi' => $satuanKonversi,
        ]);
    }

    // ✅ Cari Barang by Barcode Langsung (dengan filter cabang)
    public function getByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        
        if (!$barcode) {
            return response()->json(['error' => 'Barcode tidak boleh kosong'], 400);
        }

        $cabangId = $this->getActiveCabangId();

        $barang = Barang::with('satuanKonversi')
                         ->where('barcode', $barcode)
                         ->where('aktif', 1)
                         ->where('stok', '>', 0)
                         ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
                         ->first();

        if (!$barang) {
            return response()->json(['error' => 'Barang dengan barcode tersebut tidak ditemukan'], 404);
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
                'satuan_dasar' => $barang->satuan_terkecil,
                'satuan_konversi' => $satuanKonversi,
            ]
        ]);
    }

    // ✅ IMPORT EXPORT EXCEL
    public function importForm()
    {
        return view('pages.barang.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        // Inisialisasi import di luar blok try-catch agar dapat diakses oleh kedua catch
        $import = new \App\Imports\BarangImport();
        $cabangId = null; // Variabel untuk menyimpan ID cabang target

        try {
            
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $cabangId = $import->getActiveCabangId(); // Ambil ID cabang yang berhasil digunakan
            $cabangName = $cabangId ? Cabang::find($cabangId)->nama_cabang : 'Semua Cabang';


            $imported = $import->getImportedCount();
            $skipped = $import->getSkippedCount();
            $errors = $import->getErrors();

            $message = "Import berhasil! {$imported} data ditambahkan ke cabang **{$cabangName}**";
            
            if ($skipped > 0) {
                $message .= ", {$skipped} data dilewati (duplikat, validasi, atau error)";
            }

            if (!empty($errors)) {
                session()->flash('import_errors', $errors);
            }

            return redirect()->route('barang.index')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            // Tambahkan juga error dari SkipsOnError jika ada (jika import tidak sepenuhnya berhenti)
            $errors = array_merge($errors, $import->getErrors());
            
            return back()->with('error', 'Validasi gagal. Mohon periksa file Anda.')->with('import_errors', $errors);

        } catch (\Exception $e) {
            // Error catch-all, biasanya dari logic di constructor atau jika import berhenti total
            $errors = $import->getErrors();
            
            // Cek apakah error dari class import itu sendiri (misalnya Cabang ID NULL untuk SA)
            if (!empty($errors)) {
                return back()->with('error', 'Import Gagal Kritis: ' . $errors[0])->with('import_errors', $errors);
            }
            
            Log::error('Fatal Import Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error fatal saat memproses file: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BarangTemplateExport(), 
            'template_import_barang.xlsx'
        );
    }

    public function exportExcel()
    {
        $cabangId = $this->getActiveCabangId();
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BarangExport($cabangId),
            'data_barang_' . date('Y-m-d') . '.xlsx'
        );
    }
}