@extends('layouts.app')

@section('title', 'Riwayat Penjualan')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h2 class="page-title mb-1">Riwayat Penjualan</h2>
                <p class="page-subtitle mb-0">Daftar semua transaksi yang telah dilakukan.</p>
            </div>
        </div>
    </div>

    <div class="card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('penjualan.riwayat') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="nomor_nota" class="form-label">Nomor Nota</label>
                    <input type="text" class="form-control" id="nomor_nota" name="nomor_nota" 
                           value="{{ request('nomor_nota') }}" placeholder="Cari nomor nota...">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" 
                           value="{{ request('tanggal_dari') }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" 
                           value="{{ request('tanggal_sampai') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-custom">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>
            <strong>Data Penjualan</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-vertical-align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>No. Nota</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penjualan as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration + ($penjualan->currentPage() - 1) * $penjualan->perPage() }}</td>
                            <td>
                                <strong>{{ $p->nomor_nota }}</strong>
                            </td>
                            <td>{{ $p->tanggal_penjualan->format('d M Y H:i') }}</td>
                            <td>{{ $p->user->name ?? 'N/A' }}</td>
                            <td class="text-end">
                                Rp {{ number_format($p->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('penjualan.show', $p->id) }}" class="btn btn-sm btn-info text-white me-1" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('penjualan.print', $p->id) }}" target="_blank" class="btn btn-sm btn-success" title="Cetak Struk">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data penjualan yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top">
            {{ $penjualan->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection