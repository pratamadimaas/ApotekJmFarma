<?php

namespace App\Exports;

// UBAH: Menggunakan DetailPenjualan sebagai Model utama untuk mapping
use App\Models\DetailPenjualan; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping
{
    // UBAH NAMA VARIABEL: Ini adalah koleksi detail penjualan
    protected $detailPenjualanData; 

    public function __construct($detailPenjualanData)
    {
        $this->detailPenjualanData = $detailPenjualanData;
    }

    public function collection()
    {
        return $this->detailPenjualanData;
    }

    public function headings(): array
    {
        return [
            // Kolom Ringkasan Nota
            'Nomor Nota',
            'Tanggal Penjualan',
            'Kasir',
            'Metode Pembayaran',
            
            // Kolom Detail Item
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'QTY',
            'Harga Satuan',
            'Diskon Item',
            'Subtotal Item',
            
            // Kolom Grand Total Nota
            'Grand Total Nota',
            'Status Return',
        ];
    }

    /**
     * @param DetailPenjualan $detail
     */
    public function map($detail): array
    {
        // Akses relasi penjualan dan barang yang sudah di-eager load
        $penjualan = $detail->penjualan; 
        $barang = $detail->barang;

        return [
            // Data Nota
            $penjualan->nomor_nota,
            $penjualan->tanggal_penjualan,
            $penjualan->user->name ?? 'N/A', 
            $penjualan->metode_pembayaran,

            // Data Detail Item
            $barang->kode_barang ?? 'N/A', 
            $barang->nama_barang ?? 'N/A',
            $detail->satuan,
            $detail->jumlah,
            $detail->harga_jual,
            $detail->diskon_item,
            $detail->subtotal,

            // Grand Total Nota
            $penjualan->grand_total,
            $detail->is_return ? 'RETURNED' : 'NORMAL',
        ];
    }
}