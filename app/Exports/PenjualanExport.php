<?php

namespace App\Exports;

use App\Models\DetailPenjualan; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
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
            
            // Kolom Analisis Laba
            'HPP Satuan',
            'Total HPP',
            'Laba Kotor Item',
            'Margin (%)',
            
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

        // ✅ Hitung HPP dengan mempertimbangkan satuan konversi
        $hppSatuan = $this->hitungHPPSatuan($detail, $barang);
        $totalHPP = $hppSatuan * $detail->jumlah;
        
        // ✅ Hitung Laba Kotor (Subtotal - Total HPP)
        $labaKotor = $detail->subtotal - $totalHPP;
        
        // ✅ Hitung Margin Laba (%)
        $marginPersen = $totalHPP > 0 
            ? (($detail->subtotal - $totalHPP) / $totalHPP * 100) 
            : 0;

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
            
            // Analisis Laba
            $hppSatuan,
            $totalHPP,
            $labaKotor,
            number_format($marginPersen, 2) . '%',

            // Grand Total Nota
            $penjualan->grand_total,
            $detail->is_return ? 'RETURNED' : 'NORMAL',
        ];
    }

    /**
     * ✅ Hitung HPP per satuan dengan mempertimbangkan konversi
     */
    private function hitungHPPSatuan($detail, $barang)
    {
        if (!$barang) {
            return 0;
        }

        // Jika satuan sama dengan satuan dasar
        if ($detail->satuan === $barang->satuan_terkecil) {
            return $barang->harga_beli;
        }

        // Cari konversi satuan
        $konversi = $barang->satuanKonversi()
            ->where('nama_satuan', $detail->satuan)
            ->first();

        if ($konversi) {
            // HPP Satuan = HPP Dasar × Jumlah Konversi
            // Contoh: 1 Box = 10 Strip, maka HPP Box = HPP Strip × 10
            return $barang->harga_beli * $konversi->jumlah_konversi;
        }

        // Default: gunakan HPP dasar
        return $barang->harga_beli;
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '007BFF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,  // Nomor Nota
            'B' => 15,  // Tanggal
            'C' => 20,  // Kasir
            'D' => 18,  // Metode Pembayaran
            'E' => 15,  // Kode Barang
            'F' => 30,  // Nama Barang
            'G' => 12,  // Satuan
            'H' => 10,  // QTY
            'I' => 12,  // Harga Satuan
            'J' => 12,  // Diskon Item
            'K' => 15,  // Subtotal Item
            'L' => 12,  // HPP Satuan
            'M' => 15,  // Total HPP
            'N' => 15,  // Laba Kotor
            'O' => 12,  // Margin %
            'P' => 15,  // Grand Total
            'Q' => 12,  // Status Return
        ];
    }
}