@extends('layouts.app')

@section('title', 'Import Data Barang')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Import Data Barang dari Excel</h5>
                            <p class="text-sm mb-0">Upload file Excel untuk import data barang secara massal</p>
                        </div>
                        <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Alert Errors --}}
                    @if(session('import_errors'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>⚠️ Perhatian!</strong> Beberapa data gagal diimport:
                            <ul class="mb-0 mt-2">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Panduan Import --}}
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Panduan Import:</h6>
                        <ol class="mb-0">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data barang sesuai format template</li>
                            <li>Kolom yang <strong>wajib diisi</strong>: Kode Barang, Nama Barang, Kategori, Satuan</li>
                            <li>Upload file Excel (format: .xlsx, .xls, .csv)</li>
                            <li>Maksimal ukuran file: 2MB</li>
                        </ol>
                    </div>

                    <div class="row">
                        {{-- Download Template --}}
                        <div class="col-md-6">
                            <div class="card bg-gradient-primary">
                                <div class="card-body text-center text-white">
                                    <i class="fas fa-download fa-3x mb-3"></i>
                                    <h5 class="text-white">Download Template Excel</h5>
                                    <p class="mb-3">Template sudah berisi contoh data dan format yang benar</p>
                                    <a href="{{ route('barang.download-template') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-file-excel me-2"></i> Download Template
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Form Upload --}}
                        <div class="col-md-6">
                            <div class="card bg-gradient-success">
                                <div class="card-body text-center text-white">
                                    <i class="fas fa-upload fa-3x mb-3"></i>
                                    <h5 class="text-white">Upload File Excel</h5>
                                    <p class="mb-3">Pilih file Excel yang sudah diisi untuk diimport</p>
                                    
                                    <form action="{{ route('barang.import-excel') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" 
                                                   name="file" 
                                                   class="form-control form-control-lg @error('file') is-invalid @enderror" 
                                                   accept=".xlsx,.xls,.csv"
                                                   required>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-light btn-lg w-100">
                                            <i class="fas fa-file-import me-2"></i> Import Data Barang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Format Template Info --}}
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Format Template Excel</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kolom</th>
                                            <th>Wajib</th>
                                            <th>Tipe Data</th>
                                            <th>Contoh</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>kode_barang</code></td>
                                            <td><span class="badge bg-danger">Ya</span></td>
                                            <td>Text</td>
                                            <td>BRG001</td>
                                            <td>Kode unik, tidak boleh duplikat</td>
                                        </tr>
                                        <tr>
                                            <td><code>barcode</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>8992772311212</td>
                                            <td>Boleh kosong</td>
                                        </tr>
                                        <tr>
                                            <td><code>nama_barang</code></td>
                                            <td><span class="badge bg-danger">Ya</span></td>
                                            <td>Text</td>
                                            <td>Paracetamol 500mg</td>
                                            <td>Nama lengkap barang</td>
                                        </tr>
                                        <tr>
                                            <td><code>kategori</code></td>
                                            <td><span class="badge bg-danger">Ya</span></td>
                                            <td>Text</td>
                                            <td>Obat</td>
                                            <td>Kategori produk</td>
                                        </tr>
                                        <tr>
                                            <td><code>satuan_terkecil</code></td>
                                            <td><span class="badge bg-danger">Ya</span></td>
                                            <td>Text</td>
                                            <td>Strip, Box, Pcs</td>
                                            <td>Satuan terkecil</td>
                                        </tr>
                                        <tr>
                                            <td><code>harga_beli</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>5000</td>
                                            <td>Harga beli tanpa titik/koma</td>
                                        </tr>
                                        <tr>
                                            <td><code>harga_jual</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>7000</td>
                                            <td>Harga jual tanpa titik/koma</td>
                                        </tr>
                                        <tr>
                                            <td><code>stok</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>100</td>
                                            <td>Stok awal (default: 0)</td>
                                        </tr>
                                        <tr>
                                            <td><code>stok_minimal</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>10</td>
                                            <td>Batas stok minimum (default: 5)</td>
                                        </tr>
                                        <tr>
                                            <td><code>lokasi_rak</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Rak A1</td>
                                            <td>Lokasi penyimpanan</td>
                                        </tr>
                                        <tr>
                                            <td><code>deskripsi</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Obat demam</td>
                                            <td>Keterangan tambahan</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection