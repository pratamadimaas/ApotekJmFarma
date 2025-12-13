<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BarangTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    /**
     * Return collection data untuk template (dengan contoh)
     */
    public function collection()
    {
        return collect([
            [
                'kode_barang'     => 'BRG001',
                'barcode'         => '8992772311212', // ✅ String, bukan scientific
                'nama_barang'     => 'Paracetamol 500mg',
                'kategori'        => 'Obat',
                'satuan_terkecil' => 'Strip',
                'harga_beli'      => 5000,
                'harga_jual'      => 7000,
                'stok'            => 100,
                'stok_minimal'    => 10,
                'lokasi_rak'      => 'Rak A1',
                'deskripsi'       => 'Obat demam dan nyeri',
            ],
            [
                'kode_barang'     => 'BRG002',
                'barcode'         => '8992772311213',
                'nama_barang'     => 'Amoxicillin 500mg',
                'kategori'        => 'Obat',
                'satuan_terkecil' => 'Strip',
                'harga_beli'      => 8000,
                'harga_jual'      => 12000,
                'stok'            => 50,
                'stok_minimal'    => 10,
                'lokasi_rak'      => 'Rak A2',
                'deskripsi'       => 'Antibiotik',
            ],
        ]);
    }

    /**
     * Heading / Header Excel
     */
    public function headings(): array
    {
        return [
            'kode_barang',
            'barcode',
            'nama_barang',
            'kategori',
            'satuan_terkecil',
            'harga_beli',
            'harga_jual',
            'stok',
            'stok_minimal',
            'lokasi_rak',
            'deskripsi',
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
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // kode_barang
            'B' => 18,  // barcode (lebih lebar untuk angka panjang)
            'C' => 30,  // nama_barang
            'D' => 15,  // kategori
            'E' => 18,  // satuan_terkecil
            'F' => 15,  // harga_beli
            'G' => 15,  // harga_jual
            'H' => 12,  // stok
            'I' => 15,  // stok_minimal
            'J' => 15,  // lokasi_rak
            'K' => 30,  // deskripsi
        ];
    }

    /**
     * Register events - PENTING untuk format barcode sebagai TEXT
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // ✅ Format kolom B (barcode) sebagai TEXT agar tidak jadi scientific notation
                $event->sheet->getDelegate()
                    ->getStyle('B:B')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                // Format angka untuk harga dan stok
                $event->sheet->getDelegate()
                    ->getStyle('F:I')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                // Border untuk header
                $event->sheet->getDelegate()
                    ->getStyle('A1:K1')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Auto-filter untuk header
                $event->sheet->getDelegate()->setAutoFilter('A1:K1');

                // Freeze first row
                $event->sheet->getDelegate()->freezePane('A2');

                // Add instruction comment di cell A1
                $event->sheet->getDelegate()->getComment('B1')->getText()->createTextRun(
                    "PENTING: Kolom barcode HARUS diisi sebagai TEXT!\n" .
                    "Jika copy-paste dari file lain, pastikan format cell adalah TEXT bukan Number."
                );
            },
        ];
    }
}