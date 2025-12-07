@extends('layouts.app')

@section('title', 'Laporan Stok Opname')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Laporan Stok Opname</h1>
            <p class="text-muted mb-0">{{ $sesi->keterangan ?? 'Detail Sesi SO' }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
            <a href="{{ route('stokopname.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Sesi --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Informasi Sesi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Tanggal SO:</strong>
                    <p>{{ $sesi->tanggal->format('d F Y') }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Oleh:</strong>
                    <p>{{ $sesi->user->name }}</p>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong>
                    <p>
                        @if($sesi->status === 'completed')
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <span class="badge bg-warning">Draft</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                    <strong>Diselesaikan:</strong>
                    <p>{{ $sesi->completed_at ? $sesi->completed_at->format('d/m/Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Statistik --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Item</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ringkasan['total_item'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selisih Lebih</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">+{{ $ringkasan['total_selisih_plus'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-up-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Selisih Kurang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ringkasan['total_selisih_minus'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-down-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Item Expired Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ringkasan['item_expired'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Item SO --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Item Stok Opname</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%">Barang</th>
                            <th width="10%" class="text-center">Lokasi Rak</th>
                            <th width="10%" class="text-center">Stok Sistem</th>
                            <th width="10%" class="text-center">Stok Fisik</th>
                            <th width="10%" class="text-center">Selisih</th>
                            <th width="15%" class="text-center">Expired Date</th>
                            <th width="20%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->barang->nama_barang }}</strong>
                                <small class="d-block text-muted">{{ $item->barang->kode_barang }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item->barang->lokasi_rak ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $item->stok_sistem }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ $item->stok_fisik }}</strong>
                            </td>
                            <td class="text-center">
                                @if($item->selisih > 0)
                                    <span class="badge bg-success fs-6">+{{ $item->selisih }}</span>
                                @elseif($item->selisih < 0)
                                    <span class="badge bg-danger fs-6">{{ $item->selisih }}</span>
                                @else
                                    <span class="badge bg-secondary fs-6">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->expired_date)
                                    {{ $item->expired_date->format('d/m/Y') }}
                                    <br>
                                    @php
                                        $diffDays = now()->diffInDays($item->expired_date, false);
                                    @endphp
                                    @if($diffDays <= 30 && $diffDays >= 0)
                                        <small class="text-warning">({{ ceil($diffDays) }} hari lagi)</small>
                                    @elseif($diffDays < 0)
                                        <small class="text-danger">(Sudah lewat)</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $isExpiringSoon = $item->expired_date && $item->expired_date->lte(now()->addDays(30)) && $item->expired_date->gte(now());
                                    $isExpired = $item->expired_date && $item->expired_date->lt(now());
                                @endphp
                                
                                @if($isExpired)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Sudah Expired
                                    </span>
                                @elseif($isExpiringSoon)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle"></i> Segera Expired
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th class="text-center">{{ $details->sum('stok_sistem') }}</th>
                            <th class="text-center">{{ $details->sum('stok_fisik') }}</th>
                            <th class="text-center">
                                @php
                                    $totalSelisih = $details->sum('selisih');
                                @endphp
                                @if($totalSelisih > 0)
                                    <span class="badge bg-success fs-6">+{{ $totalSelisih }}</span>
                                @elseif($totalSelisih < 0)
                                    <span class="badge bg-danger fs-6">{{ $totalSelisih }}</span>
                                @else
                                    <span class="badge bg-secondary fs-6">0</span>
                                @endif
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .alert, .navbar, .sidebar, footer {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000;
    }
}
</style>
@endsection