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

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            @if(auth()->user()->isSuperAdmin())
                                <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>
                                    Super Admin
                                </option>
                            @endif
                            <option value="admin_cabang" {{ old('role', $user->role) == 'admin_cabang' ? 'selected' : '' }}>
                                Admin Cabang
                            </option>
                            <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>
                                Kasir
                            </option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="cabang_container">
                        <label class="form-label">
                            Cabang <span class="text-danger" id="required_indicator">*</span>
                        </label>
                        <select name="cabang_id" id="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabang as $item)
                                <option value="{{ $item->id }}" {{ old('cabang_id', $user->cabang_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                        @error('cabang_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" id="cabang_help">Wajib diisi untuk Admin Cabang dan Kasir</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password (Kosongkan jika tidak ingin diubah)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Minimal 4 karakter</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Akun</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="aktif" value="0">
                            <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif" 
                                   {{ old('aktif', $user->aktif) ? 'checked' : '' }}>
                            <label class="form-check-label" for="aktif">
                                <span class="badge {{ old('aktif', $user->aktif) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ old('aktif', $user->aktif) ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary px-4">
                        <i class="fas fa-times me-2"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const cabangContainer = document.getElementById('cabang_container');
    const cabangSelect = document.getElementById('cabang_id');
    const requiredIndicator = document.getElementById('required_indicator');
    const cabangHelp = document.getElementById('cabang_help');
    const aktifCheckbox = document.getElementById('aktif');

    // Function untuk toggle cabang field
    function toggleCabang() {
        const role = roleSelect.value;
        
        if (role === 'super_admin') {
            // Super Admin tidak perlu cabang
            cabangContainer.style.display = 'none';
            cabangSelect.removeAttribute('required');
            cabangSelect.value = ''; // Reset nilai
        } else if (role === 'admin_cabang' || role === 'kasir') {
            // Admin Cabang dan Kasir wajib pilih cabang
            cabangContainer.style.display = 'block';
            cabangSelect.setAttribute('required', 'required');
            requiredIndicator.style.display = 'inline';
        } else {
            // Default: sembunyikan
            cabangContainer.style.display = 'none';
            cabangSelect.removeAttribute('required');
        }
    }

    // Function untuk update badge status aktif
    function updateStatusBadge() {
        const badge = aktifCheckbox.parentElement.querySelector('.badge');
        if (aktifCheckbox.checked) {
            badge.textContent = 'Aktif';
            badge.className = 'badge bg-success';
        } else {
            badge.textContent = 'Non-Aktif';
            badge.className = 'badge bg-secondary';
        }
    }

    // Event listeners
    roleSelect.addEventListener('change', toggleCabang);
    aktifCheckbox.addEventListener('change', updateStatusBadge);

    // Jalankan saat halaman dimuat
    toggleCabang();
});
</script>
@endpush
@endsection