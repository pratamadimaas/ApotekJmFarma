@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama Anda --}}

@section('title', 'Edit Supplier')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">Edit Data Supplier</h1>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Supplier</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Laravel memerlukan directive ini untuk metode PUT/PATCH --}}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_supplier">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_supplier') is-invalid @enderror" id="nama_supplier" name="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
                            @error('nama_supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telepon">Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $supplier->telepon) }}" required>
                            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $supplier->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kontak_person">Kontak Person (Opsional)</label>
                            <input type="text" class="form-control @error('kontak_person') is-invalid @enderror" id="kontak_person" name="kontak_person" value="{{ old('kontak_person', $supplier->kontak_person) }}">
                            @error('kontak_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $supplier->alamat) }}</textarea>
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan (Opsional)</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $supplier->keterangan) }}</textarea>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Data</button>
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection