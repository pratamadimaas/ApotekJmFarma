@extends('layouts.app')

@section('title', 'Daftar Barang')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800">Daftar Barang</h2>
            <div class="btn-group" role="group" aria-label="Aksi Barang">
                <a href="{{ route('barang.import-form') }}" class="btn btn-info text-white me-2" title="Import Data dari Excel">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import
                </a>
                
                <a href="{{ route('barang.export-excel', request()->query()) }}" class="btn btn-success me-2" title="Export Data ke Excel">
                    <i class="bi bi-download me-1"></i> Export
                </a>
                
                <a href="{{ route('barang.create') }}" class="btn btn-primary" title="Tambah Barang Baru">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </a>
            </div>
        </div>
        
        <div class="card-body">
            
            {{-- FORM FILTER DAN PENCARIAN --}}
            <form method="GET" class="row g-2 mb-4 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label visually-hidden">Cari Barang</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/kode/barcode..." value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="kategori" class="form-label visually-hidden">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="stok_filter" class="form-label visually-hidden">Filter Stok</label>
                    <select name="stok_filter" class="form-select">
                        <option value="">Semua Stok</option>
                        <option value="rendah" {{ request('stok_filter') == 'rendah' ? 'selected' : '' }}>Stok Rendah/Minimal</option>
                        @if(isset($stokFilterOptions))
                            @foreach($stokFilterOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('stok_filter') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- ✅ FILTER SUPPLIER BARU --}}
                <div class="col-md-3">
                    <label for="supplier_id" class="form-label visually-hidden">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Semua Supplier</option>
                        @foreach($supplierList as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->nama_supplier }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="{{ route('barang.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            {{-- TABEL DATA BARANG --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover table-vertical-align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Barcode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Supplier</th> {{-- ✅ KOLOM BARU --}}
                            <th>Lokasi Rak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barang as $item)
                        <tr>
                            <td>{{ $item->kode_barang }}</td>
                            <td>
                                @if($item->barcode)
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-upc-scan"></i> {{ $item->barcode }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->nama_barang }}</td>
                            <td><span class="badge bg-secondary">{{ $item->kategori }}</span></td>
                            <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $item->stok <= $item->stok_minimal ? 'bg-danger' : 'bg-success' }}">
                                    {{ $item->stok }} {{ $item->satuan_terkecil }}
                                </span>
                            </td>
                            {{-- ✅ KOLOM BARU: Supplier --}}
                            <td>
                                @if($item->supplier_terakhir)
                                    <div>
                                        <strong>{{ $item->supplier_terakhir }}</strong>
                                        @if($item->tanggal_pembelian_terakhir)
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> 
                                                {{ \Carbon\Carbon::parse($item->tanggal_pembelian_terakhir)->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash-circle"></i> Belum ada pembelian</span>
                                @endif
                            </td>
                            <td>{{ $item->lokasi_rak ?? '-' }}</td>
                            <td>
                                <a href="{{ route('barang.show', $item->id) }}" class="btn btn-sm btn-info me-1" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('barang.edit', $item->id) }}" class="btn btn-sm btn-warning me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('barang.destroy', $item->id) }}" method="POST" class="d-inline" 
                                        onsubmit="return confirm('Yakin ingin menghapus barang {{ $item->nama_barang }}? Tindakan ini TIDAK dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-box-seam me-2"></i> Tidak ada data barang yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION --}}
            {{ $barang->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection