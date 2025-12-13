@extends('layouts.app')

@section('title', 'Hasil Tutup Shift')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Card -->
            <div class="card shadow-lg border-0 rounded-3 mb-4">
                <div class="card-header bg-success text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Shift Berhasil Ditutup
                        </h5>
                        <span class="badge bg-light text-success">{{ $shift->kode_shift ?? '#'.$shift->id }}</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Info Shift -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-person me-1"></i>Kasir
                                </small>
                                <strong class="d-block">{{ $shift->user->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-door-open me-1"></i>Waktu Buka
                                </small>
                                <strong class="d-block">{{ $shift->waktu_buka->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-door-closed me-1"></i>Waktu Tutup
                                </small>
                                <strong class="d-block">{{ $shift->waktu_tutup->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- ✅ STATISTIK PENJUALAN (UNTUK ADMIN) -->
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                    <div class="alert alert-primary">
                        <h6 class="mb-3">
                            <i class="bi bi-graph-up me-2"></i>Statistik Penjualan Shift Ini
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Total Penjualan</small>
                                <h5 class="mb-0 text-primary">Rp {{ number_format($shift->total_penjualan ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Jumlah Transaksi</small>
                                <h5 class="mb-0 text-primary">{{ $shift->penjualan->count() }} transaksi</h5>
                            </div>
                            <div class="col-md-4 mb-2">
                                <small class="text-muted d-block">Rata-rata/Transaksi</small>
                                <h5 class="mb-0 text-primary">
                                    @if($shift->penjualan->count() > 0)
                                        Rp {{ number_format($shift->total_penjualan / $shift->penjualan->count(), 0, ',', '.') }}
                                    @else
                                        Rp 0
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>

                    <!-- Detail per Metode Pembayaran -->
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <th class="text-center">Jumlah Transaksi</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailMetode as $metode)
                                <tr>
                                    <td>
                                        @if($metode->metode_pembayaran == 'cash')
                                            <i class="bi bi-cash text-success me-1"></i>Tunai
                                        @elseif($metode->metode_pembayaran == 'debit')
                                            <i class="bi bi-credit-card text-primary me-1"></i>Debit
                                        @elseif($metode->metode_pembayaran == 'credit')
                                            <i class="bi bi-credit-card-2-front text-warning me-1"></i>Credit
                                        @elseif($metode->metode_pembayaran == 'qris')
                                            <i class="bi bi-qr-code text-info me-1"></i>QRIS
                                        @else
                                            <i class="bi bi-bank text-secondary me-1"></i>Transfer
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $metode->jumlah }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($metode->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Rekap Kas -->
                    <h6 class="mb-3">
                        <i class="bi bi-cash-coin me-2"></i>Rekap Kas Tunai
                    </h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Modal Awal</small>
                                    <h5 class="mb-0">Rp {{ number_format($shift->saldo_awal, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <small class="text-muted d-block">Penjualan Tunai</small>
                                    <h5 class="mb-0 text-success">Rp {{ number_format($tunai, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center py-3">
                                    <small class="d-block">Seharusnya</small>
                                    <h5 class="mb-0">Rp {{ number_format($uangSeharusnya, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center py-3">
                                    <small class="d-block">Uang Fisik</small>
                                    <h5 class="mb-0">Rp {{ number_format($shift->saldo_akhir, 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selisih -->
                    <div class="alert {{ $shift->selisih == 0 ? 'alert-success' : ($shift->selisih > 0 ? 'alert-warning' : 'alert-danger') }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>
                                    <i class="bi bi-{{ $shift->selisih == 0 ? 'check-circle' : 'exclamation-triangle' }}-fill me-2"></i>
                                    Selisih Kas:
                                </strong>
                            </div>
                            <h4 class="mb-0">
                                @if($shift->selisih > 0)
                                    +Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                                    <small class="d-block" style="font-size: 0.75rem;">(Kelebihan)</small>
                                @elseif($shift->selisih < 0)
                                    -Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
                                    <small class="d-block" style="font-size: 0.75rem;">(Kekurangan)</small>
                                @else
                                    Pas! (Rp 0)
                                @endif
                            </h4>
                        </div>
                    </div>

                    <!-- Catatan -->
                    @if($shift->keterangan)
                    <div class="alert alert-info">
                        <strong><i class="bi bi-sticky me-2"></i>Catatan:</strong><br>
                        {{ $shift->keterangan }}
                    </div>
                    @endif

                    <!-- Non-Tunai -->
                    @if($nonTunai > 0)
                    <div class="alert alert-secondary">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-credit-card me-2"></i>Penjualan Non-Tunai:</span>
                            <strong>Rp {{ number_format($nonTunai, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('shift.cetakLaporan', $shift->id) }}" 
                           class="btn btn-outline-primary" 
                           target="_blank">
                            <i class="bi bi-printer me-2"></i>Cetak Laporan
                        </a>
                        <a href="{{ route('shift.detail', $shift->id) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-2"></i>Lihat Detail
                        </a>
                        <a href="{{ route('dashboard') }}" 
                           class="btn btn-success flex-fill">
                            <i class="bi bi-house me-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection