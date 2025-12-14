@extends('layouts.app')

@section('title', 'Laporan Return Barang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-arrow-return-left text-danger"></i> Laporan Return Barang
            </h1>
            {{-- ✅ Tampilkan info cabang --}}
            <p class="text-muted mb-0">
                <i class="bi bi-building"></i> Cabang: <strong>{{ $cabangName ?? 'N/A' }}</strong>
            </p>
        </div>
        <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Kasir
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('penjualan.laporan-return') }}">
                <div class="row">
                    <div class="col-md-5">
                        <label>Tanggal Dari</label>
                        <input type="date" class="form-control" name="tanggal_dari" 
                               value="{{ request('tanggal_dari', now()->subMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-5">
                        <label>Tanggal Sampai</label>
                        <input type="date" class="form-control" name="tanggal_sampai" 
                               value="{{ request('tanggal_sampai', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Item</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalItem ?? 0, 0, ',', '.') }} Item
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Jumlah Transaksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $returns->total() }} Return
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Return Barang</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Return</th>
                            <th>No. Nota</th>
                            <th>Nama Barang</th>
                            <th>Qty Return</th>
                            <th>Harga</th>
                            <th>Total Return</th>
                            <th>Kasir</th>
                            <th>Cabang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $key => $return)
                        <tr>
                            <td>{{ $returns->firstItem() + $key }}</td>
                            <td>{{ $return->return_date ? $return->return_date->format('d/m/Y H:i') : '-' }}</td>
                            <td><strong>{{ $return->penjualan->nomor_nota }}</strong></td>
                            <td>{{ $return->barang->nama_barang }}</td>
                            <td class="text-center">{{ $return->jumlah }} {{ $return->satuan }}</td>
                            <td class="text-end">Rp {{ number_format($return->harga_jual, 0, ',', '.') }}</td>
                            <td class="text-end"><strong>Rp {{ number_format($return->subtotal, 0, ',', '.') }}</strong></td>
                            <td>{{ $return->penjualan->user->name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $return->penjualan->cabang->nama_cabang ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data return
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection