<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        /* Gaya CSS minimal untuk PDF */
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Periode: {{ $tanggalDari }} sampai {{ $tanggalSampai }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Nota</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item->nomor_nota }}</td>
                <td>{{ $item->tanggal_penjualan }}</td>
                <td>{{ $item->user->name ?? 'N/A' }}</td>
                <td class="text-right">Rp {{ number_format($item->grand_total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>