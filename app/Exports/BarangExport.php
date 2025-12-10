<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * Ambil semua data barang
     */
    public function collection()
    {
        return Barang::orderBy('nama_barang', 'asc')->get();
    }

    /**
     * Mapping data per row
     */
    public function map($barang): array
    {
        return [
            $barang->kode_barang,
            $barang->barcode,
            $barang->nama_barang,
            $barang->kategori,
            $barang->satuan_terkecil,
            $barang->harga_beli,
            $barang->harga_jual,
            $barang->stok,
            $barang->stok_minimal,
            $barang->lokasi_rak,
            $barang->deskripsi,
        ];
    }

    /**
     * Header kolom
     */
    public function headings(): array
    {
        return [
            'Kode Barang',
            'Barcode',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Stok Minimal',
            'Lokasi Rak',
            'Deskripsi',
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
                    'startColor' => ['rgb' => '28A745'],
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
            'A' => 15,
            'B' => 18,
            'C' => 30,
            'D' => 15,
            'E' => 15,
            'F' => 12,
            'G' => 12,
            'H' => 10,
            'I' => 12,
            'J' => 12,
            'K' => 25,
        ];
    }
}