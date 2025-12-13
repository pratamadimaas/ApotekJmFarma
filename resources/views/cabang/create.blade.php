@extends('layouts.app')

@section('title', isset($cabang) ? 'Edit Cabang' : 'Tambah Cabang')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">
            <i class="bi bi-building me-2" style="color: var(--primary-color);"></i>
            {{ isset($cabang) ? 'Edit Cabang' : 'Tambah Cabang Baru' }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('cabang.index') }}">Cabang</a></li>
                <li class="breadcrumb-item active">{{ isset($cabang) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ isset($cabang) ? route('cabang.update', $cabang->id) : route('cabang.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($cabang))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kode Cabang <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="kode_cabang" 
                                       class="form-control @error('kode_cabang') is-invalid @enderror"
                                       value="{{ old('kode_cabang', $cabang->kode_cabang ?? '') }}"
                                       placeholder="Contoh: CB001"
                                       required>
                                @error('kode_cabang')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Kode unik untuk identifikasi cabang</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Cabang <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="nama_cabang" 
                                       class="form-control @error('nama_cabang') is-invalid @enderror"
                                       value="{{ old('nama_cabang', $cabang->nama_cabang ?? '') }}"
                                       placeholder="Contoh: Cabang Makassar Utara"
                                       required>
                                @error('nama_cabang')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Alamat</label>
                                <textarea name="alamat" 
                                          class="form-control @error('alamat') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Alamat lengkap cabang">{{ old('alamat', $cabang->alamat ?? '') }}</textarea>
                                @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Telepon</label>
                                <input type="text" 
                                       name="telepon" 
                                       class="form-control @error('telepon') is-invalid @enderror"
                                       value="{{ old('telepon', $cabang->telepon ?? '') }}"
                                       placeholder="0411-xxxxx">
                                @error('telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $cabang->email ?? '') }}"
                                       placeholder="cabang@apotek.com">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Penanggung Jawab</label>
                                <input type="text" 
                                       name="penanggung_jawab" 
                                       class="form-control @error('penanggung_jawab') is-invalid @enderror"
                                       value="{{ old('penanggung_jawab', $cabang->penanggung_jawab ?? '') }}"
                                       placeholder="Nama Apoteker">
                                @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="aktif" class="form-select @error('aktif') is-invalid @enderror">
                                    <option value="1" {{ old('aktif', $cabang->aktif ?? 1) == 1 ? 'selected' : '' }}>
                                        Aktif
                                    </option>
                                    <option value="0" {{ old('aktif', $cabang->aktif ?? 1) == 0 ? 'selected' : '' }}>
                                        Nonaktif
                                    </option>
                                </select>
                                @error('aktif')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ isset($cabang) ? 'Update Cabang' : 'Simpan Cabang' }}
                            </button>
                            <a href="{{ route('cabang.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-info-circle me-2"></i>Informasi
                    </h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>
                            Kode cabang harus unik
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>
                            Nama cabang digunakan untuk identifikasi
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>
                            Status nonaktif akan menonaktifkan semua user di cabang ini
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check text-success me-2"></i>
                            Cabang dapat dihapus jika belum memiliki user
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection