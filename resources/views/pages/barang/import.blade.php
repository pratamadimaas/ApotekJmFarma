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
                            <p class="text-sm mb-0">Upload file Excel untuk import data barang secara massal (termasuk satuan konversi)</p>
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
                            <li>Kolom yang <strong>wajib diisi</strong>: Kode Barang, Nama Barang, Kategori, Satuan Terkecil</li>
                            <li><strong>BARU:</strong> Anda bisa menambahkan hingga <span class="badge bg-success">5 Satuan Konversi</span> per barang</li>
                            <li>Upload file Excel (format: .xlsx, .xls, .csv)</li>
                            <li>Maksimal ukuran file: 5MB</li>
                        </ol>
                    </div>

                    <div class="row">
                        {{-- Download Template --}}
                        <div class="col-md-6">
                            <div class="card bg-gradient-primary">
                                <div class="card-body text-center text-white">
                                    <i class="fas fa-download fa-3x mb-3"></i>
                                    <h5 class="text-white">Download Template Excel</h5>
                                    <p class="mb-3">Template sudah berisi contoh data dan format yang benar (dengan satuan konversi)</p>
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

                    {{-- Format Template Info - Data Barang Utama --}}
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Format Template Excel - Data Barang Utama</h6>
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
                                            <td>Satuan dasar/terkecil</td>
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

                    {{-- Format Satuan Konversi --}}
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Format Template Excel - Satuan Konversi (Maksimal 5)</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Informasi:</strong> Setiap barang bisa memiliki hingga <strong>5 satuan konversi</strong>. 
                                Setiap satuan konversi memiliki 4 kolom:
                                <ul class="mb-0 mt-2">
                                    <li><strong>nama</strong> - Nama satuan (misal: Box, Karton, Lusin)</li>
                                    <li><strong>jumlah</strong> - Jumlah konversi ke satuan terkecil (misal: 1 Box = 10 Strip)</li>
                                    <li><strong>harga</strong> - Harga jual untuk satuan ini</li>
                                    <li><strong>default</strong> - Apakah ini satuan default? (Ya/Tidak)</li>
                                </ul>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Satuan Ke-</th>
                                            <th>Kolom</th>
                                            <th>Wajib</th>
                                            <th>Tipe</th>
                                            <th>Contoh</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Konversi 1 --}}
                                        <tr class="table-info">
                                            <td rowspan="4" class="align-middle text-center"><strong>1</strong></td>
                                            <td><code>konversi_1_nama</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Box</td>
                                            <td>Nama satuan konversi</td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><code>konversi_1_jumlah</code></td>
                                            <td><span class="badge bg-warning">Kondisional</span></td>
                                            <td>Angka</td>
                                            <td>10</td>
                                            <td>1 Box = 10 Strip (satuan terkecil)</td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><code>konversi_1_harga</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>65000</td>
                                            <td>Harga jual per Box</td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><code>konversi_1_default</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Ya / Tidak</td>
                                            <td>Satuan default untuk penjualan</td>
                                        </tr>

                                        {{-- Konversi 2 --}}
                                        <tr>
                                            <td rowspan="4" class="align-middle text-center"><strong>2</strong></td>
                                            <td><code>konversi_2_nama</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Karton</td>
                                            <td>Nama satuan konversi</td>
                                        </tr>
                                        <tr>
                                            <td><code>konversi_2_jumlah</code></td>
                                            <td><span class="badge bg-warning">Kondisional</span></td>
                                            <td>Angka</td>
                                            <td>100</td>
                                            <td>1 Karton = 100 Strip</td>
                                        </tr>
                                        <tr>
                                            <td><code>konversi_2_harga</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Angka</td>
                                            <td>600000</td>
                                            <td>Harga jual per Karton</td>
                                        </tr>
                                        <tr>
                                            <td><code>konversi_2_default</code></td>
                                            <td><span class="badge bg-secondary">Tidak</span></td>
                                            <td>Text</td>
                                            <td>Tidak</td>
                                            <td>-</td>
                                        </tr>

                                        {{-- Info untuk konversi 3-5 --}}
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <em>... dan seterusnya hingga <strong>konversi_5_nama</strong>, <strong>konversi_5_jumlah</strong>, <strong>konversi_5_harga</strong>, <strong>konversi_5_default</strong></em>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penting:</strong>
                                <ul class="mb-0">
                                    <li>Jika <code>konversi_X_nama</code> diisi, maka <code>konversi_X_jumlah</code> <strong>HARUS diisi</strong></li>
                                    <li>Kolom harga dan default boleh dikosongkan</li>
                                    <li>Jumlah konversi harus berupa angka positif (lebih besar dari 0)</li>
                                    <li>Untuk default, gunakan: <strong>Ya</strong>, <strong>Tidak</strong>, <strong>1</strong>, <strong>0</strong>, atau kosongkan</li>
                                </ul>
                            </div>

                            {{-- Contoh Data --}}
                            <div class="card bg-light mt-3">
                                <div class="card-header">
                                    <strong>📋 Contoh Data Lengkap</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr class="table-primary">
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th>Satuan Dasar</th>
                                                <th>Konversi 1</th>
                                                <th>Konversi 2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>BRG001</td>
                                                <td>Paracetamol 500mg</td>
                                                <td>Strip</td>
                                                <td>
                                                    Box, 10, 65000, Ya
                                                </td>
                                                <td>
                                                    Karton, 100, 600000, Tidak
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <small class="text-muted">
                                        Artinya: 1 Box = 10 Strip (Rp 65.000), 1 Karton = 100 Strip (Rp 600.000), dan Box adalah satuan default.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection