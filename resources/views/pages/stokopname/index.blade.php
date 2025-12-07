@extends('layouts.app')

@section('title', 'Riwayat Stok Opname')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Riwayat Stok Opname</h1>
        <a href="{{ route('stokopname.create') }}" class="btn btn-success">
            <i class="bi bi-upc-scan me-1"></i> Mulai SO Baru (Scan Barcode)
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Sesi Stok Opname</h6>
        </div>
        <div class="card-body">
            @if($sesiSO->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada riwayat Stok Opname.</p>
                    <a href="{{ route('stokopname.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Mulai SO Pertama
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th width="25%">Keterangan</th>
                                <th width="15%">Oleh</th>
                                <th width="12%" class="text-center">Total Item</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="18%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sesiSO as $index => $sesi)
                            <tr>
                                <td>{{ $sesiSO->firstItem() + $index }}</td>
                                <td>
                                    {{ $sesi->tanggal->format('d/m/Y') }}
                                    @if($sesi->completed_at)
                                        <br><small class="text-muted">{{ $sesi->completed_at->format('H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $sesi->keterangan }}</strong>
                                </td>
                                <td>
                                    <i class="bi bi-person-circle me-1"></i>
                                    {{ $sesi->user->name }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">{{ $sesi->details->count() }} item</span>
                                </td>
                                <td class="text-center">
                                    @if($sesi->status === 'completed')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sesi->status === 'completed')
                                        <a href="{{ route('stokopname.show', $sesi->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    @else
                                        <a href="{{ route('stokopname.create') }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Lanjutkan">
                                            <i class="bi bi-play-circle"></i> Lanjutkan
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $sesiSO->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Info Panel --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <h5 class="text-primary mb-3">
                        <i class="bi bi-info-circle me-2"></i>Cara Menggunakan Stok Opname
                    </h5>
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Mulai Sesi Baru:</strong> Klik tombol "Mulai SO Baru" untuk memulai sesi stok opname.
                        </li>
                        <li class="mb-2">
                            <strong>Scan Barcode:</strong> Gunakan scanner barcode atau ketik kode barang secara manual. Sistem akan otomatis menambahkan barang ke daftar.
                        </li>
                        <li class="mb-2">
                            <strong>Input Stok Fisik:</strong> Masukkan jumlah stok fisik yang sebenarnya ada di gudang.
                        </li>
                        <li class="mb-2">
                            <strong>Catat Expired Date:</strong> Jika ada, masukkan tanggal kadaluarsa produk.
                        </li>
                        <li class="mb-2">
                            <strong>Selesaikan SO:</strong> Setelah semua barang tercatat, klik "Selesaikan SO" untuk memperbarui stok sistem.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection