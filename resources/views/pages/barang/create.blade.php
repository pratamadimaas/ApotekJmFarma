@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Barang Baru</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('barang.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" class="form-control @error('kode_barang') is-invalid @enderror" 
                                   value="{{ old('kode_barang') }}" required>
                            @error('kode_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control @error('nama_barang') is-invalid @enderror" 
                                   value="{{ old('nama_barang') }}" required>
                            @error('nama_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" 
                                   value="{{ old('kategori') }}" list="kategoriList" required>
                            <datalist id="kategoriList">
                                <option value="Obat Bebas">
                                <option value="Obat Keras">
                                <option value="Vitamin">
                                <option value="Alat Kesehatan">
                            </datalist>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Satuan Terkecil <span class="text-danger">*</span></label>
                            <input type="text" name="satuan_dasar" class="form-control @error('satuan_dasar') is-invalid @enderror" 
                                   value="{{ old('satuan_dasar') }}" required placeholder="Contoh: tablet, pcs, ml">
                            <small class="text-muted">Satuan dasar/terkecil untuk perhitungan stok</small>
                            @error('satuan_dasar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" name="harga_beli" class="form-control @error('harga_beli') is-invalid @enderror" 
                                   value="{{ old('harga_beli') }}" required step="0.01">
                            @error('harga_beli')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Harga Jual (Satuan Terkecil) <span class="text-danger">*</span></label>
                            <input type="number" name="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror" 
                                   value="{{ old('harga_jual') }}" required step="0.01">
                            <small class="text-muted">Harga jual per satuan terkecil</small>
                            @error('harga_jual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control @error('stok') is-invalid @enderror" 
                                   value="{{ old('stok') }}" required>
                            <small class="text-muted">Dalam satuan terkecil</small>
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Stok Minimal <span class="text-danger">*</span></label>
                            <input type="number" name="stok_minimal" class="form-control @error('stok_minimal') is-invalid @enderror" 
                                   value="{{ old('stok_minimal', 10) }}" required>
                            @error('stok_minimal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Lokasi Rak</label>
                            <input type="text" name="lokasi_rak" class="form-control @error('lokasi_rak') is-invalid @enderror" 
                                   value="{{ old('lokasi_rak') }}" placeholder="Contoh: Rak A-1">
                            @error('lokasi_rak')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                              rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ✅ SECTION MULTI-SATUAN --}}
                <hr class="my-4">
                <h5 class="mb-3">
                    <i class="bi bi-box-seam me-2"></i>Satuan Konversi (Opsional)
                    <small class="text-muted">- Untuk penjualan dalam satuan lebih besar</small>
                </h5>
                
                <div id="satuanKonversiContainer">
                    {{-- Template satuan konversi akan ditambahkan di sini via JavaScript --}}
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="tambahSatuanKonversi()">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Satuan Konversi
                </button>

                <div class="alert alert-info">
                    <strong>Contoh:</strong> 
                    <ul class="mb-0">
                        <li>Satuan terkecil: <strong>tablet</strong> (harga Rp 500/tablet)</li>
                        <li>Konversi: <strong>Strip</strong> = 10 tablet, Harga Jual: Rp 5.000</li>
                        <li>Konversi: <strong>Box</strong> = 10 strip (100 tablet), Harga Jual: Rp 45.000</li>
                    </ul>
                </div>

                <div class="text-end">
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let satuanCounter = 0;

// Template row satuan konversi
function tambahSatuanKonversi() {
    satuanCounter++;
    const html = `
        <div class="card mb-2 satuan-row" id="satuan-${satuanCounter}">
            <div class="card-body p-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Nama Satuan</label>
                        <input type="text" name="satuan_konversi[${satuanCounter}][nama_satuan]" 
                               class="form-control form-control-sm" placeholder="Strip, Box, Botol">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Konversi</label>
                        <input type="number" name="satuan_konversi[${satuanCounter}][jumlah_konversi]" 
                               class="form-control form-control-sm" placeholder="10" min="1">
                        <small class="text-muted">Berapa satuan terkecil</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="satuan_konversi[${satuanCounter}][harga_jual]" 
                               class="form-control form-control-sm" placeholder="50000" step="0.01">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" name="satuan_konversi[${satuanCounter}][is_default]" 
                                   value="1" class="form-check-input" id="default-${satuanCounter}">
                            <label class="form-check-label" for="default-${satuanCounter}">
                                Default
                            </label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusSatuan(${satuanCounter})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#satuanKonversiContainer').append(html);
}

// Hapus satuan konversi
function hapusSatuan(id) {
    $(`#satuan-${id}`).remove();
}

// Auto tambah 1 row saat load (opsional)
$(document).ready(function() {
    // Jika ingin auto-load 1 row kosong, uncomment baris ini:
    // tambahSatuanKonversi();
});
</script>
@endpush
@endsection