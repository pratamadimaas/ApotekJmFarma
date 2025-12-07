@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Ubah Password Anda</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('change.password') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" minlength="8" required>
                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection