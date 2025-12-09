@extends('layouts.app') 

@section('title', 'Daftar Harga Satuan Barang')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Daftar Harga Satuan Barang</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Harga Berdasarkan Satuan</h6>
                <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak Daftar
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted">Daftar ini mencakup harga jual barang berdasarkan satuan terkecil (dasar) dan satuan konversi (jika ada).</p>

                {{-- 🚀 FORMULIR FILTER BARU --}}
                <form method="GET" class="row g-2 mb-4 align-items-end">
                    
                    {{-- Input Pencarian --}}
                    <div class="col-md-4">
                        <label for="search" class="form-label visually-hidden">Cari Barang</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/kode/barcode..." value="{{ request('search') }}">
                    </div>

                    {{-- Filter Kategori --}}
                    <div class="col-md-3">
                        <label for="kategori" class="form-label visually-hidden">Kategori</label>
                        {{-- $kategoriList harus dikirim dari controller --}}
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Stok Barang --}}
                    <div class="col-md-3">
                        <label for="stok_filter" class="form-label visually-hidden">Filter Stok (Kurang Dari)</label>
                        {{-- $stokFilterOptions harus dikirim dari controller --}}
                        <select name="stok_filter" class="form-select">
                            <option value="">Semua Stok</option>
                            <option value="rendah" {{ request('stok_filter') == 'rendah' ? 'selected' : '' }}>Stok Rendah/Minimal</option>
                            @foreach($stokFilterOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('stok_filter') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Filter dan Reset --}}
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary"><i class="bi bi-filter me-1"></i> Filter</button>
                        {{-- Pastikan route ini mengarah ke fungsi yang benar --}}
                        <a href="{{ route('barang.harga-satuan') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
                {{-- 🚀 AKHIR FORMULIR FILTER BARU --}}


                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="hargaSatuanTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Satuan Dasar</th>
                                <th class="text-end">Harga Jual Dasar (Rp)</th>
                                <th>Harga Satuan Konversi Lain</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + $barang->firstItem() }}</td>
                                <td>{{ $item->kode_barang }}</td>
                                <td>
                                    <strong>{{ $item->nama_barang }}</strong>
                                    {{-- Tambahkan Badge Stok --}}
                                    <span class="badge {{ $item->badge_stok_class }} ms-2" title="Stok Saat Ini">{{ $item->stok }} {{ $item->satuan_terkecil }}</span>
                                </td>
                                <td class="text-center">{{ $item->satuan_terkecil }}</td>
                                <td class="text-end">{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td>
                                    @if ($item->satuanKonversi->isEmpty())
                                        <span class="text-muted">Tidak ada konversi satuan lain.</span>
                                    @else
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach ($item->satuanKonversi as $konv)
                                                <li>
                                                    <i class="bi bi-chevron-right me-1"></i>
                                                    {{ $konv->nama_satuan }} (1 = {{ $konv->jumlah_konversi }} {{ $item->satuan_terkecil }}) : 
                                                    Rp{{ number_format($konv->harga_jual, 0, ',', '.') }}
                                                    @if ($konv->is_default)
                                                        <span class="badge bg-primary text-white ms-1">Default</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data barang yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $barang->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection