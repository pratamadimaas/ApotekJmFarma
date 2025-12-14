@extends('layouts.app')

@section('title', 'Tutup Shift')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Tutup Shift Kasir</h5>
                </div>
                <div class="card-body">
                    <!-- Info Dasar Shift -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Kasir</small>
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Waktu Buka</small>
                                <div class="fw-bold">{{ $shift->waktu_buka->format('d/m/Y H:i:s') }}</div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Modal Awal</small>
                                <div class="fw-bold text-primary">Rp {{ number_format($shift->saldo_awal, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Durasi Shift</small>
                                <div class="fw-bold">{{ $shift->waktu_buka->diffForHumans(null, true) }}</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Form Input Uang Fisik -->
                    <form method="POST" action="{{ route('shift.tutup.store') }}" id="formTutupShift">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-cash-stack text-success me-2"></i>
                                Hitung Uang Fisik di Laci <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="uang_fisik" 
                                   class="form-control form-control-lg @error('uang_fisik') is-invalid @enderror" 
                                   placeholder="Masukkan total uang di laci"
                                   required 
                                   autofocus
                                   step="0.01"
                                   min="0">
                            @error('uang_fisik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Hitung semua uang tunai yang ada di laci kasir, termasuk modal awal
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-pencil-square text-warning me-2"></i>
                                Catatan Pengeluaran (Opsional)
                            </label>
                            <textarea name="catatan" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Contoh: Beli air minum Rp 50.000, Bayar listrik Rp 200.000, dll."></textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Catat jika ada pengeluaran diluar penjualan (beli air, bayar listrik, dll) yang menyebabkan uang berkurang
                            </small>
                        </div>

                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Setelah submit, sistem akan menghitung selisih antara uang fisik dengan yang seharusnya ada di laci. Pastikan perhitungan sudah benar!
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-lock me-2"></i>Tutup Shift & Lihat Hasil
                            </button>
                            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="text-info mb-2"><i class="bi bi-lightbulb me-2"></i>Tips Tutup Shift:</h6>
                    <ul class="mb-0 small">
                        <li>Pastikan semua transaksi hari ini sudah tercatat</li>
                        <li>Hitung uang fisik dengan teliti, termasuk modal awal</li>
                        <li>Catat pengeluaran diluar penjualan di kolom catatan</li>
                        <li>Selisih akan otomatis dihitung setelah submit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('formTutupShift').addEventListener('submit', function(e) {
    const uangFisik = document.querySelector('input[name="uang_fisik"]').value;
    
    if (!uangFisik || parseFloat(uangFisik) < 0) {
        e.preventDefault();
        alert('Mohon masukkan jumlah uang fisik yang valid!');
        return false;
    }
    
    if (!confirm('Apakah Anda yakin ingin menutup shift? Pastikan perhitungan uang sudah benar!')) {
        e.preventDefault();
        return false;
    }
});

// Format input number dengan thousand separator
document.querySelector('input[name="uang_fisik"]').addEventListener('blur', function(e) {
    if (this.value) {
        const value = parseFloat(this.value);
        if (!isNaN(value)) {
            // Tampilkan format di bawah input
            const formatted = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
            
            // Update atau buat element preview
            let preview = this.parentElement.querySelector('.money-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'money-preview text-success fw-bold mt-1';
                this.parentElement.insertBefore(preview, this.nextSibling);
            }
            preview.textContent = formatted;
        }
    }
});
</script>
@endpush

@endsection