@extends('layouts.app')

@section('title', 'Menu Laporan')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            {{-- Header Section --}}
            <div class="page-header mb-4">
                <div class="d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>
                    <div>
                        <h2 class="page-title mb-1">Pusat Laporan & Analisis</h2>
                        <p class="page-subtitle mb-0">Pilih jenis laporan yang ingin Anda lihat untuk analisis bisnis.</p>
                    </div>
                </div>
            </div>

            {{-- Cards Section --}}
            <div class="row g-4">
                
                {{-- Card Laporan Penjualan --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <a href="{{ route('laporan.penjualan') }}" class="text-decoration-none">
                        <div class="card-custom card-hover h-100 border-start border-primary border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-primary-subtle text-primary me-3">
                                        <i class="bi bi-cart-check fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary mb-2">Laporan</span>
                                        <h4 class="mb-0 fw-bold text-dark">Penjualan</h4>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Analisis omzet, transaksi penjualan, dan laba kotor dari aktivitas penjualan.</p>
                                <div class="d-flex align-items-center text-primary">
                                    <span class="small fw-semibold">Lihat Laporan</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Card Laporan Pembelian --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <a href="{{ route('laporan.pembelian') }}" class="text-decoration-none">
                        <div class="card-custom card-hover h-100 border-start border-success border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-success-subtle text-success me-3">
                                        <i class="bi bi-truck fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-success mb-2">Laporan</span>
                                        <h4 class="mb-0 fw-bold text-dark">Pembelian</h4>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Pengeluaran modal, aktivitas stok masuk, dan analisis pembelian dari supplier.</p>
                                <div class="d-flex align-items-center text-success">
                                    <span class="small fw-semibold">Lihat Laporan</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                {{-- Card Laporan Laba Rugi --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <a href="{{ route('laporan.labaRugi') }}" class="text-decoration-none">
                        <div class="card-custom card-hover h-100 border-start border-warning border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-warning-subtle text-warning me-3">
                                        <i class="bi bi-graph-up-arrow fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning mb-2">Laporan</span>
                                        <h4 class="mb-0 fw-bold text-dark">Laba Rugi</h4>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Perhitungan pendapatan vs HPP, margin laba, dan profitabilitas bisnis.</p>
                                <div class="d-flex align-items-center text-warning">
                                    <span class="small fw-semibold">Lihat Laporan</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Card Laporan Stok Barang --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <a href="{{ route('laporan.stok') }}" class="text-decoration-none">
                        <div class="card-custom card-hover h-100 border-start border-info border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-info-subtle text-info me-3">
                                        <i class="bi bi-box-seam fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-info mb-2">Laporan</span>
                                        <h4 class="mb-0 fw-bold text-dark">Stok Barang</h4>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Inventori saat ini, stok minimum, nilai aset, dan potensi laba dari stok.</p>
                                <div class="d-flex align-items-center text-info">
                                    <span class="small fw-semibold">Lihat Laporan</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Card Kartu Stok --}}
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <a href="{{ route('laporan.kartuStok') }}" class="text-decoration-none">
                        <div class="card-custom card-hover h-100 border-start border-secondary border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-circle bg-secondary-subtle text-secondary me-3">
                                        <i class="bi bi-clipboard-data fs-4"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-secondary mb-2">Laporan</span>
                                        <h4 class="mb-0 fw-bold text-dark">Kartu Stok</h4>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Riwayat keluar-masuk barang, tracking mutasi stok per item secara detail.</p>
                                <div class="d-flex align-items-center text-secondary">
                                    <span class="small fw-semibold">Lihat Laporan</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>

            {{-- Info Footer --}}
            <div class="alert alert-light border mt-4" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle text-primary fs-5 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Informasi</h6>
                        <p class="mb-0 small">Semua laporan dapat difilter berdasarkan rentang tanggal dan dapat diekspor ke format Excel atau PDF. Data yang ditampilkan akan disesuaikan dengan cabang yang sedang aktif.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Icon Circle Styling */
.icon-circle {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

/* Card Hover Effect */
.card-hover {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Badge Styling */
.badge {
    font-size: 0.7rem;
    padding: 0.35rem 0.65rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Border Colors */
.border-4 {
    border-width: 4px !important;
}
</style>
@endsection