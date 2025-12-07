@extends('layouts.app')

@section('title', 'Menu Laporan')

@section('content')

<div class="container-fluid py-4">
<div class="row">
<div class="col-12">
<div class="card shadow-lg mb-4">
<div class="card-header pb-0">
<h5 class="mb-0">Pusat Laporan & Analisis</h5>
<p class="text-sm mb-0">Pilih jenis laporan yang ingin Anda lihat.</p>
</div>
<div class="card-body p-4">
<div class="row">

                    {{-- Card Laporan Penjualan --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-stats h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Laporan</p>
                                            <h5 class="font-weight-bolder">Penjualan</h5>
                                            <p class="mb-0 text-sm text-secondary">Analisis omzet & transaksi.</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                            <i class="fas fa-shopping-cart text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('laporan.penjualan') }}" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    {{-- Card Laporan Pembelian --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-stats h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Laporan</p>
                                            <h5 class="font-weight-bolder">Pembelian</h5>
                                            <p class="mb-0 text-sm text-secondary">Pengeluaran & aktivitas stok masuk.</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                            <i class="fas fa-truck-loading text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('laporan.pembelian') }}" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Card Laporan Laba Rugi --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-stats h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Laporan</p>
                                            <h5 class="font-weight-bolder">Laba Rugi</h5>
                                            <p class="mb-0 text-sm text-secondary">Hitungan pendapatan vs HPP.</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                            <i class="fas fa-chart-line text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                                {{-- Asumsi Anda memiliki rute laporan.labaRugi (sesuai controller) --}}
                                <a href="{{ route('laporan.labaRugi') }}" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    {{-- Card Laporan Stok Barang --}}
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card card-stats h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Laporan</p>
                                            <h5 class="font-weight-bolder">Stok Barang</h5>
                                            <p class="mb-0 text-sm text-secondary">Inventori, stok minimum, & nilai aset.</p>
                                        </div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                            <i class="fas fa-boxes text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('laporan.stok') }}" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


</div>
@endsection