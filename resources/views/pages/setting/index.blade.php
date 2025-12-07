@extends('layouts.app')

@section('title', 'Setting Aplikasi')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Pengaturan Aplikasi</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Dasar Apotek</h6>
        </div>
        <div class="card-body">
            {{-- Menggunakan route settings.update dengan method POST --}}
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                {{-- Note: Walaupun logikanya update, kita menggunakan POST karena form tidak mendukung PUT/PATCH secara native tanpa @method() --}}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_apotek">Nama Apotek <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_apotek') is-invalid @enderror" id="nama_apotek" name="nama_apotek" 
                                value="{{ old('nama_apotek', $settings['nama_apotek'] ?? '') }}" required>
                            @error('nama_apotek')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telepon">Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" 
                                value="{{ old('telepon', $settings['telepon'] ?? '') }}" required>
                            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                                value="{{ old('email', $settings['email'] ?? '') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="alamat">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $settings['alamat'] ?? '') }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                
                <hr>

                {{-- Tambahkan field setting lain di sini (misal: logo, kebijakan, dll) --}}

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection