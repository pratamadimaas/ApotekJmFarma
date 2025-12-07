@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Supplier Baru</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('supplier.store') }}">
                @csrf
                
                <div class="mb-3">
                    <label>Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="nama_supplier" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="telepon" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Alamat <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label>Kontak Person</label>
                    <input type="text" name="kontak_person" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                </div>

                <div class="text-end">
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection