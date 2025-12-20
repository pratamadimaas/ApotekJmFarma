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
    protected $cabangId;

    public function __construct($cabangId = null)
    {
        $this->cabangId = $cabangId;
    }

    /**
     * Ambil semua data barang dengan relasi satuanKonversi
     */
    public function collection()
    {
        return Barang::with('satuanKonversi')
            ->when($this->cabangId, fn($q) => $q->where('cabang_id', $this->cabangId))
            ->orderBy('nama_barang', 'asc')
            ->get();
    }

    /**
     * Mapping data per row dengan satuan konversi
     */
    public function map($barang): array
    {
        // Data barang utama
        $row = [
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

        // Tambahkan satuan konversi (maksimal 5)
        $konversiData = $barang->satuanKonversi->take(5);
        
        for ($i = 0; $i < 5; $i++) {
            if (isset($konversiData[$i])) {
                $konv = $konversiData[$i];
                $row[] = $konv->nama_satuan;
                $row[] = $konv->jumlah_konversi;
                $row[] = $konv->harga_jual;
                $row[] = $konv->is_default ? 'Ya' : 'Tidak';
            } else {
                // Kolom kosong jika tidak ada konversi
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }
        }

        return $row;
    }

    /**
     * Header kolom dengan satuan konversi
     */
    public function headings(): array
    {
        return [
            // Header Data Barang Utama
            'Kode Barang',
            'Barcode',
            'Nama Barang',
            'Kategori',
            'Satuan Dasar',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Stok Minimal',
            'Lokasi Rak',
            'Deskripsi',
            
            // Header Konversi 1
            'Konversi 1 - Nama Satuan',
            'Konversi 1 - Jumlah',
            'Konversi 1 - Harga Jual',
            'Konversi 1 - Default',
            
            // Header Konversi 2
            'Konversi 2 - Nama Satuan',
            'Konversi 2 - Jumlah',
            'Konversi 2 - Harga Jual',
            'Konversi 2 - Default',
            
            // Header Konversi 3
            'Konversi 3 - Nama Satuan',
            'Konversi 3 - Jumlah',
            'Konversi 3 - Harga Jual',
            'Konversi 3 - Default',
            
            // Header Konversi 4
            'Konversi 4 - Nama Satuan',
            'Konversi 4 - Jumlah',
            'Konversi 4 - Harga Jual',
            'Konversi 4 - Default',
            
            // Header Konversi 5
            'Konversi 5 - Nama Satuan',
            'Konversi 5 - Jumlah',
            'Konversi 5 - Harga Jual',
            'Konversi 5 - Default',
        ];
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
                    'startColor' => ['rgb' => '28A745'],
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
            // Kolom Data Barang Utama
            'A' => 15,  // Kode Barang
            'B' => 18,  // Barcode
            'C' => 30,  // Nama Barang
            'D' => 15,  // Kategori
            'E' => 15,  // Satuan Dasar
            'F' => 12,  // Harga Beli
            'G' => 12,  // Harga Jual
            'H' => 10,  // Stok
            'I' => 12,  // Stok Minimal
            'J' => 12,  // Lokasi Rak
            'K' => 25,  // Deskripsi
            
            // Kolom Konversi 1
            'L' => 18,  // Nama Satuan
            'M' => 12,  // Jumlah
            'N' => 12,  // Harga Jual
            'O' => 10,  // Default
            
            // Kolom Konversi 2
            'P' => 18,
            'Q' => 12,
            'R' => 12,
            'S' => 10,
            
            // Kolom Konversi 3
            'T' => 18,
            'U' => 12,
            'V' => 12,
            'W' => 10,
            
            // Kolom Konversi 4
            'X' => 18,
            'Y' => 12,
            'Z' => 12,
            'AA' => 10,
            
            // Kolom Konversi 5
            'AB' => 18,
            'AC' => 12,
            'AD' => 12,
            'AE' => 10,
        ];
    }
}