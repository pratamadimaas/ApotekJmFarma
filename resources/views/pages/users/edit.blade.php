@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2>Edit Pengguna: {{ $user->name }}</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin_cabang" {{ old('role', $user->role) == 'admin_cabang' ? 'selected' : '' }}>Admin Cabang</option>
                            <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" id="cabang_container">
                        <label class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select name="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabang as $item)
                                <option value="{{ $item->id }}" {{ old('cabang_id', $user->cabang_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password (Kosongkan jika tidak ingin diubah)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Akun</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="aktif" value="0">
                            <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $user->aktif) ? 'checked' : '' }}>
                            <label class="form-check-label" for="aktif">Aktif</label>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script untuk menyembunyikan pilihan cabang jika Role adalah Super Admin --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const cabangContainer = document.getElementById('cabang_container');

        function toggleCabang() {
            if (roleSelect.value === 'super_admin') {
                cabangContainer.style.display = 'none';
            } else {
                cabangContainer.style.display = 'block';
            }
        }

        roleSelect.addEventListener('change', toggleCabang);
        toggleCabang(); // Jalankan saat halaman dimuat
    });
</script>
@endsection