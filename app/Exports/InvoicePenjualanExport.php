<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class InvoicePenjualanExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $data;
    protected $cabangName;

    public function __construct($data, $cabangName = 'N/A')
    {
        $this->data = $data;
        $this->cabangName = $cabangName;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Invoice',
            'Kasir',
            'Shift',
            'Total Penjualan',
            'Diskon',
            'Grand Total',
            'Metode Pembayaran',
            'Nomor Referensi',
            'Cabang',
        ];
    }

    public function map($penjualan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $penjualan->tanggal_penjualan->format('d/m/Y H:i'),
            $penjualan->nomor_nota,
            $penjualan->user->name,
            'Shift #' . $penjualan->shift_id,
            $penjualan->total_penjualan,
            $penjualan->diskon,
            $penjualan->grand_total,
            strtoupper($penjualan->metode_pembayaran),
            $penjualan->nomor_referensi ?? '-',
            $penjualan->cabang->nama_cabang ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Invoice ' . $this->cabangName;
    }
}