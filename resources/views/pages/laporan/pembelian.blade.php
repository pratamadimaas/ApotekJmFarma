@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="bi bi-truck-flatbed"></i>
            </div>
            <div>
                <h2 class="page-title mb-1">Laporan Pembelian</h2>
                <p class="page-subtitle mb-0">Analisis pembelian barang dan pengeluaran modal.</p>
            </div>
        </div>
    </div>

    {{-- ✅ Filter Component --}}
    @include('pages.laporan.laporan-filter', [
        'action' => route('laporan.pembelian'),
        'tanggalDari' => $tanggalDari,
        'tanggalSampai' => $tanggalSampai,
        'showExport' => true,
        'showPdfExport' => false,
        'jenisLaporan' => 'pembelian'
    ])
    
    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card-custom p-3 bg-primary-subtle text-primary">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-coin me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Total Pengeluaran Pembelian</div>
                        <h4 class="mb-0">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom p-3 bg-info-subtle text-info">
                <div class="d-flex align-items-center">
                    <i class="bi bi-receipt me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Jumlah Transaksi (Approved)</div>
                        <h4 class="mb-0">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom p-3 bg-success-subtle text-success">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calculator me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Rata-rata per Transaksi</div>
                        <h4 class="mb-0">Rp {{ number_format($jumlahTransaksi > 0 ? $totalPembelian / $jumlahTransaksi : 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom p-3 bg-warning-subtle text-warning">
                <div class="d-flex align-items-center">
                    <i class="bi bi-box-seam me-3 fs-3"></i>
                    <div>
                        <div class="text-uppercase small">Total Item Dibeli</div>
                        <h4 class="mb-0">{{ number_format($barangTerbeli->sum('total_qty'), 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        {{-- Total Pembelian Harian --}}
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-calendar3 me-2"></i>
                    <strong>Total Pembelian Harian</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                    <th class="text-end">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perHari as $hari)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($hari->tanggal)->format('d M Y') }}</td>
                                    <td class="text-end">{{ number_format($hari->jumlah_transaksi, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($hari->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-3">Tidak ada data pembelian pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                            @if($perHari->isNotEmpty())
                            <tfoot class="bg-light">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-end">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</th>
                                    <th class="text-end">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 10 Barang Paling Banyak Dibeli --}}
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-box-seam me-2"></i>
                    <strong>10 Barang Paling Banyak Dibeli</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Barang</th>
                                    <th class="text-end">Qty (Unit Terkecil)</th>
                                    <th class="text-end">Total Harga Beli (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($barangTerbeli as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}.</td>
                                    <td>
                                        <strong>{{ $item->barang->nama_barang ?? 'Barang Dihapus' }}</strong>
                                        @if($item->barang)
                                        <br><small class="text-muted">{{ $item->barang->kode_barang }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-3">Tidak ada data barang dibeli pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                            @if($barangTerbeli->isNotEmpty())
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="2">TOTAL</th>
                                    <th class="text-end">{{ number_format($barangTerbeli->sum('total_qty'), 0, ',', '.') }}</th>
                                    <th class="text-end">Rp {{ number_format($barangTerbeli->sum('total_nilai'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Pembelian Berdasarkan Supplier --}}
        <div class="col-lg-12">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-people-fill me-2"></i>
                    <strong>Pembelian Berdasarkan Supplier</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                    <th class="text-end">Total Pembelian (Rp)</th>
                                    <th class="text-end">Rata-rata per Transaksi</th>
                                    <th class="text-end">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perSupplier as $supplier)
                                <tr>
                                    <td>{{ $loop->iteration }}.</td>
                                    <td>
                                        <strong>{{ $supplier->nama_supplier }}</strong>
                                    </td>
                                    <td class="text-end">{{ number_format($supplier->jumlah, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($supplier->total, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($supplier->jumlah > 0 ? $supplier->total / $supplier->jumlah : 0, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-info">
                                            {{ $totalPembelian > 0 ? number_format(($supplier->total / $totalPembelian) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-3">Tidak ada data pembelian per supplier pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                            @if($perSupplier->isNotEmpty())
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="2">TOTAL KESELURUHAN</th>
                                    <th class="text-end">{{ number_format($perSupplier->sum('jumlah'), 0, ',', '.') }}</th>
                                    <th class="text-end">Rp {{ number_format($perSupplier->sum('total'), 0, ',', '.') }}</th>
                                    <th class="text-end">-</th>
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