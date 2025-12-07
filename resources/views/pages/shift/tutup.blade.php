@extends('layouts.app')

@section('title', 'Tutup Shift')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <strong>Tutup Shift Kasir</strong>
                </div>
                <div class="card-body">
                    <h5>Ringkasan Shift</h5>
                    <table class="table">
                        <tr>
                            <td>Waktu Buka</td>
                            <td>{{ $shift->waktu_buka->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td>Modal Awal</td>
                            <td>Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Jumlah Transaksi</td>
                            <td>{{ $jumlahTransaksi }}</td>
                        </tr>
                        <tr>
                            <td>Total Penjualan</td>
                            <td class="text-success"><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Penjualan Tunai</td>
                            <td>Rp {{ number_format($tunai, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Penjualan Non-Tunai</td>
                            <td>Rp {{ number_format($nonTunai, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Uang Seharusnya di Laci</strong></td>
                            <td><strong>Rp {{ number_format($uangDilaci, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>

                    <hr>

                    <form method="POST" action="{{ route('shift.tutup.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label>Uang Fisik di Laci <span class="text-danger">*</span></label>
                            <input type="number" name="uang_fisik" class="form-control" required autofocus>
                            <small class="text-muted">Hitung uang tunai yang ada di laci kasir</small>
                        </div>

                        <div class="mb-3">
                            <label>Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-lock me-2"></i>Tutup Shift
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection