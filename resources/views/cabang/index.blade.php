@extends('layouts.app')

@section('title', 'Master Cabang')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-building me-2" style="color: var(--primary-color);"></i>
                Master Cabang
            </h4>
            <p class="text-muted mb-0">Kelola data cabang apotek</p>
        </div>
        <a href="{{ route('cabang.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Cabang
        </a>
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="12%">Kode Cabang</th>
                            <th width="20%">Nama Cabang</th>
                            <th width="25%">Alamat</th>
                            <th width="12%">Telepon</th>
                            <th width="15%">Penanggung Jawab</th>
                            <th width="8%" class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cabang as $key => $item)
                        <tr>
                            <td class="text-center">{{ $cabang->firstItem() + $key }}</td>
                            <td><strong class="text-primary">{{ $item->kode_cabang }}</strong></td>
                            <td><strong>{{ $item->nama_cabang }}</strong></td>
                            <td>{{ $item->alamat ?? '-' }}</td>
                            <td>
                                @if($item->telepon)
                                <i class="bi bi-telephone me-1"></i>{{ $item->telepon }}
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $item->penanggung_jawab ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->aktif)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Nonaktif
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('cabang.edit', $item->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="hapusCabang({{ $item->id }})" 
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data cabang
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $cabang->firstItem() }} - {{ $cabang->lastItem() }} 
                    dari {{ $cabang->total() }} cabang
                </div>
                {{ $cabang->links() }}
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function hapusCabang(id) {
    if (confirm('Yakin ingin menghapus cabang ini?\n\nCabang yang memiliki user tidak dapat dihapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/cabang/${id}`;
        form.submit();
    }
}
</script>
@endpush