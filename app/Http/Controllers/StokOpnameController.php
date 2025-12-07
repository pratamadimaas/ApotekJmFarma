<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokOpnameController extends Controller
{
    /**
     * Menampilkan daftar sesi Stok Opname (Riwayat).
     */
    public function index()
    {
        // Asumsi: Di masa depan, Anda akan memiliki Model StokOpname
        // Saat ini, kita hanya menampilkan daftar barang untuk SO baru (seperti draft sesi)
        $barang = Barang::orderBy('nama_barang')->get();
        
        return view('pages.stokopname.index', compact('barang'));
    }

    /**
     * Menampilkan form untuk memulai sesi Stok Opname baru.
     */
    public function create()
    {
        $barang = Barang::orderBy('nama_barang')->get();
        return view('pages.stokopname.create', compact('barang'));
    }
    
    /**
     * Memproses dan menyimpan hasil Stok Opname.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.stok_fisik' => 'required|integer|min:0',
            // Di sini Anda akan mencatat hasil SO, menghitung selisih, dan memperbarui stok.
        ]);

        DB::beginTransaction();
        try {
            // Logika Stok Opname:
            // 1. Buat sesi Stok Opname baru (Model StokOpname)
            // 2. Simpan detail hitungan (Model DetailStokOpname)
            // 3. Update stok di tabel barang dengan Stok Fisik (jika disetujui)

            // --- Logika Penyimpanan Stok Opname (Sederhana untuk demo) ---
            
            $log = [];
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);
                $stok_fisik = $item['stok_fisik'];
                $stok_sistem = $barang->stok;
                $selisih = $stok_fisik - $stok_sistem;

                if ($selisih != 0) {
                    $barang->update(['stok' => $stok_fisik]);
                    $log[] = "Stok {$barang->nama_barang} diupdate dari {$stok_sistem} menjadi {$stok_fisik} (Selisih: {$selisih}).";
                }
            }

            DB::commit();

            return redirect()->route('stokopname.index')->with('success', 'Stok Opname berhasil diselesaikan dan stok diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses Stok Opname: ' . $e->getMessage())->withInput();
        }
    }
}