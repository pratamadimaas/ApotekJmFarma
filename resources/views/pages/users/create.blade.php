@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2>Tambah User Baru</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-2"></i> Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-plus me-2"></i> Formulir User Baru
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="contoh@email.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">-- Pilih Role --</option>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>
                                        🔑 Super Admin
                                    </option>
                                @endif
                                <option value="admin_cabang" {{ old('role') == 'admin_cabang' ? 'selected' : '' }}>
                                    👤 Admin Cabang
                                </option>
                                <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>
                                    💼 Kasir
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3" id="cabang-wrapper">
                            <label for="cabang_id" class="form-label">
                                Cabang <span class="text-danger" id="required-indicator">*</span>
                            </label>
                            <select class="form-select @error('cabang_id') is-invalid @enderror" 
                                    id="cabang_id" 
                                    name="cabang_id">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($cabang as $c)
                                    <option value="{{ $c->id }}" {{ old('cabang_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cabang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Wajib diisi untuk Admin Cabang dan Kasir
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   minlength="4"
                                   placeholder="Kosongkan untuk auto-generate">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-key"></i> Minimal 4 karakter. Kosongkan untuk generate otomatis (5 karakter random).
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="aktif" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('aktif') is-invalid @enderror" 
                                    id="aktif" 
                                    name="aktif" 
                                    required>
                                <option value="1" {{ old('aktif', 1) == 1 ? 'selected' : '' }}>
                                    ✅ Aktif
                                </option>
                                <option value="0" {{ old('aktif') == 0 ? 'selected' : '' }}>
                                    ❌ Non-aktif
                                </option>
                            </select>
                            @error('aktif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
                        <i class="fas fa-times me-2"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ✅ Menggunakan Vanilla JavaScript (tidak perlu jQuery)
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const cabangWrapper = document.getElementById('cabang-wrapper');
    const cabangSelect = document.getElementById('cabang_id');
    const requiredIndicator = document.getElementById('required-indicator');
    
    // Function untuk toggle cabang field
    function toggleCabangField() {
        const role = roleSelect.value;
        
        if (role === 'super_admin') {
            // Super Admin tidak perlu cabang
            cabangWrapper.style.display = 'none';
            cabangSelect.removeAttribute('required');
            cabangSelect.value = ''; // Reset nilai
        } else if (role === 'admin_cabang' || role === 'kasir') {
            // Admin Cabang dan Kasir wajib pilih cabang
            cabangWrapper.style.display = 'block';
            cabangSelect.setAttribute('required', 'required');
            requiredIndicator.style.display = 'inline';
        } else {
            // Default: sembunyikan
            cabangWrapper.style.display = 'none';
            cabangSelect.removeAttribute('required');
        }
    }
    
    // Trigger saat halaman load
    toggleCabangField();
    
    // Trigger saat role berubah
    roleSelect.addEventListener('change', toggleCabangField);
});
</script>
@endpush
@endsection