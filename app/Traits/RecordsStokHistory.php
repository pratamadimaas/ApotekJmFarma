<?php

namespace App\Traits;

use App\Models\RiwayatStok;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait untuk mencatat riwayat perubahan stok barang
 * ✅ SUDAH DISESUAIKAN DENGAN PARAMETER DI CONTROLLER
 */
trait RecordsStokHistory
{
    /**
     * 🔥 Mencatat riwayat stok (PARAMETER DISESUAIKAN)
     * 
     * @param int $barangId ID barang
     * @param string $tipeTransaksi pembelian, penjualan, retur_penjualan, stok_opname, penyesuaian
     * @param float $jumlahPerubahan Jumlah perubahan dalam SATUAN DASAR (sudah terkonversi)
     * @param string $satuan Satuan (biasanya satuan terkecil)
     * @param string|null $keterangan Keterangan tambahan
     * @param string|null $nomorReferensi Nomor referensi transaksi
     * @param int|null $cabangId ID cabang
     * @return RiwayatStok|null
     */
    protected function catatRiwayatStok(
        int $barangId,
        string $tipeTransaksi,
        float $jumlahPerubahan, // ✅ NAMA PARAMETER SESUAI CONTROLLER
        string $satuan,
        ?string $keterangan = null,
        ?string $nomorReferensi = null,
        ?int $cabangId = null
    ): ?RiwayatStok {
        try {
            $barang = Barang::find($barangId);
            
            if (!$barang) {
                Log::error('RecordsStokHistory - Barang tidak ditemukan', [
                    'barang_id' => $barangId
                ]);
                return null;
            }

            // Hitung stok sebelum dan sesudah
            // Stok sesudah = stok saat ini (sudah diupdate)
            // Stok sebelum = stok sesudah - perubahan
            $stokSesudah = $barang->stok;
            $stokSebelum = $stokSesudah - $jumlahPerubahan;

            // Tentukan cabang_id
            $finalCabangId = $cabangId ?? $barang->cabang_id ?? (Auth::check() ? Auth::user()->cabang_id : null);

            // Buat record riwayat
            $riwayat = RiwayatStok::create([
                'barang_id' => $barangId,
                'user_id' => Auth::id() ?? 1,
                'cabang_id' => $finalCabangId,
                'tanggal' => now(),
                'tipe_transaksi' => $tipeTransaksi,
                'nomor_referensi' => $nomorReferensi,
                'stok_sebelum' => $stokSebelum,
                'jumlah_perubahan' => $jumlahPerubahan, // Positif untuk masuk, negatif untuk keluar
                'stok_sesudah' => $stokSesudah,
                'satuan' => $satuan,
                'keterangan' => $keterangan ?? ucfirst($tipeTransaksi)
            ]);

            Log::info('✅ RecordsStokHistory - Riwayat dicatat', [
                'riwayat_id' => $riwayat->id,
                'barang' => $barang->nama_barang,
                'tipe' => $tipeTransaksi,
                'stok_sebelum' => $stokSebelum,
                'perubahan' => $jumlahPerubahan,
                'stok_sesudah' => $stokSesudah,
                'satuan' => $satuan
            ]);

            return $riwayat;

        } catch (\Exception $e) {
            Log::error('❌ RecordsStokHistory - Error', [
                'barang_id' => $barangId,
                'tipe_transaksi' => $tipeTransaksi,
                'jumlah_perubahan' => $jumlahPerubahan,
                'satuan' => $satuan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * 🔥 KONVERSI SATUAN KE SATUAN DASAR (Helper method)
     * Digunakan di controller sebelum memanggil catatRiwayatStok
     * 
     * @param float $jumlah Jumlah dalam satuan input
     * @param string $satuan Nama satuan input
     * @param Barang $barang Model Barang dengan relasi satuanKonversi
     * @return float Jumlah dalam satuan dasar
     */
    protected function convertToBaseSatuan(float $jumlah, string $satuan, Barang $barang): float
    {
        // Jika sudah satuan dasar, return langsung
        if (strtolower($satuan) === strtolower($barang->satuan_terkecil)) {
            return $jumlah;
        }

        // Cari konversi satuan (case-insensitive)
        $konversi = $barang->satuanKonversi
            ->first(fn($k) => strtolower($k->nama_satuan) === strtolower($satuan));

        if (!$konversi) {
            // Fallback: anggap 1:1 dan log warning
            Log::warning('⚠️ Satuan tidak ditemukan, menggunakan 1:1', [
                'barang_id' => $barang->id,
                'barang' => $barang->nama_barang,
                'satuan_input' => $satuan,
                'satuan_dasar' => $barang->satuan_terkecil,
                'available_satuan' => $barang->satuanKonversi->pluck('nama_satuan')->toArray()
            ]);
            return $jumlah;
        }

        // Konversi: jumlah × faktor konversi
        $result = $jumlah * $konversi->jumlah_konversi;

        Log::info('🔄 Konversi Satuan', [
            'barang' => $barang->nama_barang,
            'dari' => "{$jumlah} {$satuan}",
            'ke' => "{$result} {$barang->satuan_terkecil}",
            'faktor' => $konversi->jumlah_konversi
        ]);

        return $result;
    }

    /**
     * Mencatat riwayat dengan stok manual (untuk rebuild atau kasus khusus)
     * 
     * @param int $barangId
     * @param string $tipeTransaksi
     * @param float $stokSebelum
     * @param float $jumlahPerubahan Dalam satuan dasar (sudah terkonversi)
     * @param float $stokSesudah
     * @param string|null $keterangan
     * @param string|null $nomorReferensi
     * @param int|null $cabangId
     * @param \Carbon\Carbon|null $tanggal
     * @return RiwayatStok|null
     */
    protected function catatRiwayatStokManual(
        int $barangId,
        string $tipeTransaksi,
        float $stokSebelum,
        float $jumlahPerubahan,
        float $stokSesudah,
        ?string $keterangan = null,
        ?string $nomorReferensi = null,
        ?int $cabangId = null,
        ?\Carbon\Carbon $tanggal = null
    ): ?RiwayatStok {
        try {
            $barang = Barang::find($barangId);
            
            if (!$barang) {
                Log::error('RecordsStokHistory Manual - Barang tidak ditemukan', [
                    'barang_id' => $barangId
                ]);
                return null;
            }

            $finalCabangId = $cabangId ?? $barang->cabang_id ?? (Auth::check() ? Auth::user()->cabang_id : null);

            $riwayat = RiwayatStok::create([
                'barang_id' => $barangId,
                'user_id' => Auth::id() ?? 1,
                'cabang_id' => $finalCabangId,
                'tanggal' => $tanggal ?? now(),
                'tipe_transaksi' => $tipeTransaksi,
                'nomor_referensi' => $nomorReferensi,
                'stok_sebelum' => $stokSebelum,
                'jumlah_perubahan' => $jumlahPerubahan,
                'stok_sesudah' => $stokSesudah,
                'satuan' => $barang->satuan_terkecil,
                'keterangan' => $keterangan
            ]);

            return $riwayat;

        } catch (\Exception $e) {
            Log::error('RecordsStokHistory Manual - Error', [
                'barang_id' => $barangId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
}