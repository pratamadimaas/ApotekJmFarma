@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-building me-2" style="color: var(--primary-color);"></i>
                Edit Cabang: {{ $cabang->nama_cabang }}
            </h4>
            <p class="text-muted mb-0">Ubah detail data cabang apotek</p>
        </div>
        <a href="{{ route('cabang.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('cabang.update', $cabang->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Kolom Kode Cabang --}}
                <div class="mb-3">
                    <label for="kode_cabang" class="form-label">Kode Cabang <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('kode_cabang') is-invalid @enderror" 
                           id="kode_cabang" 
                           name="kode_cabang" 
                           value="{{ old('kode_cabang', $cabang->kode_cabang) }}"
                           placeholder="Contoh: C001" 
                           required>
                    @error('kode_cabang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom Nama Cabang --}}
                <div class="mb-3">
                    <label for="nama_cabang" class="form-label">Nama Cabang <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('nama_cabang') is-invalid @enderror" 
                           id="nama_cabang" 
                           name="nama_cabang" 
                           value="{{ old('nama_cabang', $cabang->nama_cabang) }}"
                           placeholder="Masukkan nama cabang" 
                           required>
                    @error('nama_cabang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom Alamat --}}
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" 
                              id="alamat" 
                              name="alamat" 
                              rows="3"
                              placeholder="Masukkan alamat lengkap cabang">{{ old('alamat', $cabang->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom Telepon --}}
                <div class="mb-3">
                    <label for="telepon" class="form-label">Telepon</label>
                    <input type="text" 
                           class="form-control @error('telepon') is-invalid @enderror" 
                           id="telepon" 
                           name="telepon" 
                           value="{{ old('telepon', $cabang->telepon) }}"
                           placeholder="Contoh: 081234567890">
                    @error('telepon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom Penanggung Jawab --}}
                <div class="mb-3">
                    <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                    <input type="text" 
                           class="form-control @error('penanggung_jawab') is-invalid @enderror" 
                           id="penanggung_jawab" 
                           name="penanggung_jawab" 
                           value="{{ old('penanggung_jawab', $cabang->penanggung_jawab) }}"
                           placeholder="Masukkan nama penanggung jawab">
                    @error('penanggung_jawab')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Kolom Status --}}
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="aktif" 
                           name="aktif" 
                           role="switch" 
                           value="1" 
                           {{ old('aktif', $cabang->aktif) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">Status Aktif</label>
                    <small class="text-muted d-block">Nonaktifkan untuk menonaktifkan cabang ini dari sistem.</small>
                    @error('aktif')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection