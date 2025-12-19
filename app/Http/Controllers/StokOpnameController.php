<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokOpname;
use App\Models\DetailStokOpname;
use App\Traits\CabangFilterTrait;
use App\Traits\RecordsStokHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StokOpnameController extends Controller
{
    use CabangFilterTrait;
    use RecordsStokHistory;

    /**
     * ✅ FIXED: Menampilkan daftar sesi Stok Opname (Riwayat) - FILTERED BY CABANG
     */
    public function index()
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();
        
        Log::info('StokOpname Index - Debug', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId
        ]);

        $query = StokOpname::with(['user', 'cabang']);

        if ($user->isSuperAdmin()) {
            // ✅ Super Admin WAJIB filter berdasarkan cabang yang dipilih
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
                Log::info('StokOpname Index - Super Admin Filter', ['cabang_id' => $cabangId]);
            } else {
                // ✅ Jika tidak ada cabang dipilih, tampilkan semua (opsional: atau redirect)
                Log::warning('StokOpname Index - Super Admin tanpa cabang dipilih, menampilkan semua');
                // Alternatif: return redirect()->route('stokopname.index')->with('error', 'Pilih cabang terlebih dahulu');
            }
        } else {
            // ✅ User biasa: filter berdasarkan cabang user
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
                Log::info('StokOpname Index - User Filter', ['cabang_id' => $cabangId]);
            } else {
                // ✅ FALLBACK: Jika user tidak punya cabang, tampilkan hanya miliknya sendiri
                $query->where('user_id', $user->id);
                Log::warning('StokOpname Index - User tanpa cabang, filter by user_id');
            }
        }

        $sesiSO = $query->orderBy('created_at', 'desc')->paginate(15);
        
        Log::info('StokOpname Index - Result Count', ['total' => $sesiSO->total()]);
        
        return view('pages.stokopname.index', compact('sesiSO'));
    }

    /**
     * Menampilkan halaman Stok Opname dengan Scan Barcode
     */
    public function create()
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        Log::info('StokOpname Create - Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId
        ]);

        // ✅ Validasi: Semua user (termasuk Super Admin) WAJIB punya cabang aktif
        if (!$cabangId) {
            $errorMessage = $user->isSuperAdmin() 
                ? 'Silakan pilih cabang terlebih dahulu sebelum melakukan stok opname.'
                : 'Anda belum terdaftar di cabang manapun. Hubungi administrator.';
                
            return redirect()->route('stokopname.index')
                ->with('error', $errorMessage);
        }

        // ✅ Cek sesi aktif berdasarkan user DAN cabang
        $sesiAktif = StokOpname::where('user_id', $user->id)
            ->where('cabang_id', $cabangId)
            ->where('status', 'draft')
            ->first();

        if (!$sesiAktif) {
            $now = now();
            // Tentukan apakah awal atau akhir bulan (jika tanggal <= 15 = awal, > 15 = akhir)
            $periode = $now->day <= 15 ? 'Awal' : 'Akhir';
            
            $sesiAktif = StokOpname::create([
                'user_id' => $user->id,
                'cabang_id' => $cabangId,
                'tanggal' => $now,
                'bulan' => $now->month,
                'tahun' => $now->year,
                'periode' => strtolower($periode), // 'awal' atau 'akhir'
                'keterangan' => "SO {$periode} Bulan - {$now->format('F Y')}",
                'status' => 'draft'
            ]);

            Log::info('StokOpname - New Session Created', [
                'sesi_id' => $sesiAktif->id,
                'user_id' => $user->id,
                'cabang_id' => $cabangId,
                'bulan' => $sesiAktif->bulan,
                'tahun' => $sesiAktif->tahun,
                'periode' => $sesiAktif->periode,
                'status' => 'draft'
            ]);
        } else {
            Log::info('StokOpname - Existing Session Found', [
                'sesi_id' => $sesiAktif->id,
                'cabang_id' => $sesiAktif->cabang_id,
                'status' => $sesiAktif->status
            ]);
        }

        $itemsScanned = DetailStokOpname::where('stok_opname_id', $sesiAktif->id)
            ->with('barang')
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('StokOpname Create - Items Scanned', [
            'count' => $itemsScanned->count(),
            'sesi_id' => $sesiAktif->id
        ]);

        return view('pages.stokopname.create', compact('sesiAktif', 'itemsScanned'));
    }

    /**
     * API untuk scan barcode
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'sesi_id' => 'required|exists:stok_opname,id'
        ]);

        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        Log::info('StokOpname Scan - Debug', [
            'barcode' => $request->barcode,
            'user_id' => $user->id,
            'user_cabang_id' => $user->cabang_id,
            'active_cabang_id' => $cabangId
        ]);

        // ✅ Cari barang berdasarkan barcode DAN cabang
        $barang = Barang::where(function($q) use ($request) {
                $q->where('barcode', $request->barcode)
                  ->orWhere('kode_barang', $request->barcode);
            })
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->first();

        if (!$barang) {
            Log::warning('StokOpname Scan - Barang Not Found', [
                'barcode' => $request->barcode,
                'cabang_filter' => $cabangId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Barang dengan barcode tersebut tidak ditemukan di cabang ini!'
            ], 404);
        }

        Log::info('StokOpname Scan - Barang Found', [
            'barang_id' => $barang->id,
            'barang_cabang_id' => $barang->cabang_id,
            'nama_barang' => $barang->nama_barang
        ]);

        // ✅ Validasi sesi SO: cek user DAN cabang
        $sesiSO = StokOpname::where('id', $request->sesi_id)
            ->where('user_id', $user->id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$sesiSO) {
            Log::error('StokOpname Scan - Invalid Session', [
                'sesi_id' => $request->sesi_id,
                'expected_cabang_id' => $cabangId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sesi SO tidak valid atau bukan milik cabang Anda!'
            ], 403);
        }

        $existing = DetailStokOpname::where('stok_opname_id', $request->sesi_id)
            ->where('barang_id', $barang->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Barang sudah ada dalam daftar scan. Silakan edit langsung di tabel.',
                'barang' => $barang
            ], 400);
        }

        $detail = DetailStokOpname::create([
            'stok_opname_id' => $request->sesi_id,
            'barang_id' => $barang->id,
            'stok_sistem' => $barang->stok,
            'stok_fisik' => 0,
            'selisih' => -$barang->stok,
            'expired_date' => null
        ]);

        Log::info('StokOpname - Item Added Successfully', [
            'detail_id' => $detail->id,
            'sesi_id' => $request->sesi_id,
            'barang_id' => $barang->id,
            'barang_cabang_id' => $barang->cabang_id,
            'sesi_cabang_id' => $sesiSO->cabang_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan!',
            'detail' => $detail->load('barang')
        ]);
    }

    /**
     * Update stok fisik dan expired date
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'stok_fisik' => 'required|integer|min:0',
            'expired_date' => 'nullable|date'
        ]);

        $detail = DetailStokOpname::findOrFail($id);
        
        // ✅ Validasi: sesi harus milik user dan cabang yang aktif
        $cabangId = $this->getActiveCabangId();
        $sesiSO = StokOpname::where('id', $detail->stok_opname_id)
            ->where('user_id', Auth::id())
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$sesiSO) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Sesi tidak sesuai dengan cabang aktif'
            ], 403);
        }

        if ($sesiSO->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi SO sudah diselesaikan, tidak bisa diubah!'
            ], 400);
        }
        
        $detail->update([
            'stok_fisik' => $request->stok_fisik,
            'selisih' => $request->stok_fisik - $detail->stok_sistem,
            'expired_date' => $request->expired_date
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate!',
            'detail' => $detail->load('barang')
        ]);
    }

    /**
     * Hapus item dari sesi SO
     */
    public function deleteItem($id)
    {
        $detail = DetailStokOpname::findOrFail($id);
        
        // ✅ Validasi: sesi harus milik user dan cabang yang aktif
        $cabangId = $this->getActiveCabangId();
        $sesiSO = StokOpname::where('id', $detail->stok_opname_id)
            ->where('user_id', Auth::id())
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$sesiSO) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Sesi tidak sesuai dengan cabang aktif'
            ], 403);
        }

        if ($sesiSO->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi SO sudah diselesaikan, tidak bisa dihapus!'
            ], 400);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus!'
        ]);
    }

    /**
     * Selesaikan sesi SO dan update stok sistem
     */
    public function finalize(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:255'
        ]);

        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();
        
        $sesi = StokOpname::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$sesi) {
            return back()->with('error', 'Unauthorized: Sesi ini bukan milik cabang Anda!');
        }

        if ($sesi->status !== 'draft') {
            return back()->with('error', 'Sesi SO ini sudah diselesaikan!');
        }

        DB::beginTransaction();
        try {
            $details = DetailStokOpname::where('stok_opname_id', $id)->get();

            if ($details->isEmpty()) {
                return back()->with('error', 'Tidak ada barang yang ditambahkan ke Stok Opname!');
            }

            $totalItemUpdated = 0;

            foreach ($details as $detail) {
                $barang = Barang::where('id', $detail->barang_id)
                    ->where('cabang_id', $cabangId)
                    ->first();
                
                if ($barang) {
                    $stokLama = $barang->stok;
                    $stokBaru = $detail->stok_fisik;
                    $selisih = $stokBaru - $stokLama;

                    // Update stok barang
                    $barang->update(['stok' => $stokBaru]);

                    // ✅ CATAT RIWAYAT STOK
                    $this->catatRiwayatStok(
                        barangId: $barang->id,
                        tipeTransaksi: 'stok_opname',
                        jumlahPerubahan: $selisih,
                        satuan: $barang->satuan_terkecil,
                        keterangan: "Stok Opname: {$sesi->keterangan}. Stok Fisik: {$stokBaru}, Stok Sistem: {$stokLama}",
                        nomorReferensi: "SO-{$id}",
                        cabangId: $cabangId
                    );

                    $totalItemUpdated++;
                }
            }

            // Update status sesi SO
            $sesi->update([
                'status' => 'completed',
                'keterangan' => $request->keterangan ?? $sesi->keterangan,
                'completed_at' => now()
            ]);

            DB::commit();

            Log::info('Stok Opname Completed', [
                'sesi_id' => $id,
                'total_items' => $totalItemUpdated,
                'user_id' => $user->id,
                'cabang_id' => $cabangId
            ]);

            return redirect()->route('stokopname.show', $id)
                ->with('success', "Stok Opname berhasil diselesaikan! {$totalItemUpdated} item diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('StokOpname Finalize Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal menyelesaikan SO: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        $sesi = StokOpname::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$sesi) {
            return back()->with('error', 'Unauthorized!');
        }

        if ($sesi->status !== 'draft') {
            return back()->with('error', 'Tidak dapat menghapus! Sesi sudah diselesaikan.');
        }

        DB::beginTransaction();
        try {
            // Hapus detail dulu
            DetailStokOpname::where('stok_opname_id', $id)->delete();
            
            // Hapus sesi
            $sesi->delete();

            DB::commit();

            return redirect()->route('stokopname.index')
                ->with('success', 'Sesi Stok Opname berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('StokOpname Delete Error', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal menghapus sesi: ' . $e->getMessage());
        }
    }

    /**
     * ✅ FIXED: Tampilkan detail sesi SO dengan filter cabang
     */
    public function show($id)
    {
        $user = Auth::user();
        $cabangId = $this->getActiveCabangId();

        // ✅ Ambil sesi dengan validasi cabang
        $sesi = StokOpname::with(['user', 'user.cabang', 'cabang'])
            ->where('id', $id)
            ->firstOrFail();

        // ✅ Validasi akses berdasarkan cabang
        if (!$user->isSuperAdmin()) {
            if ($sesi->cabang_id !== $cabangId) {
                Log::warning('StokOpname Show - Access Denied', [
                    'user_id' => $user->id,
                    'sesi_cabang_id' => $sesi->cabang_id,
                    'user_active_cabang_id' => $cabangId
                ]);
                
                abort(403, 'Anda tidak memiliki akses ke sesi Stok Opname ini (cabang tidak sesuai).');
            }
        } else {
            // ✅ Super Admin: validasi jika ada cabang dipilih
            if ($cabangId && $sesi->cabang_id !== $cabangId) {
                Log::warning('StokOpname Show - Super Admin Access Different Cabang', [
                    'user_id' => $user->id,
                    'sesi_cabang_id' => $sesi->cabang_id,
                    'selected_cabang_id' => $cabangId
                ]);
                
                // Opsional: izinkan Super Admin melihat semua cabang
                // Atau: abort(403, 'Sesi ini dari cabang berbeda dengan yang dipilih.');
            }
        }

        $details = DetailStokOpname::where('stok_opname_id', $id)
            ->with('barang')
            ->get();

        $ringkasan = [
            'total_item' => $details->count(),
            'total_selisih_plus' => $details->where('selisih', '>', 0)->sum('selisih'),
            'total_selisih_minus' => abs($details->where('selisih', '<', 0)->sum('selisih')),
            'item_expired' => $details->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(30))
                ->count(),
            'cabang_name' => $sesi->cabang ? $sesi->cabang->nama_cabang : ($sesi->user->cabang ? $sesi->user->cabang->nama_cabang : 'Tidak ada cabang')
        ];

        Log::info('StokOpname Show - Success', [
            'sesi_id' => $id,
            'cabang_id' => $sesi->cabang_id,
            'total_items' => $ringkasan['total_item']
        ]);

        return view('pages.stokopname.show', compact('sesi', 'details', 'ringkasan'));
    }
}