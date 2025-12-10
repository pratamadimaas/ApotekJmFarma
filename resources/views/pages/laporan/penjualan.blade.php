@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Laporan Penjualan</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                {{-- Filter Tanggal --}}
                <div class="col-md-3">
                    <label for="tanggal_dari">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ $tanggalDari }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_sampai">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ $tanggalSampai }}">
                </div>
                
                {{-- Tombol Aksi --}}
                <div class="col-md-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-light me-4">Reset</a>
                </div>
                
                {{-- Tombol Export --}}
                <div class="col-md-auto d-flex align-items-end ms-auto">
                    {{-- Export Excel --}}
                    <a href="{{ route('laporan.export-excel', array_merge(request()->query(), ['type' => 'penjualan'])) }}" class="btn btn-success me-2" title="Export ke Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>

                    {{-- Export PDF --}}
                    <a href="{{ route('laporan.export-pdf', array_merge(request()->query(), ['type' => 'penjualan'])) }}" class="btn btn-danger" title="Export ke PDF">
                        <i class="bi bi-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <i class="bi bi-cash-stack float-end fa-2x"></i>
                    <h6>Total Penjualan</h6>
                    <h3 class="fw-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <i class="bi bi-receipt-cutoff float-end fa-2x"></i>
                    <h6>Jumlah Transaksi</h6>
                    <h3 class="fw-bold">{{ $jumlahTransaksi }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <i class="bi bi-currency-dollar float-end fa-2x"></i>
                    <h6>Rata-rata per Transaksi</h6>
                    <h3 class="fw-bold">Rp {{ number_format($jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-light">
                    <i class="bi bi-calendar-check me-1"></i> <strong>Penjualan Per Hari</strong>
                </div>
                <div class="card-body">
                    @if($perHari->isEmpty())
                        <p class="text-center text-muted">Tidak ada transaksi pada periode ini.</p>
                    @else
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah Transaksi</th>
                                    <th class="text-end">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perHari as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}</td>
                                    <td>{{ $item->jumlah_transaksi }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">TOTAL KESELURUHAN</th>
                                    <th class="text-end">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-light">
                    <i class="bi bi-star-fill me-1"></i> <strong>Top 10 Barang Terlaris</strong>
                </div>
                <div class="card-body">
                    @if($barangTerlaris->isEmpty())
                        <p class="text-center text-muted">Tidak ada barang yang terjual pada periode ini.</p>
                    @else
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Terjual</th>
                                    <th class="text-end">Total Omzet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangTerlaris as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->barang->nama_barang }}</td>
                                    <td>{{ $item->total_qty }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total_omzet, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            
            <div class="card mb-4 shadow">
                <div class="card-header bg-light">
                    <i class="bi bi-credit-card me-1"></i> <strong>Penjualan per Metode Bayar</strong>
                </div>
                <div class="card-body">
                    @if($perMetode->isEmpty())
                        <p class="text-center text-muted">Data metode pembayaran tidak tersedia.</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Metode</th>
                                    <th>Jumlah Transaksi</th>
                                    <th class="text-end">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perMetode as $item)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ strtoupper($item->metode_pembayaran) }}</span></td>
                                    <td>{{ $item->jumlah }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection