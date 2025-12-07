@extends('layouts.app')

@section('title', 'Daftar Supplier')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h2>Daftar Supplier</h2>
        <a href="{{ route('supplier.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Supplier
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Supplier</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $item)
                    <tr>
                        <td>{{ $item->nama_supplier }}</td>
                        <td>{{ $item->telepon }}</td>
                        <td>{{ $item->email ?? '-' }}</td>
                        <td>{{ $item->alamat }}</td>
                        <td>
                            <a href="{{ route('supplier.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('supplier.destroy', $item->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $suppliers->links() }}
        </div>
    </div>
</div>
@endsection