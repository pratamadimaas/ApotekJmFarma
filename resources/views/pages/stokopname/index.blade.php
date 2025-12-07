@extends('layouts.app')

@section('title', 'Stok Opname')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Stok Opname</h1>
        <a href="{{ route('stokopname.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Mulai SO Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Sesi Stok Opname (Riwayat)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">Fitur ini akan menampilkan riwayat sesi Stok Opname yang telah dibuat.</p>
            
            {{-- Sementara, tampilkan daftar barang untuk SO Cepat --}}
            <h6 class="mt-4">Stok Saat Ini (Perlu Diverifikasi)</h6>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Stok Sistem</th>
                            <th>Harga Beli</th>
                            <th>Satuan Terkecil</th>
                            
                            {{-- ✅ KOLOM BARU: LOKASI RAK --}}
                            <th>Lokasi Rak</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barang as $item)
                        <tr>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->stok }}</td>
                            <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td>{{ $item->satuan_terkecil }}</td>
                            
                            {{-- ✅ TAMPILKAN DATA LOKASI RAK --}}
                            <td>
                                <strong>{{ $item->lokasi_rak ?? '-' }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection