<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanKonversi;
use App\Models\Cabang;
use App\Traits\CabangFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BarangController extends Controller
{
    use CabangFilterTrait;

    public function index(Request $request)
    {
        // ✅ FORCE CLEAR CACHE BEFORE QUERY
        $cabangId = $this->getActiveCabangId();
        
        Cache::forget('barang_list_' . $cabangId);
        Cache::forget('barang_kategori_' . $cabangId);
        
        // === 🔍 DEBUG START ===
        Log::info('=== BARANG INDEX DEBUG START ===', [
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'role' => auth()->user()->role,
                'cabang_id' => auth()->user()->cabang_id,
            ],
            'active_cabang_id' => $cabangId,
            'session_cabang' => session('selected_cabang_id'),
            'request_params' => $request->except('_token')
        ]);
        
        // ✅ QUERY BASE - Force table name dan explicit select
        $query = Barang::from('barang')->select('barang.*');
        
        // ✅ FILTER CABANG - WAJIB dan PERTAMA (sebelum filter lain)
        if ($cabangId) {
            $query->where('barang.cabang_id', '=', $cabangId);
        }
        
        Log::info('Base Query (after cabang filter):', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
        
        // ✅ FILTER 1: PENCARIAN (dengan AND condition untuk cabang)
        if ($request->filled('search')) {
            $search = $request->search;
            
            // PENTING: Gunakan whereRaw atau where dengan parenthesis
            // untuk memastikan filter cabang tidak di-override
            $query->where(function($q) use ($search) {
                $q->where('barang.nama_barang', 'LIKE', "%{$search}%")
                  ->orWhere('barang.kode_barang', 'LIKE', "%{$search}%")
                  ->orWhere('barang.barcode', 'LIKE', "%{$search}%");
            });
            
            Log::info('Search filter applied', ['search' => $search]);
        }
        
        // ✅ FILTER 2: KATEGORI
        if ($request->filled('kategori')) {
            $query->where('barang.kategori', $request->kategori);
            Log::info('Kategori filter applied', ['kategori' => $request->kategori]);
        }

        // ✅ FILTER 3: STOK
        if ($request->filled('stok_filter')) {
            $filterValue = $request->stok_filter;
            
            if ($filterValue === 'rendah') {
                $query->whereRaw('barang.stok <= barang.stok_minimal');
            } elseif (is_numeric($filterValue) && $filterValue > 0) {
                $query->where('barang.stok', '<', (float) $filterValue);
            }
            
            Log::info('Stok filter applied', ['stok_filter' => $filterValue]);
        }

        // === 🔍 DEBUG: Final SQL Query ===
        $finalSql = $query->toSql();
        $finalBindings = $query->getBindings();
        
        Log::info('Final Query Before Execute:', [
            'sql' => $finalSql,
            'bindings' => $finalBindings,
            'readable_query' => vsprintf(
                str_replace('?', '%s', $finalSql), 
                collect($finalBindings)->map(fn($b) => is_numeric($b) ? $b : "'{$b}'")->toArray()
            )
        ]);
        
        // ✅ EXECUTE QUERY
        $barang = $query->orderBy('barang.nama_barang', 'asc')->paginate(20);

        Log::info('Query Results:', [
            'total' => $barang->total(),
            'count' => $barang->count(),
            'per_page' => $barang->perPage(),
            'current_page' => $barang->currentPage()
        ]);

        if ($barang->count() > 0) {
            Log::info('Sample Data (first 3):', 
                $barang->take(3)->map(fn($b) => [
                    'id' => $b->id,
                    'kode' => $b->kode_barang,
                    'nama' => $b->nama_barang,
                    'cabang_id' => $b->cabang_id
                ])->toArray()
            );
        } else {
            Log::warning('⚠️ NO DATA FOUND!');
            
            // Double check langsung ke database
            $rawCount = DB::table('barang')
                ->where('cabang_id', $cabangId)
                ->count();
                
            Log::info('Direct DB Check:', [
                'cabang_id' => $cabangId,
                'raw_count' => $rawCount
            ]);
        }

        Log::info('=== BARANG INDEX DEBUG END ===');
        
        // ✅ Kategori List (FRESH dari DB)
        $kategoriList = DB::table('barang')
            ->select('kategori')
            ->where('cabang_id', $cabangId)
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');
        
        $stokFilterOptions = [
            10 => '< 10 Unit',
            20 => '< 20 Unit',
            50 => '< 50 Unit',
        ];
        
        return view('pages.barang.index', compact('barang', 'kategoriList', 'stokFilterOptions'));
    }

    public function getSatuan($id)
{
    try {
        $cabangId = $this->getActiveCabangId();
        
        Log::info('getSatuan called', [
            'barang_id' => $id,
            'cabang_id' => $cabangId
        ]);
        
        $barang = Barang::with('satuanKonversi')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->findOrFail($id);
        
        $konversi = $barang->satuanKonversi->map(function($konv) {
            return [
                'id' => $konv->id,
                'nama_satuan' => $konv->nama_satuan,
                'jumlah_konversi' => $konv->jumlah_konversi,
                'harga_jual' => $konv->harga_jual,
                'is_default' => (bool) $konv->is_default
            ];
        });

        $response = [
            'satuan_dasar' => $barang->satuan_terkecil,
            'harga_beli' => $barang->harga_beli,
            'harga_jual' => $barang->harga_jual,
            'konversi' => $konversi
        ];
        
        Log::info('getSatuan response', $response);
        
        return response()->json($response);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Barang not found', ['barang_id' => $id]);
        
        return response()->json([
            'error' => 'Barang tidak ditemukan'
        ], 404);
        
    } catch (\Exception $e) {
        Log::error('Get Satuan Error:', [
            'barang_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Gagal mengambil data satuan',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function create()
    {
        return view('pages.barang.create');
    }

    public function store(Request $request)
    {
        Log::info('=== STORE BARANG START ===');
        
        $cabangId = auth()->user()->isSuperAdmin() 
            ? $request->cabang_id 
            : auth()->user()->cabang_id;
        
        if (!$cabangId) {
            return back()->with('error', 'Error: Cabang tidak valid!')->withInput();
        }
        
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
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['cabang_id'] = 'required|exists:cabang,id';
        }

        $request->validate($rules);

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
                'deskripsi' => $request->deskripsi,
                'cabang_id' => $cabangId
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
            
            // Clear cache
            Cache::forget('barang_list_' . $cabangId);
            
            $cabangName = Cabang::find($cabangId)->nama_cabang ?? 'cabang';
            return redirect()->route('barang.index')
                ->with('success', "Barang berhasil ditambahkan ke {$cabangName}!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Barang Error:', [
                'error' => $e->getMessage(),
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
            
            // Clear cache
            Cache::forget('barang_list_' . $cabangId);
            
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
            
            // Clear cache
            Cache::forget('barang_list_' . $cabangId);
            
            return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Methods lainnya tetap sama seperti sebelumnya
    public function stokMinimal()
    {
        $cabangId = $this->getActiveCabangId();
        
        $barang = Barang::whereRaw('stok <= stok_minimal')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('stok', 'asc')
            ->get();
            
        return view('pages.barang.stok-minimal', compact('barang'));
    }

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

    public function cariBarang(Request $request)
    {
        $keyword = $request->get('q', '');
        
        if (strlen($keyword) < 1) {
            return response()->json([]);
        }

        $cabangId = $this->getActiveCabangId();

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

    public function exportExcel()
    {
        $cabangId = $this->getActiveCabangId();
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BarangExport($cabangId),
            'data_barang_' . date('Y-m-d') . '.xlsx'
        );
    }
    public function importForm()
    {
        return view('pages.barang.import');
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $cabangId = $this->getActiveCabangId();
            
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\BarangImport($cabangId), 
                $request->file('file')
            );
            
            // Clear cache
            Cache::forget('barang_list_' . $cabangId);
            
            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil diimport!');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}