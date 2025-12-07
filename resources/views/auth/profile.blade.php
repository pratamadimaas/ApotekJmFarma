@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">
                Profil Pengguna: **{{ $user->name }}**
            </h1>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        
        {{-- Kolom Kiri: Form Ubah Profil (Nama & Email) --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ubah Informasi Profil</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Ubah Password --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Ubah Password</h6>
                </div>
                <div class="card-body">
                    {{-- Form ini akan menggunakan route terpisah jika ada, atau menggunakan AuthController --}}
                    {{-- Asumsi: Menggunakan route terpisah yang kita buat di UserController (change.password.form/change.password) --}}
                    <form action="{{ route('change.password') }}" method="POST">
                        @csrf
                        
                        <p class="small text-muted">Untuk keamanan, silakan masukkan password Anda saat ini.</p>

                        <div class="form-group">
                            <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" minlength="6" required>
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning mt-3">Ganti Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection