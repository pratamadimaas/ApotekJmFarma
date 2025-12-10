@extends('layouts.app')

@section('title', 'Kartu Stok Barang')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">📋 Kartu Stok Barang</h5>
                            <p class="text-sm mb-0 text-muted">Riwayat keluar-masuk stok per barang</p>
                        </div>
                        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Form Filter --}}
                    <form method="GET" action="{{ route('laporan.kartuStok') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Pilih Barang</label>
                                <select name="barang_id" class="form-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($daftarBarang as $item)
                                        <option value="{{ $item->id }}" 
                                            {{ request('barang_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control" 
                                    value="{{ $tanggalDari }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control" 
                                    value="{{ $tanggalSampai }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($barang)
                        {{-- Header Info Barang --}}
                        <div class="card mb-3 border border-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-1 fw-bold">{{ $barang->nama_barang }}</h5>
                                        <p class="mb-0">
                                            <span class="badge bg-white text-dark fw-bold">{{ $barang->kode_barang }}</span>
                                            <span class="ms-2">Kemasan: <strong>{{ $barang->satuan_terkecil }}</strong></span>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <small class="d-block opacity-75">Periode:</small>
                                        <strong>{{ \Carbon\Carbon::parse($tanggalDari)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabel Kartu Stok --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm align-middle" style="font-size: 0.9rem;">
                                <thead style="background: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                    <tr class="text-center">
                                        <th width="80">Tgl</th>
                                        <th width="200">Keterangan</th>
                                        <th width="120">Masuk</th>
                                        <th width="120">Keluar</th>
                                        <th width="100">Sisa</th>
                                        <th width="80">Paraf</th>
                                        <th width="70">ED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Stok Awal --}}
                                    <tr class="table-secondary fw-bold">
                                        <td class="text-center">-</td>
                                        <td>STOK AWAL</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">{{ number_format($stokAwal, 0, ',', '.') }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    {{-- Data Transaksi --}}
                                    @forelse($kartuStok as $item)
                                        <tr>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/y') }}
                                            </td>
                                            <td>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                    {{ $item['nomor'] }}
                                                </small>
                                                <span class="fw-semibold">{{ $item['keterangan'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($item['masuk'] !== '-')
                                                    <span class="badge bg-success">{{ $item['masuk'] }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item['keluar'] !== '-')
                                                    <span class="badge bg-danger">{{ $item['keluar'] }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ number_format($item['sisa'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">{{ $item['paraf'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($item['ed'] && $item['ed'] !== '-')
                                                    <small class="badge bg-warning text-dark">{{ $item['ed'] }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada transaksi pada periode ini
                                            </td>
                                        </tr>
                                    @endforelse

                                    {{-- Stok Akhir --}}
                                    @if($kartuStok->count() > 0)
                                        <tr class="table-secondary fw-bold">
                                            <td class="text-center">-</td>
                                            <td>STOK AKHIR</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center text-primary">{{ number_format($stokAkhir, 0, ',', '.') }}</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Summary Cards --}}
                        <div class="row mt-4 g-3">
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Total Masuk</small>
                                        <h4 class="text-success mb-0">
                                            {{ number_format($kartuStok->where('type', 'masuk')->sum('qty_dasar'), 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Total Keluar</small>
                                        <h4 class="text-danger mb-0">
                                            {{ number_format($kartuStok->where('type', 'keluar')->sum('qty_dasar'), 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Stok Akhir Periode</small>
                                        <h4 class="text-primary mb-0">{{ number_format($stokAkhir, 0, ',', '.') }}</h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Stok Real-time</small>
                                        <h4 class="text-info mb-0">{{ number_format($barang->stok, 0, ',', '.') }}</h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Export --}}
                        <div class="mt-4 text-end">
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Cetak Kartu Stok
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted fs-5">Pilih barang untuk melihat kartu stok</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    .btn, .card-header a, nav, footer, form {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 2px solid #000 !important;
        page-break-inside: avoid;
    }
    table {
        font-size: 10pt;
    }
    .badge {
        border: 1px solid #000;
    }
}
</style>
@endsection