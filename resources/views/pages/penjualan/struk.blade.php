<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $penjualan->nomor_nota }}</title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace; /* Font monospasi untuk tampilan struk */
            font-size: 10pt;
            padding: 0;
            margin: 0;
        }
        .struk-container {
            width: 80mm; /* Lebar standar untuk thermal printer */
            margin: 0 auto;
            padding: 10px;
        }
        .header, .footer, .separator {
            text-align: center;
            margin-bottom: 5px;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .item-list table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .item-list th, .item-list td {
            padding: 2px 0;
            text-align: left;
        }
        .item-list .qty {
            text-align: center;
            width: 10%;
        }
        .item-list .price {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 3px 0;
        }
        .totals-table .label {
            width: 60%;
            text-align: left;
        }
        .totals-table .value {
            width: 40%;
            text-align: right;
            font-weight: bold;
        }
        .grand-total .value {
            font-size: 11pt;
        }

        /* Print Media Query */
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: 80mm auto; /* Atur ukuran kertas ke lebar 80mm */
                margin: 0;
            }
            .struk-container {
                padding: 5px;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="struk-container">
        <div class="header">
            <strong>NAMA APOTEK ANDA</strong>
            <br>Jl. Alamat Apotek No. 123
            <br>Telp: (021) 123456
        </div>

        <div class="separator"></div>

        <div style="margin-bottom: 5px;">
            <table style="width: 100%; font-size: 9pt;">
                <tr>
                    <td style="width: 30%;">Nota</td>
                    <td style="width: 70%;">: {{ $penjualan->nomor_nota }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: {{ $penjualan->tanggal_penjualan->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>: {{ $penjualan->user->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <div class="item-list">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">Nama Barang</th>
                        <th class="qty">Jml</th>
                        <th class="price">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan->detailPenjualan as $detail)
                    <tr>
                        <td colspan="2">{{ $detail->barang->nama_barang ?? 'Barang Dihapus' }}</td>
                        <td class="qty">{{ $detail->jumlah }} {{ $detail->satuan }}</td>
                        <td class="price">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding-left: 10px; font-size: 8pt; color: #555;">
                            @ {{ number_format($detail->harga_jual, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="separator"></div>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td class="label">Total</td>
                    <td class="value">Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Diskon</td>
                    <td class="value">Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">GRAND TOTAL</td>
                    <td class="value">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2"><div class="separator" style="margin: 5px 0;"></div></td>
                </tr>
                <tr>
                    <td class="label">BAYAR ({{ ucfirst($penjualan->metode_pembayaran) }})</td>
                    <td class="value">Rp {{ number_format($penjualan->jumlah_bayar, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">KEMBALIAN</td>
                    <td class="value">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        <div class="separator"></div>

        <div class="footer">
            <p>TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
        </div>

    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <p>Halaman ini akan otomatis menampilkan dialog cetak. Setelah selesai, Anda dapat menutup jendela ini.</p>
    </div>
</body>
</html>