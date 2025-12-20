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
                // Data Barang Utama
                'kode_barang'     => 'BRG001',
                'barcode'         => '8992772311212',
                'nama_barang'     => 'Paracetamol 500mg',
                'kategori'        => 'Obat',
                'satuan_terkecil' => 'Strip',
                'harga_beli'      => 5000,
                'harga_jual'      => 7000,
                'stok'            => 100,
                'stok_minimal'    => 10,
                'lokasi_rak'      => 'Rak A1',
                'deskripsi'       => 'Obat demam dan nyeri',
                // Konversi 1: Box
                'konversi_1_nama'    => 'Box',
                'konversi_1_jumlah'  => 10,
                'konversi_1_harga'   => 65000,
                'konversi_1_default' => 'Ya',
                // Konversi 2: Karton
                'konversi_2_nama'    => 'Karton',
                'konversi_2_jumlah'  => 100,
                'konversi_2_harga'   => 600000,
                'konversi_2_default' => 'Tidak',
                // Konversi 3-5: Kosong
                'konversi_3_nama'    => '',
                'konversi_3_jumlah'  => '',
                'konversi_3_harga'   => '',
                'konversi_3_default' => '',
                'konversi_4_nama'    => '',
                'konversi_4_jumlah'  => '',
                'konversi_4_harga'   => '',
                'konversi_4_default' => '',
                'konversi_5_nama'    => '',
                'konversi_5_jumlah'  => '',
                'konversi_5_harga'   => '',
                'konversi_5_default' => '',
            ],
            [
                // Data Barang Utama
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
                // Konversi 1: Dus
                'konversi_1_nama'    => 'Dus',
                'konversi_1_jumlah'  => 12,
                'konversi_1_harga'   => 140000,
                'konversi_1_default' => 'Ya',
                // Konversi 2-5: Kosong
                'konversi_2_nama'    => '',
                'konversi_2_jumlah'  => '',
                'konversi_2_harga'   => '',
                'konversi_2_default' => '',
                'konversi_3_nama'    => '',
                'konversi_3_jumlah'  => '',
                'konversi_3_harga'   => '',
                'konversi_3_default' => '',
                'konversi_4_nama'    => '',
                'konversi_4_jumlah'  => '',
                'konversi_4_harga'   => '',
                'konversi_4_default' => '',
                'konversi_5_nama'    => '',
                'konversi_5_jumlah'  => '',
                'konversi_5_harga'   => '',
                'konversi_5_default' => '',
            ],
        ]);
    }

    /**
     * Heading / Header Excel
     */
    public function headings(): array
    {
        return [
            // Data Barang Utama
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
            // Satuan Konversi 1
            'konversi_1_nama',
            'konversi_1_jumlah',
            'konversi_1_harga',
            'konversi_1_default',
            // Satuan Konversi 2
            'konversi_2_nama',
            'konversi_2_jumlah',
            'konversi_2_harga',
            'konversi_2_default',
            // Satuan Konversi 3
            'konversi_3_nama',
            'konversi_3_jumlah',
            'konversi_3_harga',
            'konversi_3_default',
            // Satuan Konversi 4
            'konversi_4_nama',
            'konversi_4_jumlah',
            'konversi_4_harga',
            'konversi_4_default',
            // Satuan Konversi 5
            'konversi_5_nama',
            'konversi_5_jumlah',
            'konversi_5_harga',
            'konversi_5_default',
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header row (baris 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'] // Biru
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
            // Data Barang Utama
            'A' => 15,  // kode_barang
            'B' => 18,  // barcode
            'C' => 30,  // nama_barang
            'D' => 15,  // kategori
            'E' => 18,  // satuan_terkecil
            'F' => 12,  // harga_beli
            'G' => 12,  // harga_jual
            'H' => 10,  // stok
            'I' => 12,  // stok_minimal
            'J' => 15,  // lokasi_rak
            'K' => 25,  // deskripsi
            // Konversi 1 (L-O)
            'L' => 15,  // konversi_1_nama
            'M' => 12,  // konversi_1_jumlah
            'N' => 12,  // konversi_1_harga
            'O' => 12,  // konversi_1_default
            // Konversi 2 (P-S)
            'P' => 15,  // konversi_2_nama
            'Q' => 12,  // konversi_2_jumlah
            'R' => 12,  // konversi_2_harga
            'S' => 12,  // konversi_2_default
            // Konversi 3 (T-W)
            'T' => 15,  // konversi_3_nama
            'U' => 12,  // konversi_3_jumlah
            'V' => 12,  // konversi_3_harga
            'W' => 12,  // konversi_3_default
            // Konversi 4 (X-AA)
            'X' => 15,  // konversi_4_nama
            'Y' => 12,  // konversi_4_jumlah
            'Z' => 12,  // konversi_4_harga
            'AA' => 12, // konversi_4_default
            // Konversi 5 (AB-AE)
            'AB' => 15, // konversi_5_nama
            'AC' => 12, // konversi_5_jumlah
            'AD' => 12, // konversi_5_harga
            'AE' => 12, // konversi_5_default
        ];
    }

    /**
     * Register events - Format khusus untuk kolom tertentu
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ✅ Format kolom B (barcode) sebagai TEXT
                $sheet->getStyle('B:B')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                // Format angka untuk harga beli, harga jual, stok, stok minimal
                $sheet->getStyle('F:I')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                
                // Format angka untuk konversi jumlah dan harga (M, N, Q, R, U, V, Y, Z, AC, AD)
                $sheet->getStyle('M:M')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('N:N')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('Q:Q')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('R:R')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('U:U')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('V:V')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('Y:Y')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('Z:Z')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('AC:AC')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('AD:AD')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                // ✅ Warna khusus untuk header Satuan Konversi (L-AE)
                $sheet->getStyle('L1:AE1')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '70AD47'] // Hijau untuk konversi
                    ],
                ]);

                // Border untuk semua header
                $sheet->getStyle('A1:AE1')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Auto-filter untuk header
                $sheet->setAutoFilter('A1:AE1');

                // Freeze first row
                $sheet->freezePane('A2');

                // Set row height untuk header
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Add instruction comment di cell B1 (Barcode)
                $sheet->getComment('B1')->getText()->createTextRun(
                    "⚠️ PENTING: Kolom barcode HARUS format TEXT!\n" .
                    "Jika copy-paste, pastikan format cell adalah TEXT."
                );
            },
        ];
    }
}