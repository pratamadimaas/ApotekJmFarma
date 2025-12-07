<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StokOpname;
use App\Models\DetailStokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokOpnameController extends Controller
{
    /**
     * Menampilkan daftar sesi Stok Opname (Riwayat).
     */
    public function index()
    {
        $sesiSO = StokOpname::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('pages.stokopname.index', compact('sesiSO'));
    }

    /**
     * Menampilkan halaman Stok Opname dengan Scan Barcode.
     */
    public function create()
    {
        // Ambil sesi SO yang sedang aktif (status: draft)
        $sesiAktif = StokOpname::where('user_id', Auth::id())
            ->where('status', 'draft')
            ->first();

        // Jika belum ada sesi aktif, buat sesi baru
        if (!$sesiAktif) {
            $sesiAktif = StokOpname::create([
                'user_id' => Auth::id(),
                'tanggal' => now(),
                'keterangan' => 'Sesi SO - ' . now()->format('d M Y H:i'),
                'status' => 'draft'
            ]);
        }

        // Ambil detail barang yang sudah di-scan di sesi ini
        $itemsScanned = DetailStokOpname::where('stok_opname_id', $sesiAktif->id)
            ->with('barang')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.stokopname.create', compact('sesiAktif', 'itemsScanned'));
    }

    /**
     * API untuk scan barcode dan tambah item ke sesi SO
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'sesi_id' => 'required|exists:stok_opname,id'
        ]);

        // Cari barang berdasarkan barcode
        $barang = Barang::where('barcode', $request->barcode)
            ->orWhere('kode_barang', $request->barcode)
            ->first();

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang dengan barcode tersebut tidak ditemukan!'
            ], 404);
        }

        // Cek apakah barang sudah ada di sesi ini
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

        // Tambahkan ke detail SO
        $detail = DetailStokOpname::create([
            'stok_opname_id' => $request->sesi_id,
            'barang_id' => $barang->id,
            'stok_sistem' => $barang->stok,
            'stok_fisik' => 0, // Default, akan diisi manual
            'selisih' => -$barang->stok,
            'expired_date' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan!',
            'detail' => $detail->load('barang')
        ]);
    }

    /**
     * Update stok fisik dan expired date untuk item tertentu
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'stok_fisik' => 'required|integer|min:0',
            'expired_date' => 'nullable|date'
        ]);

        $detail = DetailStokOpname::findOrFail($id);
        
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

        $sesi = StokOpname::findOrFail($id);

        if ($sesi->status !== 'draft') {
            return back()->with('error', 'Sesi SO ini sudah diselesaikan!');
        }

        DB::beginTransaction();
        try {
            // Update stok sistem berdasarkan stok fisik
            $details = DetailStokOpname::where('stok_opname_id', $id)->get();

            foreach ($details as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->update(['stok' => $detail->stok_fisik]);
                }
            }

            // Update status sesi
            $sesi->update([
                'status' => 'completed',
                'keterangan' => $request->keterangan ?? $sesi->keterangan,
                'completed_at' => now()
            ]);

            DB::commit();

            return redirect()->route('stokopname.show', $id)
                ->with('success', 'Stok Opname berhasil diselesaikan dan stok diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan SO: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail sesi SO
     */
    public function show($id)
    {
        $sesi = StokOpname::with('user')->findOrFail($id);
        $details = DetailStokOpname::where('stok_opname_id', $id)
            ->with('barang')
            ->get();

        // Hitung ringkasan
        $ringkasan = [
            'total_item' => $details->count(),
            'total_selisih_plus' => $details->where('selisih', '>', 0)->sum('selisih'),
            'total_selisih_minus' => $details->where('selisih', '<', 0)->sum('selisih'),
            'item_expired' => $details->whereNotNull('expired_date')
                ->where('expired_date', '<=', now()->addDays(30))
                ->count()
        ];

        return view('pages.stokopname.show', compact('sesi', 'details', 'ringkasan'));
    }
}