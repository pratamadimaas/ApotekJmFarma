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

    <div class="card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.pembelian') }}" class="row g-3 align-items-end">
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
            <div class="mt-3">
                <a href="{{ route('laporan.export-excel', ['jenis' => 'pembelian', 'tanggal_dari' => $tanggalDari, 'tanggal_sampai' => $tanggalSampai]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('laporan.export-pdf', ['jenis' => 'pembelian', 'tanggal_dari' => $tanggalDari, 'tanggal_sampai' => $tanggalSampai]) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>
    
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
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-bar-chart me-2"></i>
                    <strong>Total Pembelian Harian</strong>
                </div>
                <div class="card-body">
                    <canvas id="pembelianPerHariChart"></canvas>
                    <div class="table-responsive mt-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped">
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
                                    <td>{{ Carbon::parse($hari->tanggal)->format('d M Y') }}</td>
                                    <td class="text-end">{{ $hari->jumlah_transaksi }}</td>
                                    <td class="text-end">{{ number_format($hari->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center">Tidak ada data pembelian.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-box-seam me-2"></i>
                    <strong>10 Barang Paling Banyak Dibeli</strong>
                </div>
                <div class="card-body">
                    <canvas id="barangTerbanyakChart"></canvas>
                    <div class="table-responsive mt-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Barang</th>
                                    <th class="text-end">Qty (Unit Terkecil)</th>
                                    <th class="text-end">Total Harga Beli (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($barangTerbanyak as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}.</td>
                                    <td>{{ $item->barang->nama_barang ?? 'Barang Dihapus' }}</td>
                                    <td class="text-end">{{ number_format($item->total_qty, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">Tidak ada data barang dibeli.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-people-fill me-2"></i>
                    <strong>Pembelian Berdasarkan Supplier</strong>
                </div>
                <div class="card-body">
                    <canvas id="perSupplierChart"></canvas>
                    <div class="table-responsive mt-3" style="max-height: 250px;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th class="text-end">Jumlah Transaksi</th>
                                    <th class="text-end">Total Pembelian (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perSupplier as $supplier)
                                <tr>
                                    <td>{{ $supplier->supplier->nama_supplier ?? 'Supplier Dihapus' }}</td>
                                    <td class="text-end">{{ $supplier->jumlah }}</td>
                                    <td class="text-end">{{ number_format($supplier->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center">Tidak ada data pembelian per supplier.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Data dari PHP
    const perHariData = @json($perHari);
    const barangTerbanyakData = @json($barangTerbanyak);
    const perSupplierData = @json($perSupplier);

    // Helper untuk format rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    };

    // --- Chart 1: Pembelian Per Hari ---
    new Chart(document.getElementById('pembelianPerHariChart'), {
        type: 'bar',
        data: {
            labels: perHariData.map(row => row.tanggal),
            datasets: [{
                label: 'Total Pembelian Harian (Rp)',
                data: perHariData.map(row => row.total),
                backgroundColor: 'rgba(102, 126, 234, 0.7)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatRupiah(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += formatRupiah(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // --- Chart 2: Barang Paling Banyak Dibeli ---
    new Chart(document.getElementById('barangTerbanyakChart'), {
        type: 'doughnut',
        data: {
            labels: barangTerbanyakData.map(row => row.barang ? row.barang.nama_barang : 'Barang Dihapus'),
            datasets: [{
                label: 'Qty Dibeli',
                data: barangTerbanyakData.map(row => row.total_qty),
                backgroundColor: [
                    '#667eea', '#764ba2', '#a8e063', '#4bc0c0', '#f3a683', 
                    '#ffc048', '#eb3b5a', '#3867d6', '#45aaf2', '#2d98da'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
    
    // --- Chart 3: Pembelian Per Supplier ---
    new Chart(document.getElementById('perSupplierChart'), {
        type: 'pie',
        data: {
            labels: perSupplierData.map(row => row.supplier ? row.supplier.nama_supplier : 'Supplier Dihapus'),
            datasets: [{
                label: 'Total Pembelian',
                data: perSupplierData.map(row => row.total),
                backgroundColor: [
                    '#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0',
                    '#f3a683', '#ffc048', '#eb3b5a', '#3867d6', '#45aaf2'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += formatRupiah(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush