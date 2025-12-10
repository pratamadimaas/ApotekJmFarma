<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Return data untuk template (contoh 2 baris)
     */
    public function collection()
    {
        return collect([
            [
                'BRG001',          // kode_barang
                '8992772311212',   // barcode
                'Paracetamol 500mg', // nama_barang
                'Obat',            // kategori
                'Strip',           // satuan_terkecil
                5000,              // harga_beli
                7000,              // harga_jual
                100,               // stok
                10,                // stok_minimal
                'Rak A1',          // lokasi_rak
                'Obat demam'       // deskripsi
            ],
            [
                'BRG002',
                '8992772311229',
                'Amoxicillin 500mg',
                'Obat',
                'Strip',
                8000,
                12000,
                50,
                10,
                'Rak A2',
                'Antibiotik'
            ],
        ]);
    }

    /**
     * Header kolom
     */
    public function headings(): array
    {
        return [
            'kode_barang',      // A
            'barcode',          // B
            'nama_barang',      // C
            'kategori',         // D
            'satuan_terkecil',  // E
            'harga_beli',       // F
            'harga_jual',       // G
            'stok',             // H
            'stok_minimal',     // I
            'lokasi_rak',       // J
            'deskripsi',        // K
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
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
            'A' => 15, // kode_barang
            'B' => 18, // barcode
            'C' => 30, // nama_barang
            'D' => 15, // kategori
            'E' => 15, // satuan_terkecil
            'F' => 12, // harga_beli
            'G' => 12, // harga_jual
            'H' => 10, // stok
            'I' => 12, // stok_minimal
            'J' => 12, // lokasi_rak
            'K' => 25, // deskripsi
        ];
    }
}