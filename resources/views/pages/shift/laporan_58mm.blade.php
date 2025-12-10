<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Shift #{{ $shift->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            width: 58mm;
            margin: 0 auto;
            padding: 5mm;
            line-height: 1.4;
        }
        
        .center {
            text-align: center;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        
        .header h3 {
            font-size: 11pt;
            margin: 3px 0;
        }
        
        .header p {
            font-size: 8pt;
            margin: 2px 0;
        }
        
        .section {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px dashed #000;
        }
        
        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .row-label {
            flex: 1;
        }
        
        .row-value {
            text-align: right;
            font-weight: bold;
        }
        
        .divider {
            border-bottom: 1px solid #000;
            margin: 5px 0;
        }
        
        .divider-dashed {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        
        .total-row {
            font-size: 10pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .selisih-box {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #000;
            font-size: 11pt;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8pt;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        
        table td {
            padding: 2px 0;
        }
        
        .small-text {
            font-size: 8pt;
        }
        
        @media print {
            body {
                padding: 0;
            }
            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h3>APOTEK JM Farma</h3>
        <p>LAPORAN TUTUP SHIFT KASIR</p>
        <p class="small-text">{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Info Shift -->
    <div class="section">
        <div class="center bold">INFORMASI SHIFT</div>
        <table>
            <tr>
                <td>Shift ID</td>
                <td>:</td>
                <td class="bold">#{{ $shift->id }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td>{{ $shift->user->name }}</td>
            </tr>
            <tr>
                <td>Buka</td>
                <td>:</td>
                <td>{{ $shift->waktu_buka->format('d/m/y H:i') }}</td>
            </tr>
            <tr>
                <td>Tutup</td>
                <td>:</td>
                <td>{{ $shift->waktu_tutup->format('d/m/y H:i') }}</td>
            </tr>
            <tr>
                <td>Durasi</td>
                <td>:</td>
                <td>{{ $shift->waktu_buka->diffForHumans($shift->waktu_tutup, true) }}</td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Penjualan -->
    <div class="section">
        <div class="center bold">RINGKASAN PENJUALAN</div>
        @php
            $tunai = $shift->penjualan->where('metode_pembayaran', 'cash')->sum('grand_total');
            $debit = $shift->penjualan->where('metode_pembayaran', 'debit')->sum('grand_total');
            $credit = $shift->penjualan->where('metode_pembayaran', 'credit')->sum('grand_total');
            $qris = $shift->penjualan->where('metode_pembayaran', 'qris')->sum('grand_total');
            $nonTunai = $debit + $credit + $qris;
        @endphp
        
        <table>
            <tr>
                <td>Jumlah Transaksi</td>
                <td>:</td>
                <td class="bold">{{ $shift->jumlah_transaksi }} trx</td>
            </tr>
        </table>
        
        <div class="divider-dashed"></div>
        
        <div class="small-text">Rincian per Metode:</div>
        <table class="small-text">
            <tr>
                <td>- Tunai (Cash)</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($tunai, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>- Debit</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($debit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>- Kredit</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($credit, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>- QRIS</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($qris, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        <div class="row total-row">
            <span>TOTAL PENJUALAN</span>
            <span>Rp {{ number_format($shift->total_penjualan, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Perhitungan Laci -->
    <div class="section">
        <div class="center bold">PERHITUNGAN LACI KASIR</div>
        
        <table>
            <tr>
                <td>Modal Awal</td>
                <td>:</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Penjualan Tunai</td>
                <td>:</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($tunai, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        @php
            $uangSeharusnya = $shift->modal_awal + $tunai;
        @endphp
        
        <div class="row total-row">
            <span>Uang Seharusnya</span>
            <span>Rp {{ number_format($uangSeharusnya, 0, ',', '.') }}</span>
        </div>
        
        <table>
            <tr>
                <td>Uang Fisik di Laci</td>
                <td>:</td>
                <td class="bold" style="text-align: right;">Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Selisih -->
    <div class="selisih-box">
        @php
            $selisih = $shift->selisih;
            if ($selisih == 0) {
                $status = 'SEIMBANG';
            } elseif ($selisih > 0) {
                $status = 'KELEBIHAN';
            } else {
                $status = 'KEKURANGAN';
            }
        @endphp
        
        <div>{{ $status }}</div>
        <div style="font-size: 14pt; margin: 5px 0;">
            {{ $selisih >= 0 ? '+' : '' }} Rp {{ number_format(abs($selisih), 0, ',', '.') }}
        </div>
    </div>

    <!-- Catatan -->
    @if ($shift->catatan)
    <div class="section">
        <div class="center bold">CATATAN PENGELUARAN</div>
        <div class="small-text" style="white-space: pre-line; padding: 5px 0;">{{ $shift->catatan }}</div>
    </div>
    @endif

    <!-- Detail Transaksi -->
    <div class="section">
        <div class="center bold">DETAIL TRANSAKSI</div>
        <div class="small-text">
            @foreach ($shift->penjualan as $index => $penjualan)
            <div style="margin: 8px 0; padding: 5px 0; border-bottom: 1px dotted #ccc;">
                <div class="bold">{{ $index + 1 }}. {{ $penjualan->no_faktur }}</div>
                <div>{{ $penjualan->created_at->format('d/m/y H:i') }} - {{ ucfirst($penjualan->metode_pembayaran) }}</div>
                <div class="bold" style="text-align: right;">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Terima Kasih</p>
        <p>Laporan ini dibuat otomatis oleh sistem</p>
        <p style="margin-top: 5px;">*** SIMPAN SEBAGAI BUKTI ***</p>
    </div>

</body>
</html>

<script>
    // Auto print saat halaman dimuat
    window.onload = function() {
        window.print();
    };
</script>