<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Shift #{{ $shift->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h3, .header p {
            margin: 0;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.summary td, table.summary th {
            padding: 4px;
            text-align: left;
        }
        table.transactions th, table.transactions td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        table.transactions th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>Laporan Penutupan Shift Kasir</h3>
        <p>Apotek Sehat Selalu</p>
        <p>Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
    </div>

    <div class="section-title">Informasi Shift</div>
    <table class="summary">
        <tr>
            <td width="20%">ID Shift</td>
            <td width="30%">: #{{ $shift->id }}</td>
            <td width="20%">Kasir</td>
            <td width="30%">: {{ $shift->user->name }}</td>
        </tr>
        <tr>
            <td>Waktu Buka</td>
            <td>: {{ \Carbon\Carbon::parse($shift->waktu_buka)->translatedFormat('d M Y H:i:s') }}</td>
            <td>Waktu Tutup</td>
            <td>: {{ \Carbon\Carbon::parse($shift->waktu_tutup)->translatedFormat('d M Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Durasi Shift</td>
            <td colspan="3">: {{ \Carbon\Carbon::parse($shift->waktu_buka)->diffForHumans(\Carbon\Carbon::parse($shift->waktu_tutup), true) }}</td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Keuangan</div>
    <table class="summary">
        <tr>
            <td width="30%">Modal Awal</td>
            <td width="10%">:</td>
            <td class="text-right">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Penjualan (Semua)</td>
            <td>:</td>
            <td class="text-right">Rp {{ number_format($shift->total_penjualan, 0, ',', '.') }} ({{ $shift->jumlah_transaksi }} Transaksi)</td>
        </tr>
        <tr>
            <td>Total Penjualan Tunai</td>
            <td>:</td>
            <td class="text-right">
                @php
                    // Hitung Tunai di sini karena data Penjualan hanya di-load tanpa agregasi
                    $tunai = $shift->penjualan->where('metode_pembayaran', 'tunai')->sum('total_bayar');
                    $uangSeharusnya = $shift->modal_awal + $tunai;
                @endphp
                Rp {{ number_format($tunai, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td>Uang Seharusnya di Laci (Modal + Tunai)</td>
            <td>:</td>
            <td class="text-right">Rp {{ number_format($uangSeharusnya, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #333;">Uang Fisik di Laci</td>
            <td style="border-top: 1px solid #333;">:</td>
            <td class="text-right" style="border-top: 1px solid #333;">Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Selisih (Kelebihan/Kekurangan)</strong></td>
            <td>:</td>
            <td class="text-right">
                @php
                    $selisih = $shift->selisih;
                    $prefix = $selisih >= 0 ? '+' : '';
                @endphp
                <strong>{{ $prefix }} Rp {{ number_format($selisih, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>
    
    @if ($shift->catatan)
    <div class="section-title">Catatan Penutupan</div>
    <p style="border: 1px dashed #ccc; padding: 10px; font-style: italic;">{{ $shift->catatan }}</p>
    @endif

    <div style="page-break-before: always;"></div>

    <div class="section-title">Detail Transaksi Penjualan</div>
    <table class="transactions">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Faktur</th>
                <th width="20%">Waktu</th>
                <th width="25%">Metode Bayar</th>
                <th width="35%" class="text-right">Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shift->penjualan as $key => $penjualan)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $penjualan->no_faktur }}</td>
                <td>{{ \Carbon\Carbon::parse($penjualan->created_at)->translatedFormat('d/m/Y H:i:s') }}</td>
                <td>{{ ucfirst($penjualan->metode_pembayaran) }}</td>
                <td class="text-right">Rp {{ number_format($penjualan->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada transaksi penjualan dalam shift ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Apotek.</p>
    </div>

</body>
</html>