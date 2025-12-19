<?php

namespace App\Traits;

use App\Models\RiwayatStok;
use Illuminate\Support\Facades\Auth;

trait RecordsStokHistory
{
    /**
     * Catat perubahan stok ke riwayat
     */
    protected function catatRiwayatStok(
        $barangId,
        $tipeTransaksi,
        $jumlahPerubahan,
        $satuan,
        $keterangan = null,
        $nomorReferensi = null,
        $cabangId = null
    ) {
        $barang = \App\Models\Barang::find($barangId);
        
        if (!$barang) {
            \Log::error('RiwayatStok - Barang not found', ['barang_id' => $barangId]);
            return;
        }

        $stokSebelum = $barang->stok;
        $stokSesudah = $stokSebelum + $jumlahPerubahan;

        RiwayatStok::create([
            'barang_id' => $barangId,
            'user_id' => Auth::id(),
            'cabang_id' => $cabangId ?? $barang->cabang_id,
            'tanggal' => now(),
            'tipe_transaksi' => $tipeTransaksi,
            'nomor_referensi' => $nomorReferensi,
            'stok_sebelum' => $stokSebelum,
            'jumlah_perubahan' => $jumlahPerubahan,
            'stok_sesudah' => $stokSesudah,
            'satuan' => $satuan,
            'keterangan' => $keterangan
        ]);

        \Log::info('RiwayatStok - Recorded', [
            'barang_id' => $barangId,
            'tipe' => $tipeTransaksi,
            'stok_sebelum' => $stokSebelum,
            'perubahan' => $jumlahPerubahan,
            'stok_sesudah' => $stokSesudah
        ]);
    }
}