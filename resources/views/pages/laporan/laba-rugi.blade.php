@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h2 class="page-title mb-1">Laporan Laba Rugi</h2>
                <p class="page-subtitle mb-0">Perhitungan laba kotor berdasarkan penjualan dan HPP.</p>
            </div>
        </div>
    </div>

    <div class="card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.labaRugi') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" 
                           value="{{ $tanggalDari }}">
                </div>
                <div class="col-md-4">
                    <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" 
                           value="{{ $tanggalSampai }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-filter me-2"></i>Tampilkan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-custom mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <i class="bi bi-calculator me-2"></i>
            <strong>Ringkasan Laba Kotor</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Total Pendapatan (Penjualan)</span>
                            <strong class="text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Harga Pokok Penjualan (HPP)</span>
                            <strong class="text-danger">Rp {{ number_format($hpp, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                            <strong>LABA KOTOR</strong>
                            <strong class="fs-5 text-primary">Rp {{ number_format($labaKotor, 0, ',', '.') }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="p-3 text-center">
                        <p class="mb-1 text-secondary">Margin Laba Kotor:</p>
                        <h1 class="text-primary">{{ number_format($marginLaba, 2, ',', '.') }} %</h1>
                        <p class="small text-muted">(Laba Kotor / Pendapatan)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted small">
            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
            Perhitungan ini tidak termasuk biaya operasional (gaji, sewa, listrik, dll.).
        </div>
    </div>

    <div class="card-custom">
        <div class="card-header">
            <i class="bi bi-list-ol me-2"></i>
            <strong>Rincian Laba Kotor per Barang</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-vertical-align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th class="text-end">Qty Terjual</th>
                            <th class="text-end">Total Penjualan (A)</th>
                            <th class="text-end">Total HPP (B)</th>
                            <th class="text-end">Laba Kotor (A - B)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detailPerItem as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->nama_barang }}</strong>
                            </td>
                            <td class="text-end">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <strong class="{{ $item->laba >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($item->laba, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Tidak ada data penjualan dalam periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th>TOTAL KESELURUHAN</th>
                            <th></th>
                            <th class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                            <th class="text-end text-danger">Rp {{ number_format($hpp, 0, ',', '.') }}</th>
                            <th class="text-end text-primary fs-6">Rp {{ number_format($labaKotor, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection