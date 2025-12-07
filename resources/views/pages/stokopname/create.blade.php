@extends('layouts.app')

@section('title', 'Mulai Stok Opname Baru')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Mulai Stok Opname (SO) Baru</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Pencatatan Stok Fisik</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('stokopname.store') }}" method="POST">
                @csrf

                {{-- Header Sesi SO --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Stok Opname <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" 
                                value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="keterangan">Keterangan Sesi (Opsional)</label>
                            <input type="text" class="form-control" id="keterangan" name="keterangan" 
                                value="{{ old('keterangan') }}" placeholder="Contoh: SO Akhir Bulan November">
                        </div>
                    </div>
                </div>

                <h5 class="mt-4">Daftar Barang untuk Pencatatan</h5>
                
                <p class="text-muted small">Catat stok fisik (jumlah aktual) di kolom Stok Fisik untuk membandingkannya dengan Stok Sistem.</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stokOpnameTable">
                        <thead>
                            <tr>
                                <th class="text-nowrap">Kode / Nama Barang</th>
                                
                                {{-- ✅ KOLOM BARU: LOKASI RAK --}}
                                <th class="text-center text-nowrap">Lokasi Rak</th> 
                                
                                <th class="text-center text-nowrap">Satuan Terkecil</th>
                                <th class="text-center text-nowrap">Stok Sistem</th>
                                <th class="text-center text-nowrap">Stok Fisik <span class="text-danger">*</span></th>
                                <th class="text-center text-nowrap">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barang as $index => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][barang_id]" value="{{ $item->id }}">
                                    <strong>{{ $item->nama_barang }}</strong>
                                    <small class="d-block text-muted">{{ $item->kode_barang }}</small>
                                </td>
                                
                                {{-- ✅ TAMPILKAN LOKASI RAK --}}
                                <td class="text-center">
                                    <strong class="text-primary">{{ $item->lokasi_rak ?? '-' }}</strong>
                                </td>
                                
                                <td class="text-center">
                                    <p class="mb-0">{{ $item->satuan_terkecil }}</p>
                                </td>
                                <td class="text-center">
                                    <p class="stok-sistem mb-0">{{ $item->stok }}</p>
                                </td>
                                <td>
                                    <input type="number" min="0" 
                                           class="form-control form-control-sm text-center stok-fisik-input @error('items.'.$index.'.stok_fisik') is-invalid @enderror"
                                           name="items[{{ $index }}][stok_fisik]" 
                                           value="{{ old('items.'.$index.'.stok_fisik', $item->stok) }}" 
                                           data-stok-sistem="{{ $item->stok }}"
                                           required>
                                    @error('items.'.$index.'.stok_fisik')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                                <td class="text-center">
                                    <p class="selisih-output mb-0 text-muted">0</p>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada barang aktif dalam inventori.</td> {{-- Colspan disesuaikan --}}
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success" id="btnSubmitSO">
                        <i class="bi bi-save me-2"></i>Selesaikan & Update Stok
                    </button>
                    <a href="{{ route('stokopname.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('stokOpnameTable');

        // Fungsi untuk menghitung selisih per baris
        function calculateSelisih(row) {
            // Ambil stok sistem dari data attribute di input
            const stokSistem = parseInt(row.querySelector('.stok-fisik-input').dataset.stokSistem) || 0;
            const stokFisikInput = row.querySelector('.stok-fisik-input');
            const selisihOutput = row.querySelector('.selisih-output');
            
            // Mengambil nilai stok fisik (jika kosong, dianggap 0)
            let stokFisik = parseInt(stokFisikInput.value) || 0;
            if (stokFisikInput.value === '') {
                 stokFisik = 0;
            }

            const selisih = stokFisik - stokSistem;
            selisihOutput.textContent = selisih;
            
            // Memberi warna pada selisih
            // Hapus semua kelas warna sebelum menambahkan yang baru
            selisihOutput.classList.remove('text-muted', 'text-danger', 'text-success', 'font-weight-bold');

            if (selisih > 0) {
                selisihOutput.classList.add('text-success', 'font-weight-bold');
            } else if (selisih < 0) {
                selisihOutput.classList.add('text-danger', 'font-weight-bold');
            } else {
                selisihOutput.classList.add('text-muted');
            }
        }

        // Jalankan perhitungan awal untuk semua baris (untuk data lama/old input)
        table.querySelectorAll('tbody tr').forEach(row => {
            calculateSelisih(row);
        });

        // Event listener untuk menghitung selisih saat input berubah
        table.addEventListener('input', function(e) {
            if (e.target.classList.contains('stok-fisik-input')) {
                calculateSelisih(e.target.closest('tr'));
            }
        });
        
        // Opsional: Cek minimal harus ada data sebelum submit (sudah dihandle oleh validasi backend)
        document.getElementById('btnSubmitSO').addEventListener('click', function(e) {
            if (table.querySelectorAll('tbody tr').length === 0) {
                alert('Tidak ada barang untuk dicatat.');
                e.preventDefault();
            }
        });
    });
</script>
@endpush