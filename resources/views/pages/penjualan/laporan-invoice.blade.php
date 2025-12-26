@extends('layouts.app')

@section('title', 'Laporan Invoice Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-text text-primary"></i> Laporan Invoice Penjualan
            </h1>
            {{-- ✅ Tampilkan info cabang --}}
            <p class="text-muted mb-0">
                <i class="bi bi-building"></i> Cabang: <strong>{{ $cabangName ?? 'N/A' }}</strong>
            </p>
        </div>
        <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Kasir
        </a>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-x-octagon me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
            <a href="{{ route('penjualan.laporan-invoice.export-excel', request()->all()) }}" 
               class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('penjualan.laporan-invoice') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Tanggal Dari</label>
                        <input type="date" class="form-control" name="tanggal_dari" 
                               value="{{ request('tanggal_dari', $tanggalDari) }}">
                    </div>
                    <div class="col-md-4">
                        <label>Tanggal Sampai</label>
                        <input type="date" class="form-control" name="tanggal_sampai" 
                               value="{{ request('tanggal_sampai', $tanggalSampai) }}">
                    </div>
                    <div class="col-md-3">
                        <label>Metode Pembayaran</label>
                        <select class="form-control" name="metode_pembayaran">
                            <option value="">-- Semua --</option>
                            <option value="cash" {{ request('metode_pembayaran') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ request('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="qris" {{ request('metode_pembayaran') == 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="debit" {{ request('metode_pembayaran') == 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="credit" {{ request('metode_pembayaran') == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Pendapatan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Transaksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalTransaksi ?? 0, 0, ',', '.') }} Invoice
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt-cutoff fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Invoice Penjualan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Invoice</th>
                            <th>Kasir</th>
                            <th>Shift</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Cabang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualan as $key => $item)
                        <tr>
                            <td>{{ $penjualan->firstItem() + $key }}</td>
                            <td>{{ $item->tanggal_penjualan->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ $item->nomor_nota }}</strong></td>
                            <td>{{ $item->user->name }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    Shift #{{ $item->shift_id }}
                                </span>
                            </td>
                            <td class="text-end"><strong>Rp {{ number_format($item->grand_total, 0, ',', '.') }}</strong></td>
                            <td>
                                @if($item->metode_pembayaran == 'cash')
                                    <span class="badge bg-success">💵 Cash</span>
                                @elseif($item->metode_pembayaran == 'transfer')
                                    <span class="badge bg-primary">🏦 Transfer</span>
                                @elseif($item->metode_pembayaran == 'qris')
                                    <span class="badge bg-info">📱 QRIS</span>
                                @else
                                    <span class="badge bg-secondary">💳 {{ ucfirst($item->metode_pembayaran) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $item->cabang->nama_cabang ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                {{-- Detail --}}
                                <a href="{{ route('penjualan.show', $item->id) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- Print --}}
                                <a href="{{ route('penjualan.print', $item->id) }}" 
                                   class="btn btn-sm btn-success" 
                                   target="_blank" 
                                   title="Print">
                                    <i class="bi bi-printer"></i>
                                </a>

                                {{-- Edit (Hanya Admin & Super Admin) --}}
                                @if(Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin'))
                                    <a href="{{ route('penjualan.edit', $item->id) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('penjualan.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus penjualan {{ $item->nomor_nota }}? Stok barang akan dikembalikan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data invoice
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan {{ $penjualan->firstItem() ?? 0 }} - {{ $penjualan->lastItem() ?? 0 }} dari {{ $penjualan->total() }} data
                </div>
                <div>
                    {{ $penjualan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection