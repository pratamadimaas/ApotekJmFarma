@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h2 class="page-title mb-1">Laporan Laba Rugi</h2>
                    <p class="page-subtitle mb-0">Perhitungan laba kotor berdasarkan penjualan, return, dan HPP.</p>
                </div>
            </div>
            
            {{-- ✅ Tombol Export Excel --}}
            <div>
                <form action="{{ route('laporan.export-excel') }}" method="GET" class="d-inline">
                    <input type="hidden" name="jenis" value="laba-rugi">
                    <input type="hidden" name="tanggal_dari" value="{{ $tanggalDari }}">
                    <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampai }}">
                    
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Export Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ✅ Filter Component --}}
    @include('pages.laporan.laporan-filter', [
        'action' => route('laporan.labaRugi'),
        'tanggalDari' => $tanggalDari,
        'tanggalSampai' => $tanggalSampai,
        'showExport' => false,
        'jenisLaporan' => 'laba-rugi'
    ])

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
                            <span class="text-muted">(-) Total Return Barang</span>
                            <strong class="text-warning">Rp {{ number_format($totalReturn ?? 0, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                            <span><strong>Pendapatan Bersih</strong></span>
                            <strong class="text-success">Rp {{ number_format($pendapatanBersih ?? $totalPendapatan, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Harga Pokok Penjualan (HPP)</span>
                            <strong class="text-info">Rp {{ number_format($hpp, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">(-) HPP Return (Barang Kembali)</span>
                            <strong class="text-info">Rp {{ number_format($hppReturn ?? 0, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                            <span><strong>HPP Bersih</strong></span>
                            <strong class="text-danger">Rp {{ number_format($hppBersih ?? $hpp, 0, ',', '.') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-success text-white">
                            <strong>LABA KOTOR</strong>
                            <strong class="fs-5">Rp {{ number_format($labaKotor, 0, ',', '.') }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="p-3 text-center">
                        <p class="mb-1 text-secondary">Margin Laba Kotor:</p>
                        <h1 class="text-primary">{{ number_format($marginLaba, 2, ',', '.') }} %</h1>
                        <p class="small text-muted">(Laba Kotor / Pendapatan Bersih)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted small">
            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
            Perhitungan ini sudah memperhitungkan return barang dan tidak termasuk biaya operasional (gaji, sewa, listrik, dll.).
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
                            <th class="text-end">Total Return (B)</th>
                            <th class="text-end">Total HPP (C)</th>
                            <th class="text-end">Laba Kotor (A - B - C)</th>
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
                            <td class="text-end text-warning">Rp {{ number_format($item->total_return ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <strong class="{{ $item->laba >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($item->laba, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada data penjualan dalam periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th>TOTAL KESELURUHAN</th>
                            <th></th>
                            <th class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                            <th class="text-end text-warning">Rp {{ number_format($totalReturn ?? 0, 0, ',', '.') }}</th>
                            <th class="text-end text-danger">Rp {{ number_format($hppBersih ?? $hpp, 0, ',', '.') }}</th>
                            <th class="text-end text-success fs-6">Rp {{ number_format($labaKotor, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection