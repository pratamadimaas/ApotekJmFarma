@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid dashboard-apotek">
    <!-- Welcome Header -->
    <div class="welcome-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="welcome-title">
                    <i class="bi bi-heart-pulse-fill me-2"></i>
                    Selamat Datang, {{ auth()->user()->name }}!
                </h2>
                <p class="welcome-subtitle mb-0">
                    <i class="bi bi-calendar-check me-2"></i>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
                    <span class="mx-2">•</span>
                    <i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::now()->format('H:i') }} WIB
                </p>
            </div>
            <div class="col-md-4 text-end">
                @if(!$shiftAktif)
                    <a href="{{ route('shift.buka.form') }}" class="btn btn-gradient-primary btn-lg">
                        <i class="bi bi-door-open me-2"></i>Buka Shift
                    </a>
                @else
                    <div class="shift-badge-large">
                        <i class="bi bi-clock-history me-2"></i>
                        <span>Shift Aktif</span>
                        <small class="d-block">Shift #{{ $shiftAktif->kode_shift }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Shift Alert -->
    @if(!$shiftAktif)
    <div class="alert alert-danger-custom mb-4">
        <div class="d-flex align-items-center">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-title">Shift Belum Dibuka</h5>
                <p class="mb-0">Anda perlu membuka shift terlebih dahulu untuk memulai transaksi penjualan.</p>
            </div>
            <a href="{{ route('shift.buka.form') }}" class="btn btn-light">Buka Sekarang</a>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Penjualan Hari Ini -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-label">Penjualan Hari Ini</span>
                    <h3 class="stat-value">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</h3>
                    <div class="stat-footer">
                        <i class="bi bi-receipt me-1"></i>
                        {{ $transaksiHariIni }} transaksi
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Penjualan Bulan Ini -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-label">Penjualan Bulan Ini</span>
                    <h3 class="stat-value">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</h3>
                    <div class="stat-footer">
                        <i class="bi bi-calendar-month me-1"></i>
                        {{ \Carbon\Carbon::now()->format('F Y') }}
                    </div>
                </div>
            </div>
        </div>

         {{-- 🔒 LABA CARDS: HANYA UNTUK ADMIN & SUPER ADMIN --}}
    @if(!auth()->user()->isKasir())
    <!-- Card 3: Laba Hari Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="bi bi-piggy-bank-fill"></i>
            </div>
            <div class="stat-card-content">
                <span class="stat-label">Laba Hari Ini</span>
                <h3 class="stat-value">Rp {{ number_format($labaHariIni, 0, ',', '.') }}</h3>
                <div class="stat-footer">
                    <i class="bi bi-graph-up me-1"></i>
                    @if($penjualanHariIni > 0)
                        Margin: {{ number_format(($labaHariIni / $penjualanHariIni) * 100, 1) }}%
                    @else
                        Margin: 0%
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Laba Bulan Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="stat-card-content">
                <span class="stat-label">Laba Bulan Ini</span>
                <h3 class="stat-value">Rp {{ number_format($labaBulanIni, 0, ',', '.') }}</h3>
                <div class="stat-footer">
                    <i class="bi bi-graph-up me-1"></i>
                    @if($penjualanBulanIni > 0)
                        Margin: {{ number_format(($labaBulanIni / $penjualanBulanIni) * 100, 1) }}%
                    @else
                        Margin: 0%
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Card 3 untuk Kasir: Stok Minimum -->
    <div class="col-xl-4 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-card-content">
                <span class="stat-label">Stok Minimum</span>
                <h3 class="stat-value">{{ $barangStokMinimum }}</h3>
                <div class="stat-footer">
                    <i class="bi bi-box-seam me-1"></i>
                    Barang perlu restock
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
    {{-- Shift Aktif Semua User --}}
@if($shiftAktifSemua->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card-modern">
            <div class="card-modern-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h5 class="card-modern-title mb-0">Shift Aktif Saat Ini</h5>
                            <small class="text-muted">{{ $shiftAktifSemua->count() }} user sedang bertugas</small>
                        </div>
                    </div>
                    <div class="badge bg-success-soft" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="bi bi-circle-fill pulse-dot me-1" style="font-size: 0.5rem;"></i>
                        Live
                    </div>
                </div>
            </div>
            <div class="card-modern-body">
                <div class="row g-3">
                    @foreach($shiftAktifSemua as $shift)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="shift-active-card">
                            <div class="shift-active-header">
                                <div class="shift-active-avatar">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div class="shift-active-info">
                                    <div class="shift-active-name">{{ $shift->user->name }}</div>
                                    <div class="shift-active-role">
                                        <i class="bi bi-briefcase me-1"></i>
                                        {{ ucfirst($shift->user->role ?? 'kasir') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="shift-active-details">
                                <div class="shift-detail-item">
                                    <i class="bi bi-clock-history"></i>
                                    <span>{{ \Carbon\Carbon::parse($shift->waktu_buka)->format('H:i') }}</span>
                                </div>
                                <div class="shift-detail-item">
                                    <i class="bi bi-hash"></i>
                                    <span>{{ $shift->kode_shift ?? 'Shift #'.$shift->id }}</span>
                                </div>
                                <div class="shift-detail-item">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>Rp {{ number_format($shift->saldo_awal ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- ✅ TAMBAHAN: TOTAL PENJUALAN SHIFT INI (REAL-TIME) --}}
                            <div class="shift-penjualan-box">
                                <div class="shift-penjualan-label">
                                    <i class="bi bi-cart-check me-1"></i>Penjualan Shift Ini
                                </div>
                                <div class="shift-penjualan-amount">
                                    Rp {{ number_format($shift->total_penjualan_realtime ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="shift-penjualan-count">
                                    {{ $shift->jumlah_transaksi_realtime ?? 0 }} transaksi
                                </div>
                            </div>

                            <div class="shift-active-status">
                                <span class="badge bg-success-soft">
                                    <i class="bi bi-circle-fill pulse-dot me-1"></i>Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Second Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card stat-card-danger">
                <div class="stat-card-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-label">Stok Minimum</span>
                    <h3 class="stat-value">{{ $barangStokMinimum }}</h3>
                    <div class="stat-footer">
                        <i class="bi bi-box-seam me-1"></i>
                        Barang perlu restock
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stat-card stat-card-purple">
                <div class="stat-card-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-label">Barang Terlaris</span>
                    <h3 class="stat-value">{{ $barangTerlaris->count() }}</h3>
                    <div class="stat-footer">
                        <i class="bi bi-trophy me-1"></i>
                        Top produk bulan ini
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stat-card stat-card-teal">
                <div class="stat-card-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div class="stat-card-content">
                    <span class="stat-label">Total Transaksi</span>
                    <h3 class="stat-value">{{ \App\Models\Penjualan::thisMonth()->count() }}</h3>
                    <div class="stat-footer">
                        <i class="bi bi-calendar-month me-1"></i>
                        Bulan ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card-modern">
                <div class="card-modern-header">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div>
                            <h5 class="card-modern-title mb-0">Grafik Penjualan</h5>
                            <small class="text-muted">7 Hari Terakhir</small>
                        </div>
                    </div>
                </div>
                <div class="card-modern-body">
                    <canvas id="chartPenjualan" height="280"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-modern">
                <div class="card-modern-header">
                    <div class="d-flex align-items-center">
                        <div class="card-icon me-3">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <h5 class="card-modern-title mb-0">Top 5 Terlaris</h5>
                            <small class="text-muted">Bulan Ini</small>
                        </div>
                    </div>
                </div>
                <div class="card-modern-body p-0">
                    @forelse($barangTerlaris as $item)
                    <div class="top-item">
                        <div class="top-item-rank">{{ $loop->iteration }}</div>
                        <div class="top-item-content">
                            <div class="top-item-name">{{ $item->nama_barang }}</div>
                            <div class="top-item-stats">
                                <span class="badge bg-primary-soft">
                                    <i class="bi bi-box me-1"></i>{{ number_format($item->total_terjual) }} terjual
                                </span>
                            </div>
                        </div>
                        <div class="top-item-revenue">
                            Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}
                        </div>
                    </div>
                    @empty
                    <div class="empty-state-small">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada data penjualan</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Barang Stok Hampir Habis -->
    @if($barangStokMinimum > 0)
    <div class="row">
        <div class="col-12">
            <div class="card-modern card-alert-danger">
                <div class="card-modern-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="card-icon me-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div>
                                <h5 class="card-modern-title mb-0">Peringatan Stok</h5>
                                <small class="text-muted">{{ $barangStokMinimum }} barang memerlukan perhatian</small>
                            </div>
                        </div>
                        <a href="{{ route('barang.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-arrow-right me-2"></i>Kelola Stok
                        </a>
                    </div>
                </div>
                <div class="card-modern-body">
                    <div class="row g-3">
                        @forelse($barangHabis as $barang)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <div class="stock-alert-item">
                                <div class="stock-alert-icon">
                                    <i class="bi bi-capsule"></i>
                                </div>
                                <div class="stock-alert-name">{{ $barang->nama_barang }}</div>
                                <div class="stock-alert-qty">
                                    <span class="badge bg-danger">Stok: {{ $barang->stok }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted text-center mb-0">Tidak ada barang dengan stok minimum</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
/* Dashboard Apotek Theme */
:root {
    --apotek-primary: #667eea;
    --apotek-success: #10b981;
    --apotek-warning: #f59e0b;
    --apotek-danger: #ef4444;
    --apotek-info: #3b82f6;
    --apotek-teal: #14b8a6;
    --apotek-purple: #8b5cf6;
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --gradient-info: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    --gradient-teal: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    --gradient-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

/* Welcome Header */
.welcome-header {
    padding: 1.5rem 0;
}

.welcome-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.welcome-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
}

.shift-badge-large {
    background: var(--gradient-success);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.shift-badge-large small {
    font-size: 0.85rem;
    opacity: 0.9;
}

/* Alert Custom */
.alert-danger-custom {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 2px solid #fecaca;
    border-radius: 16px;
    padding: 1.5rem;
}

.alert-icon {
    width: 60px;
    height: 60px;
    background: var(--apotek-danger);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 1.5rem;
}

.alert-title {
    font-weight: 700;
    color: #991b1b;
    margin-bottom: 0.25rem;
}

/* Stats Cards */
.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.stat-card-icon {
    position: absolute;
    top: -20px;
    right: 20px;
    width: 80px;
    height: 80px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    opacity: 0.9;
}

.stat-card-primary .stat-card-icon {
    background: var(--gradient-primary);
}

.stat-card-success .stat-card-icon {
    background: var(--gradient-success);
}

.stat-card-warning .stat-card-icon {
    background: var(--gradient-warning);
}

.stat-card-info .stat-card-icon {
    background: var(--gradient-info);
}

.stat-card-danger .stat-card-icon {
    background: var(--gradient-danger);
}

.stat-card-teal .stat-card-icon {
    background: var(--gradient-teal);
}

.stat-card-purple .stat-card-icon {
    background: var(--gradient-purple);
}

.stat-label {
    display: block;
    color: #6b7280;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0.5rem 0;
}

.stat-card-primary .stat-value {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-success .stat-value {
    background: var(--gradient-success);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-warning .stat-value {
    background: var(--gradient-warning);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-info .stat-value {
    background: var(--gradient-info);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-danger .stat-value {
    background: var(--gradient-danger);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-teal .stat-value {
    background: var(--gradient-teal);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-card-purple .stat-value {
    background: var(--gradient-purple);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-footer {
    color: #9ca3af;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

/* Shift Active Cards */
.shift-penjualan-box {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 2px solid #0ea5e9;
    border-radius: 8px;
    padding: 0.75rem;
    margin: 0.75rem 0;
    text-align: center;
}

.shift-penjualan-label {
    font-size: 0.75rem;
    color: #0369a1;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.shift-penjualan-amount {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0c4a6e;
    margin-bottom: 0.25rem;
}

.shift-penjualan-count {
    font-size: 0.7rem;
    color: #0369a1;
}

.shift-active-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.3s ease;
    height: 100%;
}

.shift-active-card:hover {
    border-color: var(--apotek-success);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    transform: translateY(-2px);
}

.shift-active-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.shift-active-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.shift-active-info {
    flex: 1;
}

.shift-active-name {
    font-weight: 700;
    color: #1f2937;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.shift-active-role {
    font-size: 0.8rem;
    color: #6b7280;
}

.shift-active-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.shift-detail-item {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #4b5563;
}

.shift-detail-item i {
    width: 20px;
    color: var(--apotek-success);
    margin-right: 0.5rem;
}

.shift-active-status {
    text-align: center;
    margin-top: 0.75rem;
}

.bg-success-soft {
    background: rgba(16, 185, 129, 0.1);
    color: var(--apotek-success);
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
}

.pulse-dot {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* Modern Cards */
.card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    height: 100%;
}

.card-modern-header {
    padding: 1.5rem;
    border-bottom: 2px solid #f3f4f6;
}

.card-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.card-modern-title {
    font-weight: 700;
    color: #1f2937;
}

.card-modern-body {
    padding: 1.5rem;
}

/* Top Items */
.top-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.3s ease;
}

.top-item:last-child {
    border-bottom: none;
}

.top-item:hover {
    background: #f9fafb;
}

.top-item-rank {
    width: 36px;
    height: 36px;
    background: var(--gradient-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    margin-right: 1rem;
    flex-shrink: 0;
}

.top-item-content {
    flex: 1;
}

.top-item-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.top-item-stats {
    margin-top: 0.25rem;
}

.top-item-revenue {
    font-weight: 700;
    color: var(--apotek-success);
    font-size: 0.9rem;
    text-align: right;
}

.bg-primary-soft {
    background: rgba(102, 126, 234, 0.1);
    color: var(--apotek-primary);
    font-size: 0.75rem;
}

/* Alert Card */
.card-alert-danger {
    border: 2px solid #fecaca;
}

.card-alert-danger .card-modern-header {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

/* Stock Alert Items */
.stock-alert-item {
    background: white;
    border: 2px solid #fecaca;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stock-alert-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(239, 68, 68, 0.2);
}

.stock-alert-icon {
    width: 48px;
    height: 48px;
    background: var(--apotek-danger);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin: 0 auto 0.75rem;
}

.stock-alert-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    min-height: 2.5rem;
}

.stock-alert-qty .badge {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
}

/* Empty State */
.empty-state-small {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}

.empty-state-small i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

/* Button Gradient */
.btn-gradient-primary {
    background: var(--gradient-primary);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-gradient-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.5rem;
    }
.stat-value {
    font-size: 1.5rem;
}

.shift-badge-large {
    margin-top: 1rem;
}
}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Data penjualan 7 hari
const penjualan7HariData = @json($penjualan7Hari);

// Format labels dan data
const chartLabels = penjualan7HariData.map(item => {
    const date = new Date(item.tanggal);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
});
const chartData = penjualan7HariData.map(item => item.total);

// Create gradient
const ctx = document.getElementById('chartPenjualan').getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
gradient.addColorStop(1, 'rgba(102, 126, 234, 0.01)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Penjualan',
            data: chartData,
            borderColor: '#667eea',
            backgroundColor: gradient,
            borderWidth: 3,
            pointRadius: 6,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 8,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1f2937',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                borderRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f3f4f6',
                    borderDash: [5, 5]
                },
                ticks: {
                    color: '#6b7280',
                    callback: function(value) {
                        return 'Rp ' + (value / 1000) + 'K';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6b7280'
                }
            }
        }
    }
});
</script>
@endpush