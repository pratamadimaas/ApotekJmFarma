@extends('layouts.app')

@section('title', 'Detail Shift #' . $shift->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">Detail Shift #{{ $shift->id }}</h1>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Ringkasan Shift</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Kasir</td>
                            <td>:</td>
                            <td>{{ $shift->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Waktu Buka</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($shift->waktu_buka)->translatedFormat('d F Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Waktu Tutup</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($shift->waktu_tutup)->translatedFormat('d F Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Durasi</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($shift->waktu_buka)->diffForHumans(\Carbon\Carbon::parse($shift->waktu_tutup), true) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Modal Awal</td>
                            <td>:</td>
                            <td>Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Total Penjualan</td>
                            <td>:</td>
                            <td>Rp {{ number_format($shift->total_penjualan, 0, ',', '.') }} ({{ $shift->jumlah_transaksi }} Transaksi)</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Uang Fisik (Laci)</td>
                            <td>:</td>
                            <td>Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Selisih</td>
                            <td>:</td>
                            <td>
                                @php
                                    $class = $shift->selisih == 0 ? 'success' : ($shift->selisih > 0 ? 'warning' : 'danger');
                                    $prefix = $shift->selisih > 0 ? '+' : '';
                                @endphp
                                <span class="text-{{ $class }} font-weight-bold">{{ $prefix }} Rp {{ number_format($shift->selisih, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if ($shift->catatan)
            <div class="alert alert-info mt-3">
                <strong>Catatan Penutupan:</strong> {{ $shift->catatan }}
            </div>
            @endif
            
            <a href="{{ route('shift.riwayat') }}" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Kembali ke Riwayat</a>
            <a href="{{ route('shift.cetakLaporan', $shift->id) }}" class="btn btn-warning mt-3" target="_blank">
                <i class="bi bi-printer"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Daftar Penjualan dalam Shift -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">Daftar Transaksi Penjualan ({{ $shift->penjualan->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
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
                            <td>Rp {{ number_format($penjualan->total_bayar, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($penjualan->metode_pembayaran) }}</td>
                            <td>
                                @foreach ($penjualan->details as $detail)
                                    <small class="d-block">{{ $detail->barang->nama_barang }} ({{ $detail->qty }} {{ $detail->satuan }}) - Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</small>
                                @endforeach
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

</div>
@endsection