@extends('layouts.app')

@section('title', 'Daftar Barang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2>Daftar Barang</h2>
        <a href="{{ route('barang.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Barang
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            
            <form method="GET" class="row g-2 mb-4 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label visually-hidden">Cari Barang</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/kode..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="kategori" class="form-label visually-hidden">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="{{ route('barang.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-vertical-align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Lokasi Rak</th> {{-- ✅ KOLOM BARU --}}
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barang as $item)
                        <tr>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td><span class="badge bg-secondary">{{ $item->kategori }}</span></td>
                            <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $item->stok <= $item->stok_minimal ? 'bg-danger' : 'bg-success' }}">
                                    {{ $item->stok }} {{ $item->satuan_terkecil }}
                                </span>
                            </td>
                            <td>{{ $item->lokasi_rak ?? '-' }}</td> {{-- ✅ MENAMPILKAN DATA LOKASI RAK --}}
                            <td>
                                <a href="{{ route('barang.edit', $item->id) }}" class="btn btn-sm btn-warning me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('barang.destroy', $item->id) }}" method="POST" class="d-inline" 
                                        onsubmit="return confirm('Yakin ingin menghapus barang {{ $item->nama_barang }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Tidak ada data barang yang ditemukan.</td> {{-- ✅ COLSPAN DISESUAIKAN menjadi 8 --}}
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $barang->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection