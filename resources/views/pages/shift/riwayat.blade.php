@extends('layouts.app')

@section('title', 'Riwayat Shift')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h2 class="page-title mb-1">Riwayat Shift Kasir</h2>
                <p class="page-subtitle mb-0">Daftar shift yang telah dibuka dan ditutup.</p>
            </div>
        </div>
    </div>

    {{-- ✅ FORM FILTER (Disesuaikan dengan logika ShiftController@riwayat) --}}
    <div class="card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('shift.riwayat') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tanggal_dari" class="form-label">Tanggal Buka Dari</label>
                    <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" 
                            value="{{ request('tanggal_dari') }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_sampai" class="form-label">Tanggal Buka Sampai</label>
                    <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" 
                            value="{{ request('tanggal_sampai') }}">
                </div>
                {{-- Anda bisa tambahkan filter User ID di sini jika perlu --}}
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-filter me-2"></i>Filter Riwayat
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card-custom">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>
            <strong>Data Riwayat Shift</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-vertical-align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">Shift ID</th>
                            <th>Kasir</th>
                            <th>Waktu Buka</th>
                            <th>Waktu Tutup</th>
                            <th class="text-end">Modal Awal</th>
                            <th class="text-end">Total Penjualan</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shifts as $s)
                        <tr>
                            <td class="text-center">
                                {{-- PERUBAHAN DI SINI: Menggunakan kode_shift atau fallback ke id --}}
                                <strong>
                                    @if($s->kode_shift)
                                        #{{ $s->kode_shift }} 
                                    @else
                                        #{{ $s->id }}
                                    @endif
                                </strong>
                            </td>
                            <td>{{ $s->user->name ?? 'N/A' }}</td>
                            <td>{{ $s->waktu_buka->format('d M Y H:i') }}</td>
                            <td>
                                @if($s->waktu_tutup)
                                    {{ $s->waktu_tutup->format('d M Y H:i') }}
                                @else
                                    <span class="badge bg-success">Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($s->modal_awal, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{-- Kunci utama: Data sudah dihitung dan disimpan di Controller --}}
                                <strong>Rp {{ number_format($s->total_penjualan, 0, ',', '.') }}</strong> 
                            </td>
                            <td class="text-end">
                                @php
                                    $selisihClass = $s->selisih == 0 ? 'text-success' : ($s->selisih > 0 ? 'text-info' : 'text-danger');
                                @endphp
                                <span class="{{ $selisihClass }}">
                                    Rp {{ number_format($s->selisih, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{-- Tombol Detail Shift --}}
                                <a href="{{ route('shift.detail', $s->id) }}" class="btn btn-sm btn-info text-white me-1" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                {{-- Tombol Cetak Laporan --}}
                                <a href="{{ route('shift.cetakLaporan', $s->id) }}" target="_blank" class="btn btn-sm btn-success" title="Cetak Laporan">
                                    <i class="bi bi-printer"></i>
                                </a>
                                {{-- ✅ TOMBOL DELETE (Hanya tampil untuk Admin) --}}
                                @if(Auth::user()->role === 'admin')
                                <form action="{{ route('shift.destroy', $s->id) }}" method="POST" class="d-inline" 
                                    onsubmit="return confirm('APAKAH ANDA YAKIN INGIN MENGHAPUS SHIFT {{ $s->kode_shift ?? $s->id }}? Menghapus shift akan menghapus semua data penjualan di dalamnya.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data riwayat shift yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top">
            {{ $shifts->links('pagination::bootstrap-5') }} 
        </div>
    </div>
</div>
@endsection