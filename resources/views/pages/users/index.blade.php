@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2>Daftar Pengguna Sistem</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Tambah User Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                    <button class="btn btn-secondary" type="submit">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'success' }}">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                <span class="badge bg-{{ $user->aktif ? 'success' : 'secondary' }}">{{ $user->aktif ? 'Aktif' : 'Non-aktif' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info me-2">Edit</a>
                                
                                {{-- Form Reset Password --}}
                                <form action="{{ route('users.resetPassword', $user->id) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Yakin reset password {{ $user->name }}? Password baru akan digenerate otomatis.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning me-2">Reset Pass</button>
                                </form>
                                
                                {{-- Form Hapus --}}
                                @if(Auth::user()->id !== $user->id)
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada user ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection