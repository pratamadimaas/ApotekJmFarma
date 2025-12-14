@extends('layouts.app')

@section('title', 'Riwayat Pembelian')

@push('styles')
<style>
    /* ✅ FIX: Badge Status dengan warna yang jelas */
    .badge-status {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        white-space: nowrap; /* Mencegah status terpotong */
    }
    
    .badge-status.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }
    
    .badge-status.warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: #000000;
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
    }
    
    .badge-status.danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }
    
    .badge-status.primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    /* Hover effect untuk badge */
    .badge-status:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
    }
    
    /* ✅ Row hover effect */
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.002);
    }
    
    /* Style untuk ringkasan barang */
    .item-summary {
        margin: 0;
        list-style-type: none;
        padding-left: 0;
    }
    .item-summary li {
        font-size: 0.75rem; /* text-xs */
        color: #6c757d; /* text-muted/secondary */
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
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Pembelian</h5>
                    <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Pembelian
                    </a>
                </div>
                
                <div class="card-body pt-0 pb-2">

                    {{-- Form Filter dan Pencarian --}}
                    <form method="GET" action="{{ route('pembelian.index') }}" class="p-4 border-bottom">
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
                            
                            {{-- Filter Status (Ditambahkan dari PembelianController:index) --}}
                            <div class="col-md-3">
                                <label for="status" class="form-label text-sm">Status</label>
                                <select class="form-select form-select-sm" id="status" name="status">
                                    <option value="">-- Semua Status --</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    {{-- Status 'pending' disembunyikan di controller index kecuali diminta eksplisit --}}
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm me-2">Reset Filter</a>
                                <button type="submit" class="btn btn-info btn-sm">Filter & Cari</button>
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
                                        @php
                                            $statusClass = 'primary';
                                            $statusText = ucfirst($item->status);
                                            
                                            if ($item->status == 'approved') {
                                                $statusClass = 'success';
                                                $statusText = '✓ Approved';
                                            } elseif ($item->status == 'pending') {
                                                $statusClass = 'warning';
                                                $statusText = '⏳ Pending';
                                            } elseif ($item->status == 'cancelled') {
                                                $statusClass = 'danger';
                                                $statusText = '✕ Cancelled';
                                            }
                                        @endphp
                                        <span class="badge-status {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    
                                    {{-- Kolom Aksi --}}
                                    <td class="align-middle text-center text-nowrap">
                                        {{-- 1. Tombol Detail --}}
                                        <a href="{{ route('pembelian.show', $item->id) }}" 
                                            class="btn btn-sm btn-link text-info mb-0 p-1" 
                                            title="Detail"
                                            style="font-size: 1.1rem;">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        
                                        {{-- 2. Tombol Cetak Barcode (Hanya jika Approved) --}}
                                        @if($item->status === 'approved')
                                        <a href="{{ route('pembelian.cetak-barcode', $item->id) }}" 
                                            class="btn btn-sm btn-link text-primary mb-0 p-1" {{-- Ganti ke text-primary agar kontras dengan info --}}
                                            title="Cetak Barcode"
                                            style="font-size: 1.1rem;">
                                            <i class="bi bi-upc-scan"></i>
                                        </a>
                                        @endif

                                        {{-- 3. Edit dan Hapus (Asumsi: Hanya Admin dan Super Admin) --}}
                                        {{-- Gunakan pengecekan role yang lebih robust di blade, misalnya melalui helper/policy, tapi mengikuti logika user role 'admin' dari kode Anda: --}}
                                        @if(Auth::check() && Auth::user()->role === 'admin') 
                                            
                                            <a href="{{ route('pembelian.edit', $item->id) }}" 
                                                class="btn btn-sm btn-link text-warning mb-0 p-1" 
                                                title="Edit"
                                                style="font-size: 1.1rem;">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <form action="{{ route('pembelian.destroy', $item->id) }}" 
                                                    method="POST" 
                                                    class="d-inline" 
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pembelian {{ $item->nomor_pembelian }}? Stok barang akan dikurangi/dikembalikan.');">
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
                                    <td colspan="9" class="text-center text-muted py-4">Tidak ada data pembelian yang ditemukan.</td>
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