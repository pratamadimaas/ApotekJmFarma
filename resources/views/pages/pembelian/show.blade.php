@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Detail Pembelian</h2>
        <div class="d-flex">
            {{-- Tombol Kembali --}}
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            
            {{-- Tombol Cetak Barcode (Hanya muncul jika sudah Approved) --}}
            @if($pembelian->status === 'approved')
            <a href="{{ route('pembelian.cetak-barcode', $pembelian->id) }}" class="btn btn-info me-2">
                <i class="bi bi-upc-scan me-1"></i> Cetak Barcode
            </a>
            @endif
            
            {{-- Tombol Edit Pembelian --}}
            <a href="{{ route('pembelian.edit', $pembelian->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit Pembelian
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Dasar</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Nomor Faktur:</strong>
                            <span>{{ $pembelian->nomor_pembelian }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Tanggal Beli:</strong>
                            <span>{{ $pembelian->tanggal_pembelian->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Supplier:</strong>
                            <span>{{ $pembelian->supplier->nama_supplier ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Dibuat Oleh:</strong>
                            <span>{{ $pembelian->user->name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Status:</strong>
                            {{-- Penyesuaian class badge agar sesuai Bootstrap 5 --}}
                            <span><span class="badge {{ $pembelian->status == 'approved' ? 'bg-success' : ($pembelian->status == 'cancelled' ? 'bg-danger' : 'bg-warning') }}">{{ ucfirst($pembelian->status) }}</span></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Cabang:</strong>
                            <span>{{ $pembelian->cabang->nama_cabang ?? 'N/A' }}</span>
                        </li>
                        @if($pembelian->keterangan)
                        <li class="list-group-item">
                            <strong>Keterangan:</strong>
                            <p class="mb-0">{{ $pembelian->keterangan }}</p>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Biaya</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Total Pembelian (Gross)</td>
                                <td class="text-end">Rp {{ number_format($pembelian->total_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Diskon ({{ number_format($pembelian->diskon, 0) }}%)</td>
                                {{-- Hitung nilai diskon secara dinamis --}}
                                @php
                                    $nilaiDiskon = $pembelian->total_pembelian * ($pembelian->diskon / 100);
                                    $subtotalSetelahDiskon = $pembelian->total_pembelian - $nilaiDiskon;
                                    $nilaiPajak = $pembelian->grand_total - $subtotalSetelahDiskon;
                                @endphp
                                <td class="text-end text-danger">- Rp {{ number_format($nilaiDiskon, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>PPN/Pajak ({{ number_format($pembelian->pajak, 0) }}%)</td>
                                {{-- Hitung nilai pajak secara dinamis --}}
                                <td class="text-end text-success">+ Rp {{ number_format($nilaiPajak, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-primary">
                                <th>GRAND TOTAL (Dibayar)</th>
                                <th class="text-end">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detail Item Pembelian</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-vertical-align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th class="text-center">Tgl. Exp.</th>
                                    <th class="text-center">Qty Satuan</th>
                                    <th class="text-end">Harga Beli</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pembelian->detailPembelian as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $detail->barang->nama_barang ?? 'Barang Dihapus' }}</strong>
                                        <small class="text-muted d-block">{{ $detail->barang->kode_barang ?? '' }}</small>
                                    </td>
                                    <td class="text-center">
                                        {{ $detail->tanggal_kadaluarsa ? (is_string($detail->tanggal_kadaluarsa) ? date('M Y', strtotime($detail->tanggal_kadaluarsa)) : $detail->tanggal_kadaluarsa->format('M Y')) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $detail->jumlah ?? $detail->qty }} {{ $detail->satuan }}
                                    </td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                                    <td class="text-end">**Rp {{ number_format($detail->subtotal, 0, ',', '.') }}**</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada detail barang untuk pembelian ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection