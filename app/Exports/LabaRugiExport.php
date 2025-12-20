<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LabaRugiExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithColumnWidths, 
    WithTitle,
    WithEvents
{
    protected $detailPerItem;
    protected $ringkasan;
    protected $periode;

    public function __construct($detailPerItem, $ringkasan, $periode)
    {
        $this->detailPerItem = $detailPerItem;
        $this->ringkasan = $ringkasan;
        $this->periode = $periode;
    }

    public function collection()
    {
        return $this->detailPerItem;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN LABA RUGI'],
            ['Periode: ' . $this->periode['dari'] . ' s/d ' . $this->periode['sampai']],
            ['Tanggal Cetak: ' . now()->format('d/m/Y H:i')],
            [],
            ['RINGKASAN'],
            ['Keterangan', 'Nilai'],
            ['Total Pendapatan (Penjualan)', $this->ringkasan['totalPendapatan']],
            ['(-) Total Return Barang', $this->ringkasan['totalReturn']],
            ['Pendapatan Bersih', $this->ringkasan['pendapatanBersih']],
            ['Harga Pokok Penjualan (HPP)', $this->ringkasan['hpp']],
            ['(-) HPP Return (Barang Kembali)', $this->ringkasan['hppReturn']],
            ['HPP Bersih', $this->ringkasan['hppBersih']],
            ['LABA KOTOR', $this->ringkasan['labaKotor']],
            ['Margin Laba Kotor (%)', number_format($this->ringkasan['marginLaba'], 2, ',', '.') . '%'],
            [],
            ['RINCIAN LABA KOTOR PER BARANG'],
            [
                'Nama Barang',
                'Qty Terjual',
                'Total Penjualan (A)',
                'Total Return (B)',
                'Total HPP (C)',
                'Laba Kotor (A - B - C)',
                'Margin (%)'
            ],
        ];
    }

    public function map($item): array
    {
        $marginPersen = $item->total_penjualan > 0 
            ? (($item->laba / $item->total_penjualan) * 100) 
            : 0;

        return [
            $item->nama_barang,
            $item->total_qty,
            $item->total_penjualan,
            $item->total_return ?? 0,
            $item->total_hpp,
            $item->laba,
            number_format($marginPersen, 2, ',', '.') . '%',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk judul utama (baris 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '1F4788'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Style untuk periode (baris 2)
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Style untuk tanggal cetak (baris 3)
            3 => [
                'font' => [
                    'size' => 9,
                    'italic' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Style untuk header "RINGKASAN" (baris 5)
            5 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745'],
                ],
            ],
            
            // Style untuk header tabel ringkasan (baris 6)
            6 => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9'],
                ],
            ],
            
            // Style untuk baris LABA KOTOR (baris 13)
            13 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745'],
                ],
            ],
            
            // Style untuk header "RINCIAN" (baris 16)
            16 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '007BFF'],
                ],
            ],
            
            // Style untuk header tabel detail (baris 17)
            17 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '007BFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,  // Nama Barang / Keterangan
            'B' => 15,  // Qty / Nilai
            'C' => 18,  // Total Penjualan
            'D' => 15,  // Total Return
            'E' => 15,  // Total HPP
            'F' => 18,  // Laba Kotor
            'G' => 12,  // Margin %
        ];
    }

    public function title(): string
    {
        return 'Laporan Laba Rugi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                // Merge cells untuk judul
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                $sheet->mergeCells('A5:G5');
                $sheet->mergeCells('A16:G16');
                
                // Format number untuk kolom nilai di ringkasan
                $sheet->getStyle('B7:B14')->getNumberFormat()
                    ->setFormatCode('#,##0');
                
                // Format number untuk tabel detail
                $sheet->getStyle('B18:F' . $lastRow)->getNumberFormat()
                    ->setFormatCode('#,##0');
                
                // Border untuk tabel ringkasan
                $sheet->getStyle('A6:B14')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
                
                // Border untuk tabel detail
                $sheet->getStyle('A17:G' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
                
                // Tambahkan row total di akhir tabel detail
                $totalRow = $lastRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
                $sheet->setCellValue('B' . $totalRow, '=SUM(B18:B' . $lastRow . ')');
                $sheet->setCellValue('C' . $totalRow, '=SUM(C18:C' . $lastRow . ')');
                $sheet->setCellValue('D' . $totalRow, '=SUM(D18:D' . $lastRow . ')');
                $sheet->setCellValue('E' . $totalRow, '=SUM(E18:E' . $lastRow . ')');
                $sheet->setCellValue('F' . $totalRow, '=SUM(F18:F' . $lastRow . ')');
                
                // Style untuk row total
                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F0F0'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '333333'],
                        ],
                    ],
                ]);
                
                // Format number untuk row total
                $sheet->getStyle('B' . $totalRow . ':F' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                
                // Auto-height untuk semua baris
                foreach (range(1, $totalRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}