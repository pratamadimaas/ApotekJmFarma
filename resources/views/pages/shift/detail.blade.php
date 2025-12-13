@extends('layouts.app')

@section('title', 'Detail Shift')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Detail Shift: {{ $shift->kode_shift }}</h4>
            <small class="text-muted">{{ $shift->user->name ?? 'N/A' }}</small>
        </div>
        <div>
            <a href="{{ route('shift.riwayat') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('shift.cetakLaporan', $shift->id) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Info Shift -->a
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Informasi Waktu</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Waktu Buka</small>
                            <p class="mb-2"><strong>{{ \Carbon\Carbon::parse($shift->waktu_buka)->translatedFormat('d M Y, H:i') }}</strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Waktu Tutup</small>
                            <p class="mb-2"><strong>{{ $shift->waktu_tutup ? \Carbon\Carbon::parse($shift->waktu_tutup)->translatedFormat('d M Y, H:i') : '-' }}</strong></p>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">Durasi Shift</small>
                    <p class="mb-0">
                        <strong>
                            @if($shift->waktu_tutup)
                                {{ \Carbon\Carbon::parse($shift->waktu_buka)->diffForHumans(\Carbon\Carbon::parse($shift->waktu_tutup), true) }}
                            @else
                                Belum Ditutup
                            @endif
                        </strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Ringkasan Keuangan</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">
                            <small class="text-muted">Saldo Awal</small>
                            <p class="mb-0"><strong>Rp {{ number_format($shift->saldo_awal ?? 0, 0, ',', '.') }}</strong></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Saldo Akhir</small>
                            <p class="mb-0"><strong>Rp {{ number_format($shift->saldo_akhir ?? 0, 0, ',', '.') }}</strong></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Selisih</small>
                            <p class="mb-0">
                                <strong class="{{ ($shift->selisih ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($shift->selisih ?? 0, 0, ',', '.') }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Status</small>
                            <p class="mb-0">
                                <span class="badge {{ $shift->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($shift->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Penjualan -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-receipt fs-1 text-primary"></i>
                    <h3 class="mt-2">{{ $statistik['jumlah_transaksi'] }}</h3>
                    <small class="text-muted">Total Transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cash-coin fs-1 text-success"></i>
                    <h5 class="mt-2">Rp {{ number_format($statistik['total_penjualan'], 0, ',', '.') }}</h5>
                    <small class="text-muted">Total Penjualan</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-graph-up fs-1 text-info"></i>
                    <h5 class="mt-2">Rp {{ number_format($statistik['rata_rata_transaksi'], 0, ',', '.') }}</h5>
                    <small class="text-muted">Rata-rata per Transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-wallet2 fs-1 text-warning"></i>
                    <h5 class="mt-2">{{ $metodePembayaran->count() }}</h5>
                    <small class="text-muted">Jenis Pembayaran</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Metode Pembayaran -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Detail per Metode Pembayaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Metode</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalSemua = $metodePembayaran->sum('total');
                        @endphp
                        @forelse($metodePembayaran as $metode)
                        <tr>
                            <td>
                                @if($metode['metode'] === 'cash')
                                    <i class="bi bi-cash text-success"></i> Cash
                                @elseif($metode['metode'] === 'debit')
                                    <i class="bi bi-credit-card text-primary"></i> Debit Card
                                @elseif($metode['metode'] === 'credit')
                                    <i class="bi bi-credit-card-2-front text-warning"></i> Credit Card
                                @elseif($metode['metode'] === 'qris')
                                    <i class="bi bi-qr-code text-info"></i> QRIS
                                @elseif($metode['metode'] === 'transfer')
                                    <i class="bi bi-bank text-danger"></i> Transfer
                                @else
                                    {{ ucfirst($metode['metode']) }}
                                @endif
                            </td>
                            <td class="text-center">{{ $metode['jumlah'] }}</td>
                            <td class="text-end"><strong>Rp {{ number_format($metode['total'], 0, ',', '.') }}</strong></td>
                            <td class="text-end">
                                {{ $totalSemua > 0 ? number_format(($metode['total'] / $totalSemua) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data metode pembayaran</td>
                        </tr>
                        @endforelse
                        @if($metodePembayaran->count() > 0)
                        <tr class="table-secondary fw-bold">
                            <td>TOTAL</td>
                            <td class="text-center">{{ $metodePembayaran->sum('jumlah') }}</td>
                            <td class="text-end">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
                            <td class="text-end">100%</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daftar Transaksi -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Daftar Transaksi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Faktur</th>
                            <th>Waktu</th>
                            <th>Total Bayar</th>
                            <th>Metode</th>
                            <th>Detail Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shift->penjualan as $penjualan)
                        <tr>
                            <td>{{ $penjualan->no_faktur }}</td>
                            <td>{{ \Carbon\Carbon::parse($penjualan->created_at)->translatedFormat('H:i:s') }}</td>
                            <td>Rp {{ number_format($penjualan->total_penjualan, 0, ',', '.') }}</td>
                            <td>
                                @if($penjualan->metode_pembayaran === 'cash')
                                    <span class="badge bg-success">Cash</span>
                                @elseif($penjualan->metode_pembayaran === 'debit')
                                    <span class="badge bg-primary">Debit</span>
                                @elseif($penjualan->metode_pembayaran === 'credit')
                                    <span class="badge bg-warning">Credit</span>
                                @elseif($penjualan->metode_pembayaran === 'qris')
                                    <span class="badge bg-info">QRIS</span>
                                @elseif($penjualan->metode_pembayaran === 'transfer')
                                    <span class="badge bg-danger">Transfer</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($penjualan->metode_pembayaran) }}</span>
                                @endif
                            </td>
                            <td>
                                {{-- ✅ FIX: Gunakan detailPenjualan (sesuai nama relasi di Model) --}}
                                @if($penjualan->detailPenjualan && $penjualan->detailPenjualan->count() > 0)
                                    @foreach ($penjualan->detailPenjualan as $detail)
                                        <small class="d-block">
                                            {{ $detail->barang->nama_barang ?? 'N/A' }} 
                                            ({{ $detail->qty }} {{ $detail->satuan }}) 
                                            - Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </small>
                                    @endforeach
                                @else
                                    <small class="text-muted">Tidak ada detail barang</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada transaksi penjualan dalam shift ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($shift->keterangan)
    <!-- Catatan -->
    <div class="card mt-4">
        <div class="card-header bg-warning">
            <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Catatan</h6>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $shift->keterangan }}</p>
        </div>
    </div>
    @endif
</div>
@endsection