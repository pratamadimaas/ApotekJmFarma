@extends('layouts.app') 
{{-- Ganti 'layouts.app' dengan nama layout Anda yang sebenarnya --}}

@section('title', 'Laporan Stok Barang')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Laporan Stok Barang</h1>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                        <div class="dropdown no-arrow">
                            <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                                <i class="fas fa-print"></i> Cetak
                            </button>
                            <a href="#" class="btn btn-sm btn-outline-primary ml-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- FORM FILTER --}}
                        <form action="{{ route('laporan.stok') }}" method="GET" class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label for="kategori">Filter Kategori</label>
                                <select name="kategori" id="kategori" class="form-control">
                                    <option value="">Semua Kategori</option>
                                    {{-- Menggunakan data kategoriList dari controller --}}
                                    @foreach ($kategoriList as $kategori)
                                        <option value="{{ $kategori }}" 
                                            {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                            {{ $kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="filter">Status Stok</label>
                                <select name="filter" id="filter" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="minimal" {{ request('filter') == 'minimal' ? 'selected' : '' }}>Stok Mendekati Minimum</option>
                                    <option value="habis" {{ request('filter') == 'habis' ? 'selected' : '' }}>Stok Habis (0)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                                <a href="{{ route('laporan.stok') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- RINGKASAN STOK --}}
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Nilai Stok (HPP)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp{{ number_format($totalNilaiStok, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Nilai Jual Stok</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp{{ number_format($totalNilaiJual, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tag fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Potensi Laba Kotor</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp{{ number_format($potensialLaba, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Barang dalam Stok (Total: {{ $barang->count() }} Item)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th class="text-end">Stok Sekarang</th>
                                <th class="text-end">Stok Minimum</th>
                                <th class="text-end">Harga Beli (HPP)</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-end">Nilai Stok (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $item)
                            <tr>
                                <td>{{ $item->nama_barang }}</td>
                                <td>{{ $item->kategori }}</td>
                                <td class="text-end">
                                    {{ $item->stok }}
                                    @if ($item->stok <= $item->stok_minimum)
                                        <i class="fas fa-exclamation-triangle text-warning ml-2" title="Mendekati Minimum"></i>
                                    @elseif ($item->stok == 0)
                                        <i class="fas fa-times-circle text-danger ml-2" title="Stok Habis"></i>
                                    @endif
                                </td>
                                <td class="text-end">{{ $item->stok_minimum }}</td>
                                <td class="text-end">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($item->stok * $item->harga_beli, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data stok barang yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">TOTAL NILAI STOK</th>
                                <th class="text-end">Rp{{ number_format($totalNilaiStok, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection