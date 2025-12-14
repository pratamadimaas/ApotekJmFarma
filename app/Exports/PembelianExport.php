<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PembelianExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithTitle, 
    ShouldAutoSize,
    WithColumnFormatting
{
    protected $detailPembelian;
    protected $rowNumber = 0;

    public function __construct($detailPembelian)
    {
        $this->detailPembelian = $detailPembelian;
    }

    /**
     * Return collection data
     */
    public function collection()
    {
        return $this->detailPembelian;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Faktur',
            'Supplier',
            'Kode Barang',
            'Nama Barang',
            'Qty',
            'Satuan',
            'Harga Beli',
            'Subtotal',
            'Diskon',
            'PPN',
            'Grand Total',
            'Status',
            'User',
            'Cabang',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($detail): array
    {
        $this->rowNumber++;
        
        $pembelian = $detail->pembelian;
        
        return [
            $this->rowNumber,
            \Carbon\Carbon::parse($pembelian->tanggal_pembelian)->format('d/m/Y'),
            $pembelian->nomor_pembelian ?? '-',
            $pembelian->supplier->nama_supplier ?? '-',
            $detail->barang->kode_barang ?? '-',
            $detail->barang->nama_barang ?? 'Barang Dihapus',
            (int) $detail->jumlah, // ✅ Cast ke integer
            $detail->satuan ?? '-',
            (int) $detail->harga_beli, // ✅ Cast ke integer (hilangkan format string)
            (int) $detail->subtotal, // ✅ Cast ke integer
            (int) ($pembelian->diskon ?? 0), // ✅ Cast ke integer
            (int) ($pembelian->pajak ?? 0), // ✅ Cast ke integer
            (int) $pembelian->grand_total, // ✅ Cast ke integer
            strtoupper($pembelian->status),
            $pembelian->user->name ?? '-',
            $pembelian->cabang->nama_cabang ?? 'Pusat',
        ];
    }

    /**
     * Column formatting - Format angka dengan separator ribuan
     */
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER, // Qty
            'I' => '#,##0', // Harga Beli - Angka dengan separator ribuan
            'J' => '#,##0', // Subtotal
            'K' => '#,##0', // Diskon
            'L' => '#,##0', // PPN
            'M' => '#,##0', // Grand Total
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style untuk header (row 1)
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style untuk semua data
        $lastRow = $this->rowNumber + 1;
        $sheet->getStyle("A1:P{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alignment untuk kolom nomor
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Alignment untuk kolom angka (Qty, Harga, dll)
        $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I2:M{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Set row height untuk header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Set sheet title
     */
    public function title(): string
    {
        return 'Laporan Pembelian';
    }
}