<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\RiwayatStok;
use Illuminate\Database\Seeder;

class StokAwalSeeder extends Seeder
{
    public function run()
    {
        echo "📦 Mencatat stok awal...\n\n";
        
        $barang = Barang::all(); // Ambil semua barang, termasuk yang stok 0
        
        if ($barang->isEmpty()) {
            echo "⚠️  Tidak ada barang di database!\n";
            return;
        }
        
        $recorded = 0;
        
        foreach ($barang as $b) {
            RiwayatStok::create([
                'barang_id' => $b->id,
                'user_id' => 1, // Admin
                'cabang_id' => $b->cabang_id,
                'tanggal' => now(),
                'tipe_transaksi' => 'stok_awal',
                'nomor_referensi' => 'STOK-AWAL-' . now()->format('Ymd'),
                'stok_sebelum' => 0,
                'jumlah_perubahan' => $b->stok,
                'stok_sesudah' => $b->stok,
                'satuan' => $b->satuan_terkecil,
                'keterangan' => 'Stok awal sistem tracking - ' . now()->format('d/m/Y H:i')
            ]);
            
            echo "  ✅ {$b->nama_barang}: {$b->stok} {$b->satuan_terkecil}\n";
            $recorded++;
        }
        
        echo "\n";
        echo "========================================\n";
        echo "✅ SELESAI!\n";
        echo "📊 Total barang dicatat: {$recorded}\n";
        echo "📅 Tanggal: " . now()->format('d/m/Y H:i:s') . "\n";
        echo "========================================\n";
    }
}