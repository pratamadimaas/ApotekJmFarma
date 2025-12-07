@extends('layouts.app')

@section('title', 'Buka Shift')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Buka Shift Kasir</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('shift.buka.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label>Kasir</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Waktu Buka</label>
                            <input type="text" class="form-control" value="{{ now()->format('d/m/Y H:i:s') }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Modal Awal <span class="text-danger">*</span></label>
                            <input type="number" name="modal_awal" class="form-control" required autofocus>
                            <small class="text-muted">Jumlah uang tunai di laci kasir</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-unlock me-2"></i>Buka Shift
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection