@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi bi-file-text-fill"></i>
                </div>
                <div>
                    <h2 class="page-title mb-1">Detail Penjualan</h2>
                    <p class="page-subtitle mb-0">Nomor Nota: <strong>{{ $penjualan->nomor_nota }}</strong></p>
                </div>
            </div>
            <div>
                <a href="{{ route('penjualan.riwayat') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('penjualan.print', $penjualan->id) }}" target="_blank" class="btn btn-success">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-custom mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Informasi Transaksi</strong>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tanggal Transaksi:</span>
                            <strong>{{ $penjualan->tanggal_penjualan->format('d F Y H:i') }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Kasir:</span>
                            <strong>{{ $penjualan->user->name ?? 'N/A' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Shift ID:</span>
                            <strong>#{{ $penjualan->shift_id ?? 'N/A' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Metode Pembayaran:</span>
                            <strong>{{ ucfirst($penjualan->metode_pembayaran) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Pelanggan:</span>
                            <strong>{{ $penjualan->nama_pelanggan ?? 'Umum' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-body">
                    <div class="summary-row">
                        <span>Total Penjualan</span>
                        <span class="fs-6">Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span class="fs-6 text-danger">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="summary-row summary-total">
                        <strong>Grand Total</strong>
                        <strong class="total-price">Rp {{ number_format($penjualan->grand_total, 0, ',', '.') }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="summary-row">
                        <span>Uang Dibayar</span>
                        <span class="fs-5 text-success">Rp {{ number_format($penjualan->jumlah_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Kembalian</span>
                        <span class="fs-5 text-info">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-list-check me-2"></i>
                    <strong>Detail Barang</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-vertical-align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penjualan->detailPenjualan as $detail)
                                <tr>
                                    <td>
                                        <strong>{{ $detail->barang->nama_barang ?? 'Barang Dihapus' }}</strong>
                                        <div class="small text-muted">{{ $detail->barang->kode_barang ?? '' }}</div>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $detail->jumlah }} {{ $detail->satuan }}</td>
                                    <td class="text-end">
                                        <strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ... (Tambahkan CSS kustom dari kasir.blade.php jika diperlukan, seperti .card-custom, .page-header, .summary-row, .total-price) ... */
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    font-size: 0.95rem;
}
.summary-total {
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}
.total-price {
    font-size: 1.5rem;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
@endpush