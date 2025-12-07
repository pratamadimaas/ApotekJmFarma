<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Barang;
use App\Models\Shift;
use App\Models\SatuanKonversi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index()
    {
        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return redirect()->route('shift.buka.form')->with('error', 'Anda harus membuka shift terlebih dahulu!');
        }

        $barang = Barang::where('stok', '>', 0)->where('aktif', 1)->get();
        
        return view('pages.penjualan.kasir', compact('shift', 'barang'));
    }

    public function cariBarang(Request $request)
    {
        $keyword = $request->get('q');
        
        $barang = Barang::where('stok', '>', 0)
                         ->where('aktif', 1)
                         ->where(function($query) use ($keyword) {
                             $query->where('nama_barang', 'LIKE', "%{$keyword}%")
                                   ->orWhere('kode_barang', 'LIKE', "%{$keyword}%");
                         })
                         ->limit(10)
                         ->get();

        return response()->json($barang);
    }

    public function getBarang($id)
{
    $barang = Barang::with('satuanKonversi')->findOrFail($id);
    
    // ✅ Satuan konversi dengan harga spesifik
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
        'nama_barang' => $barang->nama_barang,
        'harga_jual' => $barang->harga_jual,
        'stok' => $barang->stok,
        'satuan_dasar' => $barang->satuan_terkecil,
        'satuan_konversi' => $satuanKonversi,
    ]);
}

    // ✅ Store Transaksi - HANDLE MULTI SATUAN
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.harga' => 'required|numeric|min:0',
            'total_bayar' => 'required|numeric|min:0',
            'uang_dibayar' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|in:cash,debit,credit,qris',
        ]);

        $shift = Shift::where('user_id', Auth::id())
                     ->whereNull('waktu_tutup')
                     ->first();

        if (!$shift) {
            return response()->json(['error' => 'Shift belum dibuka!'], 400);
        }

        DB::beginTransaction();
        try {
            $nomorNota = Penjualan::generateNomorNota(); 

            $diskon = $request->diskon ?? 0;
            $totalPenjualan = $request->total_bayar;
            $grandTotal = $totalPenjualan - $diskon;
            $kembalian = $request->uang_dibayar - $grandTotal;

            $penjualan = Penjualan::create([
                'nomor_nota' => $nomorNota,
                'user_id' => Auth::id(),
                'shift_id' => $shift->id,
                'tanggal_penjualan' => now(),
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_penjualan' => $totalPenjualan,
                'diskon' => $diskon,
                'pajak' => 0,
                'grand_total' => $grandTotal,
                'jumlah_bayar' => $request->uang_dibayar,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $request->metode_pembayaran ?? 'cash',
                'keterangan' => $request->keterangan,
            ]);

            // ✅ Simpan Detail & Kurangi Stok
            foreach ($request->items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                // ✅ Hitung qty dalam satuan terkecil
                $qtyDasar = $item['qty'];
                
                if ($item['satuan'] !== $barang->satuan_terkecil) {
                    $konversi = SatuanKonversi::where('barang_id', $barang->id)
                                             ->where('nama_satuan', $item['satuan'])
                                             ->first();
                    if ($konversi) {
                        $qtyDasar = $item['qty'] * $konversi->jumlah_konversi;
                    }
                }

                // Cek stok
                if ($barang->stok < $qtyDasar) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak cukup! Tersedia: {$barang->stok} {$barang->satuan_terkecil}");
                }

                // Simpan detail
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_jual' => $item['harga'],
                    'subtotal' => $item['qty'] * $item['harga']
                ]);

                // Kurangi stok (dalam satuan terkecil)
                $barang->decrement('stok', $qtyDasar);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'invoice' => $nomorNota,
                'penjualan_id' => $penjualan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        $query = Penjualan::with(['user', 'detailPenjualan.barang', 'shift']); 

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('nomor_nota')) {
            $query->where('nomor_nota', 'LIKE', "%{$request->nomor_nota}%");
        }

        $penjualan = $query->orderBy('tanggal_penjualan', 'desc')->paginate(20);

        return view('pages.penjualan.riwayat', compact('penjualan'));
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['user', 'shift', 'detailPenjualan.barang'])->findOrFail($id);
        
        return view('pages.penjualan.detail', compact('penjualan'));
    }

    public function printStruk($id)
    {
        $penjualan = Penjualan::with(['detailPenjualan.barang', 'user'])->findOrFail($id);
        
        return view('pages.penjualan.struk', compact('penjualan'));
    }
}