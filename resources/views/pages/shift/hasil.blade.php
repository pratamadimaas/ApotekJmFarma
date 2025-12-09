@extends('layouts.app')

@section('title', 'Hasil Tutup Shift #' . $shift->id)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header dengan Status -->
            <div class="text-center mb-4">
                <h2 class="mb-3">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Shift Berhasil Ditutup
                </h2>
                <p class="text-muted">Berikut adalah ringkasan hasil shift Anda hari ini</p>
            </div>

            <!-- Card Selisih (Status Utama) -->
            <div class="card shadow-lg mb-4 border-0">
                <div class="card-body text-center py-5">
                    <h5 class="text-muted mb-3">Status Laci Kasir</h5>
                    @php
                        $isBalanced = $shift->selisih == 0;
                        $isOver = $shift->selisih > 0;
                        $isShort = $shift->selisih < 0;
                        
                        if ($isBalanced) {
                            $statusClass = 'success';
                            $statusIcon = 'check-circle-fill';
                            $statusText = 'SEIMBANG';
                        } elseif ($isOver) {
                            $statusClass = 'warning';
                            $statusIcon = 'exclamation-triangle-fill';
                            $statusText = 'KELEBIHAN';
                        } else {
                            $statusClass = 'danger';
                            $statusIcon = 'x-circle-fill';
                            $statusText = 'KEKURANGAN';
                        }
                    @endphp
                    
                    <div class="mb-3">
                        <i class="bi bi-{{ $statusIcon }} text-{{ $statusClass }}" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="text-{{ $statusClass }} mb-2">{{ $statusText }}</h3>
                    <h1 class="display-4 fw-bold text-{{ $statusClass }}">
                        {{ $shift->selisih >= 0 ? '+' : '' }} Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
                    </h1>
                    
                    @if ($isBalanced)
                        <p class="text-muted mt-3">Sempurna! Uang di laci sesuai dengan perhitungan sistem.</p>
                    @elseif ($isOver)
                        <p class="text-warning mt-3">Uang di laci lebih banyak dari yang seharusnya.</p>
                    @else
                        <p class="text-danger mt-3">Uang di laci kurang dari yang seharusnya.</p>
                    @endif
                </div>
            </div>

            <!-- Detail Ringkasan -->
            <div class="row mb-4">
                <!-- Info Shift -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-info-circle me-2"></i>Informasi Shift
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted">Shift ID</td>
                                    <td class="fw-bold">#{{ $shift->id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Kasir</td>
                                    <td class="fw-bold">{{ $shift->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Waktu Buka</td>
                                    <td>{{ $shift->waktu_buka->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Waktu Tutup</td>
                                    <td>{{ $shift->waktu_tutup->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Durasi</td>
                                    <td class="fw-bold text-info">{{ $shift->waktu_buka->diffForHumans($shift->waktu_tutup, true) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Keuangan -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-cash-stack me-2"></i>Ringkasan Keuangan
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted">Modal Awal</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Penjualan Tunai</td>
                                    <td class="text-end text-success">+ Rp {{ number_format($tunai, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fw-bold">Uang Seharusnya</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($uangSeharusnya, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Uang Fisik di Laci</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fw-bold">Selisih</td>
                                    <td class="text-end fw-bold text-{{ $statusClass }}">
                                        {{ $shift->selisih >= 0 ? '+' : '' }} Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penjualan per Metode -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-credit-card me-2"></i>Rincian Penjualan per Metode Pembayaran
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-cash-coin text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-1 text-muted">Tunai (Cash)</h6>
                                <h5 class="fw-bold text-success">Rp {{ number_format($tunai, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-1 text-muted">Non-Tunai</h6>
                                <h5 class="fw-bold text-primary">Rp {{ number_format($nonTunai, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-graph-up text-dark" style="font-size: 2rem;"></i>
                                <h6 class="mt-2 mb-1 text-muted">Total Penjualan</h6>
                                <h5 class="fw-bold">Rp {{ number_format($shift->total_penjualan, 0, ',', '.') }}</h5>
                                <small class="text-muted">{{ $shift->jumlah_transaksi }} Transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            @if ($shift->catatan)
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning">
                    <i class="bi bi-sticky me-2"></i>Catatan Pengeluaran
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;">{{ $shift->catatan }}</p>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Pilih Aksi Selanjutnya:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('shift.cetakLaporan', $shift->id) }}" 
                               target="_blank" 
                               class="btn btn-success btn-lg w-100">
                                <i class="bi bi-printer me-2"></i>Cetak Laporan Shift (58mm)
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('shift.riwayat') }}" 
                               class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-clock-history me-2"></i>Lihat Riwayat Shift
                            </a>
                        </div>
                        <div class="col-md-12">
                            <a href="{{ route('dashboard') }}" 
                               class="btn btn-secondary btn-lg w-100">
                                <i class="bi bi-house me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto print option
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Tanya user apakah ingin langsung print
    if (confirm('Shift berhasil ditutup! Apakah Anda ingin mencetak laporan sekarang?')) {
        window.open('{{ route('shift.cetakLaporan', $shift->id) }}', '_blank');
    }
});
</script>
@endpush

@endsection