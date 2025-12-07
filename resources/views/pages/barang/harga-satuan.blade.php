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
                                <td><strong>{{ $item->nama_barang }}</strong></td>
                                <td class="text-center">{{ $item->satuan_terkecil }}</td>
                                <td class="text-end">{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td>
                                    @if ($item->satuanKonversi->isEmpty())
                                        <span class="text-muted">Tidak ada konversi satuan lain.</span>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($item->satuanKonversi as $konv)
                                                <li>
                                                    <i class="bi bi-chevron-right me-1"></i>
                                                    {{ $konv->nama_satuan }} ({{ $konv->jumlah_konversi }} {{ $item->satuan_terkecil }}) : 
                                                    Rp{{ number_format($konv->harga_jual, 0, ',', '.') }}
                                                    @if ($konv->is_default)
                                                        <span class="badge bg-info text-white ms-1">Default</span>
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
                    {{ $barang->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection