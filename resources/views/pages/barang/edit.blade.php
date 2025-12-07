@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">Edit Data Barang</h1>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Barang</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('barang.update', $barang->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Detail Barang Utama -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="kode_barang">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" 
                                   id="kode_barang" name="kode_barang" 
                                   value="{{ old('kode_barang', $barang->kode_barang) }}" required>
                            @error('kode_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label for="nama_barang">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_barang') is-invalid @enderror" 
                                   id="nama_barang" name="nama_barang" 
                                   value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                            @error('nama_barang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="kategori">Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kategori') is-invalid @enderror" 
                                   id="kategori" name="kategori" 
                                   value="{{ old('kategori', $barang->kategori) }}" 
                                   list="kategoriList" required>
                            <datalist id="kategoriList">
                                <option value="Obat Bebas">
                                <option value="Obat Keras">
                                <option value="Vitamin">
                                <option value="Alat Kesehatan">
                            </datalist>
                            @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="satuan_dasar">Satuan Terkecil <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('satuan_dasar') is-invalid @enderror" 
                                   id="satuan_dasar" name="satuan_dasar" 
                                   value="{{ old('satuan_dasar', $barang->satuan_terkecil) }}" required>
                            <small class="text-muted">Satuan dasar untuk perhitungan stok</small>
                            @error('satuan_dasar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="lokasi_rak">Lokasi Rak</label>
                            <input type="text" class="form-control @error('lokasi_rak') is-invalid @enderror" 
                                   id="lokasi_rak" name="lokasi_rak" 
                                   value="{{ old('lokasi_rak', $barang->lokasi_rak) }}" 
                                   placeholder="Contoh: Rak A-1">
                            @error('lokasi_rak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="harga_beli">Harga Beli <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('harga_beli') is-invalid @enderror" 
                                   id="harga_beli" name="harga_beli" 
                                   value="{{ old('harga_beli', $barang->harga_beli) }}" required>
                            @error('harga_beli')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="harga_jual">Harga Jual (Satuan Terkecil) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('harga_jual') is-invalid @enderror" 
                                   id="harga_jual" name="harga_jual" 
                                   value="{{ old('harga_jual', $barang->harga_jual) }}" required>
                            <small class="text-muted">Harga per satuan terkecil</small>
                            @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="stok">Stok Saat Ini <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" 
                                   class="form-control @error('stok') is-invalid @enderror" 
                                   id="stok" name="stok" 
                                   value="{{ old('stok', $barang->stok) }}" required>
                            <small class="text-muted">Dalam satuan terkecil</small>
                            @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="stok_minimal">Stok Minimal <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" 
                                   class="form-control @error('stok_minimal') is-invalid @enderror" 
                                   id="stok_minimal" name="stok_minimal" 
                                   value="{{ old('stok_minimal', $barang->stok_minimal) }}" required>
                            @error('stok_minimal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                              id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <hr class="my-4">

                {{-- ✅ SECTION SATUAN KONVERSI --}}
                <h5 class="mb-3">
                    <i class="bi bi-box-seam me-2"></i>Satuan Konversi
                    <small class="text-muted">- Untuk penjualan dalam satuan lebih besar</small>
                </h5>
                <p class="text-muted small">
                    Tambahkan satuan lain beserta nilai konversinya dan harga jual per satuan. 
                    <strong>Contoh:</strong> 1 Strip = 10 tablet, Harga: Rp 5.000
                </p>
                
                <div id="konversi-container">
                    @php $k_index = 0; @endphp
                    {{-- Loop data satuan konversi yang sudah ada --}}
                    @foreach ($barang->satuanKonversi as $konversi)
                    <div class="konversi-row card mb-2" data-index="{{ $k_index }}">
                        <div class="card-body p-3">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Nama Satuan</label>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="satuan_konversi[{{ $k_index }}][nama_satuan]" 
                                           placeholder="Strip, Box, Botol" 
                                           value="{{ old('satuan_konversi.' . $k_index . '.nama_satuan', $konversi->nama_satuan) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jumlah Konversi</label>
                                    <input type="number" step="1" min="1" class="form-control form-control-sm" 
                                           name="satuan_konversi[{{ $k_index }}][jumlah_konversi]" 
                                           placeholder="10" 
                                           value="{{ old('satuan_konversi.' . $k_index . '.jumlah_konversi', $konversi->jumlah_konversi) }}">
                                    <small class="text-muted">Berapa satuan terkecil</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga Jual</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" 
                                           name="satuan_konversi[{{ $k_index }}][harga_jual]" 
                                           placeholder="50000" 
                                           value="{{ old('satuan_konversi.' . $k_index . '.harga_jual', $konversi->harga_jual) }}">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="satuan_konversi[{{ $k_index }}][is_default]" 
                                               value="1" class="form-check-input" 
                                               id="default-{{ $k_index }}"
                                               {{ old('satuan_konversi.' . $k_index . '.is_default', $konversi->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="default-{{ $k_index }}">
                                            Default
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-danger remove-konversi" title="Hapus Satuan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php $k_index++; @endphp
                    @endforeach

                    {{-- Error dari validasi --}}
                    @if ($errors->has('satuan_konversi') || $errors->has('satuan_konversi.*'))
                        <div class="alert alert-danger mt-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Terdapat kesalahan pada data satuan konversi yang Anda masukkan.
                        </div>
                    @endif
                </div>

                <button type="button" id="add-konversi-btn" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Satuan Konversi
                </button>

                <div class="alert alert-info mt-3">
                    <strong>Contoh Pengisian:</strong>
                    <ul class="mb-0">
                        <li>Satuan terkecil: <strong>tablet</strong> (harga Rp 500/tablet)</li>
                        <li>Konversi: <strong>Strip</strong> = 10 tablet, Harga Jual: Rp 5.000</li>
                        <li>Konversi: <strong>Box</strong> = 100 tablet (10 strip), Harga Jual: Rp 45.000</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Barang
                    </button>
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ✅ Ambil index terakhir dari data yang sudah ada
    let konversiIndex = {{ $barang->satuanKonversi->count() }}; 

    // ✅ Template baris konversi baru - SESUAI STRUKTUR BARU
    function getKonversiTemplate(index) {
        return `
            <div class="konversi-row card mb-2" data-index="${index}">
                <div class="card-body p-3">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Nama Satuan</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="satuan_konversi[${index}][nama_satuan]" 
                                   placeholder="Strip, Box, Botol">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jumlah Konversi</label>
                            <input type="number" step="1" min="1" class="form-control form-control-sm" 
                                   name="satuan_konversi[${index}][jumlah_konversi]" 
                                   placeholder="10">
                            <small class="text-muted">Berapa satuan terkecil</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" 
                                   name="satuan_konversi[${index}][harga_jual]" 
                                   placeholder="50000">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input type="checkbox" name="satuan_konversi[${index}][is_default]" 
                                       value="1" class="form-check-input" id="default-${index}">
                                <label class="form-check-label" for="default-${index}">
                                    Default
                                </label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger remove-konversi" title="Hapus Satuan">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('konversi-container');
        const addButton = document.getElementById('add-konversi-btn');

        // Event listener untuk tombol tambah satuan konversi
        addButton.addEventListener('click', function() {
            container.insertAdjacentHTML('beforeend', getKonversiTemplate(konversiIndex));
            konversiIndex++;
        });

        // Delegasi Event Listener untuk tombol hapus
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-konversi') || 
                e.target.closest('.remove-konversi')) {
                e.target.closest('.konversi-row').remove();
            }
        });
    });
</script>
@endpush
@endsection