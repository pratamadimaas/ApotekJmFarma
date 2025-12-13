@extends('layouts.app')

@section('title', 'Riwayat Stok Opname')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-clipboard-check me-2"></i>Riwayat Stok Opname
            </h2>
            <a href="{{ route('stokopname.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Mulai Stok Opname Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Tanggal</th>
                            <th width="20%">User</th>
                            <th width="20%">Cabang</th>
                            <th width="25%">Keterangan</th>
                            <th width="10%">Status</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sesiSO as $index => $sesi)
                            <tr>
                                <td>{{ $sesiSO->firstItem() + $index }}</td>
                                <td>{{ $sesi->tanggal->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ $sesi->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $sesi->user->email }}</small>
                                </td>
                                <td>
                                    @if($sesi->cabang)
                                        <i class="bi bi-building me-1"></i>{{ $sesi->cabang->nama_cabang }}
                                    @elseif($sesi->user->cabang)
                                        <i class="bi bi-building me-1 text-muted"></i>
                                        <span class="text-muted">{{ $sesi->user->cabang->nama_cabang }} (dari user)</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $sesi->keterangan }}</td>
                                <td>
                                    @if($sesi->status === 'draft')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-pencil-square me-1"></i>Draft
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Selesai
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('stokopname.show', $sesi->id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Belum ada riwayat stok opname</p>
                                    <a href="{{ route('stokopname.create') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-circle me-1"></i> Mulai Stok Opname
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $sesiSO->firstItem() ?? 0 }} - {{ $sesiSO->lastItem() ?? 0 }} 
                    dari {{ $sesiSO->total() }} sesi
                </div>
                <div>
                    {{ $sesiSO->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection