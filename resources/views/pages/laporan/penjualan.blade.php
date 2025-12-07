@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Laporan Penjualan</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <label>Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" class="form-control" value="{{ $tanggalDari }}">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ $tanggalSampai }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Penjualan</h6>
                    <h3>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Jumlah Transaksi</h6>
                    <h3>{{ $jumlahTransaksi }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Rata-rata per Transaksi</h6>
                    <h3>Rp {{ number_format($jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Per Hari -->
    <div class="card mb-4">
        <div class="card-header"><strong>Penjualan Per Hari</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah Transaksi</th>
                        <th>Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perHari as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $item->jumlah_transaksi }}</td>
                        <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Barang Terlaris -->
    <div class="card">
        <div class="card-header"><strong>Top 10 Barang Terlaris</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Terjual</th>
                        <th>Total Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barangTerlaris as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->barang->nama_barang }}</td>
                        <td>{{ $item->total_qty }}</td>
                        <td>Rp {{ number_format($item->total_omzet, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection