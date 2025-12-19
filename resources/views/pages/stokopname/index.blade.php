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

    <!-- Filter Card -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('stokopname.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Filter Periode -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Periode</label>
                        <select name="periode" class="form-select form-select-sm">
                            <option value="">Semua Periode</option>
                            <option value="awal" {{ request('periode') == 'awal' ? 'selected' : '' }}>Awal Bulan</option>
                            <option value="akhir" {{ request('periode') == 'akhir' ? 'selected' : '' }}>Akhir Bulan</option>
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Semua Bulan</option>
                            @php
                                $bulanNama = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach($bulanNama as $key => $nama)
                                <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tahun -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            @for($year = date('Y'); $year >= date('Y') - 3; $year--)
                                <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <!-- Filter Tanggal Dari -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Dari Tanggal</label>
                        <input type="date" name="tanggal_dari" class="form-control form-control-sm" 
                               value="{{ request('tanggal_dari') }}">
                    </div>

                    <!-- Filter Tanggal Sampai -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Sampai Tanggal</label>
                        <input type="date" name="tanggal_sampai" class="form-control form-control-sm" 
                               value="{{ request('tanggal_sampai') }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('stokopname.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Reset Filter
                        </a>
                        
                        @if(request()->hasAny(['periode', 'bulan', 'tahun', 'status', 'tanggal_dari', 'tanggal_sampai']))
                            <span class="badge bg-info ms-2">
                                <i class="bi bi-filter-circle me-1"></i>Filter Aktif
                            </span>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="15%">Periode SO</th>
                            <th width="15%">User</th>
                            <th width="15%">Cabang</th>
                            <th width="23%">Keterangan</th>
                            <th width="10%">Status</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sesiSO as $index => $sesi)
                            <tr>
                                <td>{{ $sesiSO->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($sesi->tanggal)->format('d/m/Y') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $bulanNama = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
                                            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                        $periode = ucfirst($sesi->periode ?? 'awal');
                                        $bulan = $bulanNama[$sesi->bulan] ?? '';
                                    @endphp
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $periode }} {{ $bulan }} {{ $sesi->tahun }}
                                    </span>
                                </td>
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
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">
                                        @if(request()->hasAny(['periode', 'bulan', 'tahun', 'status', 'tanggal_dari', 'tanggal_sampai']))
                                            Tidak ada data yang sesuai dengan filter
                                        @else
                                            Belum ada riwayat stok opname
                                        @endif
                                    </p>
                                    @if(!request()->hasAny(['periode', 'bulan', 'tahun', 'status', 'tanggal_dari', 'tanggal_sampai']))
                                        <a href="{{ route('stokopname.create') }}" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-circle me-1"></i> Mulai Stok Opname
                                        </a>
                                    @endif
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
                    {{ $sesiSO->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection