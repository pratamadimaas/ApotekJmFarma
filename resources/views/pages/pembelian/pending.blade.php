@extends('layouts.app')

@section('title', 'Pembelian Pending')

@push('styles')
<style>
    .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        white-space: nowrap;
    }
    
    .badge-status.warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: #000000;
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
    }

    .badge-status:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #fff3cd !important;
        transform: scale(1.002);
    }
    
    .item-summary {
        margin: 0;
        list-style-type: none;
        padding-left: 0;
    }
    .item-summary li {
        font-size: 0.75rem;
        color: #6c757d;
        line-height: 1.4;
    }
</style>
@endpush

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-octagon me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-lg mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history text-warning me-2"></i> Pembelian Pending
                        </h5>
                        <div>
                            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                            </a>
                            <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Pembelian
                            </a>
                        </div>
                    </div>

                    {{-- Tab Navigation --}}
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" href="{{ route('pembelian.index') }}">
                                <i class="bi bi-check-circle me-1"></i> Approved & Cancelled
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" href="{{ route('pembelian.pending') }}">
                                <i class="bi bi-clock-history me-1"></i> Pending
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body pt-0 pb-2">

                    {{-- Alert Info --}}
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Info:</strong> Pembelian dengan status <strong>PENDING</strong> belum menambahkan stok barang. Klik tombol <strong>Detail</strong> lalu <strong>Approve</strong> untuk memproses pembelian.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    {{-- Form Filter dan Pencarian --}}
                    <form method="GET" action="{{ route('pembelian.pending') }}" class="p-4 border-bottom">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="tanggal_dari" class="form-label text-sm">Tanggal Dari</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="tanggal_sampai" class="form-label text-sm">Tanggal Sampai</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="no_faktur" class="form-label text-sm">No. Faktur</label>
                                <input type="text" class="form-control form-control-sm" id="no_faktur" name="no_faktur" placeholder="Cari No. Faktur" value="{{ request('no_faktur') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_id" class="form-label text-sm">Supplier</label>
                                <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                                    <option value="">-- Semua Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('pembelian.pending') }}" class="btn btn-secondary btn-sm me-2">Reset Filter</a>
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="bi bi-search me-1"></i> Filter & Cari
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive px-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">No. Faktur</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Supplier</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cabang</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Barang (Ringkasan)</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Total Bayar</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Petugas</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembelian as $item)
                                <tr>
                                    <td class="align-middle">
                                        <p class="text-xs font-weight-bold mb-0 ps-3">{{ $item->nomor_pembelian }}</p> 
                                    </td>
                                    <td class="align-middle">
                                        <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($item->tanggal_pembelian)->format('d M Y') }}</p>
                                    </td>
                                    <td class="align-middle">
                                        <p class="text-xs text-secondary mb-0">{{ $item->supplier->nama_supplier ?? 'N/A' }}</p>
                                    </td>
                                    <td class="align-middle">
                                        <p class="text-xs text-secondary mb-0">{{ $item->cabang->nama_cabang ?? 'N/A' }}</p>
                                    </td>
                                    
                                    {{-- Ringkasan Barang --}}
                                    <td class="align-middle">
                                        @if($item->detailPembelian->count())
                                            <ul class="item-summary">
                                            @foreach($item->detailPembelian->take(3) as $detail)
                                                <li>
                                                    {{ $detail->barang->nama_barang ?? 'Barang Dihapus' }} ({{ $detail->jumlah ?? $detail->qty }} {{ $detail->satuan }})
                                                </li>
                                            @endforeach
                                            @if($item->detailPembelian->count() > 3)
                                                <li class="text-xxs text-primary mt-1">
                                                    *+{{ $item->detailPembelian->count() - 3 }} item lainnya
                                                </li>
                                            @endif
                                            </ul>
                                        @else
                                            <p class="text-xxs text-muted mb-0">- Tidak Ada Item -</p>
                                        @endif
                                    </td>
                                    
                                    {{-- Total Bayar --}}
                                    <td class="align-middle text-end">
                                        <p class="text-xs font-weight-bold mb-0">Rp{{ number_format($item->grand_total, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="align-middle">
                                        <p class="text-xs text-secondary mb-0">{{ $item->user->name ?? 'N/A' }}</p>
                                    </td>
                                    
                                    {{-- Status --}}
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge-status warning">⏳ Pending</span>
                                    </td>
                                    
                                    {{-- Kolom Aksi --}}
                                    <td class="align-middle text-center text-nowrap">
                                        {{-- Tombol Detail (untuk approve) --}}
                                        <a href="{{ route('pembelian.show', $item->id) }}" 
                                            class="btn btn-sm btn-link text-success mb-0 p-1" 
                                            title="Detail & Approve"
                                            style="font-size: 1.1rem;">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        {{-- Edit dan Hapus (Hanya Admin dan Super Admin) --}}
                                        @if(Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin'))
                                            
                                            <a href="{{ route('pembelian.edit', $item->id) }}" 
                                                class="btn btn-sm btn-link text-warning mb-0 p-1" 
                                                title="Edit"
                                                style="font-size: 1.1rem;">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <form action="{{ route('pembelian.destroy', $item->id) }}" 
                                                    method="POST" 
                                                    class="d-inline" 
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pembelian {{ $item->nomor_pembelian }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-link text-danger mb-0 p-1" 
                                                        title="Hapus"
                                                        style="font-size: 1.1rem;">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                            
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0">Tidak ada pembelian pending.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="p-3 d-flex justify-content-center">
                        {{ $pembelian->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection