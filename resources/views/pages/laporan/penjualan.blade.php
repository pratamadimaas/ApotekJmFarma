@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <h2 class="page-title mb-1">Laporan Penjualan</h2>
                <p class="page-subtitle mb-0">Analisis penjualan dan laba kotor.</p>
            </div>
        </div>
    </div>

    @include('pages.laporan.laporan-filter', [
        'action' => route('laporan.penjualan'),
        'tanggalDari' => $tanggalDari,
        'tanggalSampai' => $tanggalSampai,
        'showExport' => true,
        'showPdfExport' => true,
        'jenisLaporan' => 'penjualan'
    ])

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card-custom p-3 bg-primary-subtle text-primary">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-stack me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Total Penjualan</div>
                        <h4 class="mb-0">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card-custom p-3 bg-warning-subtle text-warning">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up-arrow me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Total Laba Kotor</div>
                        <h4 class="mb-0">Rp {{ number_format($labaKotor, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card-custom p-3 bg-success-subtle text-success">
                <div class="d-flex align-items-center">
                    <i class="bi bi-receipt-cutoff me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Jumlah Transaksi</div>
                        <h4 class="mb-0">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card-custom p-3 bg-info-subtle text-info">
                <div class="d-flex align-items-center">
                    <i class="bi bi-currency-dollar me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Rata-rata per Transaksi</div>
                        <h4 class="mb-0">Rp {{ number_format($jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Penjualan Per Hari --}}
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-calendar-check me-2"></i>
                    <strong>Penjualan Per Hari</strong>
                    <span class="badge bg-secondary ms-2">{{ $perHari->total() }} hari</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                    <th class="text-end">Total Laba</th>
                                    <th class="text-end">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perHari as $item)
                                <tr>
                                    <td>{{ ($perHari->currentPage() - 1) * $perHari->perPage() + $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                    <td class="text-end">{{ $item->jumlah_transaksi }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $item->laba_kotor >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            Rp {{ number_format($item->laba_kotor, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada transaksi pada periode ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ Pagination --}}
                    @if($perHari->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="small text-muted">
                            Menampilkan {{ $perHari->firstItem() }} - {{ $perHari->lastItem() }} dari {{ $perHari->total() }}
                        </div>
                        {{ $perHari->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top 10 Barang Terlaris --}}
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-star-fill me-2"></i>
                    <strong>Barang Terlaris</strong>
                    <span class="badge bg-secondary ms-2">{{ $barangTerlaris->total() }} item</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Barang</th>
                                    <th class="text-end">Jumlah Terjual</th>
                                    <th class="text-end">Total Omzet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangTerlaris as $item)
                                <tr>
                                    <td>{{ ($barangTerlaris->currentPage() - 1) * $barangTerlaris->perPage() + $loop->iteration }}</td>
                                    <td>{{ $item->barang->nama_barang }}</td>
                                    <td class="text-end">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total_omzet, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada barang yang terjual pada periode ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ✅ Pagination --}}
                    @if($barangTerlaris->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="small text-muted">
                            Menampilkan {{ $barangTerlaris->firstItem() }} - {{ $barangTerlaris->lastItem() }} dari {{ $barangTerlaris->total() }}
                        </div>
                        {{ $barangTerlaris->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Penjualan per Metode Bayar --}}
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-credit-card me-2"></i>
                    <strong>Penjualan per Metode Bayar</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                    <th class="text-end">Total Nilai</th>
                                    <th class="text-end">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perMetode as $item)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ strtoupper($item->metode_pembayaran) }}</span></td>
                                    <td class="text-end">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-info">
                                            {{ $totalPenjualan > 0 ? number_format(($item->total / $totalPenjualan) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Data metode pembayaran tidak tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($perMetode->isNotEmpty())
                            <tfoot class="bg-light">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-end">{{ number_format($perMetode->sum('jumlah'), 0, ',', '.') }}</th>
                                    <th class="text-end">Rp {{ number_format($perMetode->sum('total'), 0, ',', '.') }}</th>
                                    <th class="text-end"><span class="badge bg-success">100%</span></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection