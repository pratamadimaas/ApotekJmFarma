@extends('layouts.app') 

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">Tambah Pembelian Baru</h1>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Pembelian</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('pembelian.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_faktur">Nomor Faktur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('no_faktur') is-invalid @enderror" id="no_faktur" name="no_faktur" value="{{ old('no_faktur') }}" required>
                            @error('no_faktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_id">Supplier <span class="text-danger">*</span></label>
                            <select class="form-control @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }} {{-- ✅ KOREKSI: Menggunakan nama_supplier --}}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <h5>Detail Barang yang Dibeli <span class="text-danger">*</span></h5>
                <div id="items-container">
                    {{-- Baris Item Pertama (Template) --}}
                    <div class="item-row row mb-2 border p-2">
                        <div class="col-md-4">
                            <label>Barang</label>
                            <select class="form-control barang-select" name="items[0][barang_id]" required>
                                <option value="">Pilih Barang</option>
                                @foreach($barang as $brg)
                                    <option value="{{ $brg->id }}">{{ $brg->nama_barang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Qty</label>
                            <input type="number" step="1" min="1" class="form-control qty-input" name="items[0][qty]" value="1" required>
                        </div>
                        <div class="col-md-2">
                            <label>Satuan</label>
                            <select class="form-control satuan-select" name="items[0][satuan]" required>
                                <option value="">Pilih Satuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Harga Beli (Satuan)</label>
                            <input type="number" step="0.01" min="0" class="form-control harga-beli-input" name="items[0][harga_beli]" value="0" required>
                        </div>
                        {{-- 🟢 INPUT BARU: HARGA JUAL --}}
                        <div class="col-md-2">
                            <label>Harga Jual (Satuan)</label>
                            <input type="number" step="0.01" min="0" class="form-control harga-jual-input" name="items[0][harga_jual]" value="0" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end"> {{-- DIUBAH: 1 -> 2 UNTUK MUDAHKAN TATA LETAK --}}
                            <button type="button" class="btn btn-danger btn-sm remove-item" style="height: 38px;" disabled>-</button>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Tgl. Kadaluarsa (Opsional)</label>
                            <input type="date" class="form-control" name="items[0][tanggal_kadaluarsa]">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Subtotal (Otomatis)</label>
                            <input type="text" class="form-control subtotal-input" value="0" readonly>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-item-btn" class="btn btn-success btn-sm mt-3">+ Tambah Barang</button>
                <hr>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_harga">Total Harga (Sebelum Diskon/PPN) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_harga') is-invalid @enderror" id="total_harga" name="total_harga" value="{{ old('total_harga', 0) }}" readonly required>
                            @error('total_harga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="diskon">Diskon (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('diskon') is-invalid @enderror" id="diskon" name="diskon" value="{{ old('diskon', 0) }}">
                            @error('diskon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="ppn">PPN (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('ppn') is-invalid @enderror" id="ppn" name="ppn" value="{{ old('ppn', 0) }}">
                            @error('ppn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_bayar">Total Bayar Akhir <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_bayar') is-invalid @enderror" id="total_bayar" name="total_bayar" value="{{ old('total_bayar', 0) }}" readonly required>
                            @error('total_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let itemIndex = 0;

    // Fungsi untuk mendapatkan template baris item baru
    function getItemTemplate(index) {
        // Mendapatkan opsi barang dari template blade yang sudah dirender di halaman
        let barangOptions = `
            <option value="">Pilih Barang</option>
            @foreach($barang as $brg)
                <option value="{{ $brg->id }}">{{ $brg->nama_barang }}</option>
            @endforeach
        `;

        return `
            <div class="item-row row mb-2 border p-2" data-index="${index}">
                <div class="col-md-4">
                    <label>Barang</label>
                    <select class="form-control barang-select" name="items[${index}][barang_id]" required>
                        ${barangOptions}
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Qty</label>
                    <input type="number" step="1" min="1" class="form-control qty-input" name="items[${index}][qty]" value="1" required>
                </div>
                <div class="col-md-2">
                    <label>Satuan</label>
                    <select class="form-control satuan-select" name="items[${index}][satuan]" required>
                        <option value="">Pilih Satuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Harga Beli (Satuan)</label>
                    <input type="number" step="0.01" min="0" class="form-control harga-beli-input" name="items[${index}][harga_beli]" value="0" required>
                </div>
                <div class="col-md-2">
                    <label>Harga Jual (Satuan)</label>
                    <input type="number" step="0.01" min="0" class="form-control harga-jual-input" name="items[${index}][harga_jual]" value="0" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-item" style="height: 38px;">-</button>
                </div>
                <div class="col-md-4 mt-2">
                    <label>Tgl. Kadaluarsa (Opsional)</label>
                    <input type="date" class="form-control" name="items[${index}][tanggal_kadaluarsa]">
                </div>
                <div class="col-md-4 mt-2">
                    <label>Subtotal (Otomatis)</label>
                    <input type="text" class="form-control subtotal-input" value="0" readonly>
                </div>
            </div>
        `;
    }

    // Fungsi untuk menghitung total harga per baris
    function calculateSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const hargaBeli = parseFloat(row.querySelector('.harga-beli-input').value) || 0;
        const subtotal = (qty * hargaBeli).toFixed(2);
        row.querySelector('.subtotal-input').value = subtotal;
        calculateGrandTotal();
    }

    // Fungsi untuk menghitung grand total pembelian
    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const subtotal = parseFloat(row.querySelector('.subtotal-input').value) || 0;
            grandTotal += subtotal;
        });

        const diskonPersen = parseFloat(document.getElementById('diskon').value) || 0;
        const ppnPersen = parseFloat(document.getElementById('ppn').value) || 0;

        document.getElementById('total_harga').value = grandTotal.toFixed(2);

        let totalBayar = grandTotal;

        // Hitung Diskon
        const nilaiDiskon = totalBayar * (diskonPersen / 100);
        totalBayar -= nilaiDiskon;
        
        // Hitung PPN
        const nilaiPpn = totalBayar * (ppnPersen / 100);
        totalBayar += nilaiPpn;

        document.getElementById('total_bayar').value = Math.max(0, totalBayar).toFixed(2);
    }

    // Fungsi AJAX untuk mendapatkan satuan konversi barang
    function fetchSatuanKonversi(barangId, selectElement) {
        if (!barangId) {
            selectElement.innerHTML = '<option value="">Pilih Satuan</option>';
            return;
        }

        fetch(`/barang/${barangId}/satuan`) 
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Pilih Satuan</option>';
                // Tambahkan satuan dasar
                options += `<option value="${data.satuan_dasar}">${data.satuan_dasar} (Dasar)</option>`;
                
                // Tambahkan satuan konversi
                data.konversi.forEach(konv => {
                    options += `<option value="${konv.satuan_konversi}">${konv.satuan_konversi}</option>`;
                });
                selectElement.innerHTML = options;
            })
            .catch(error => {
                console.error('Error fetching satuan:', error);
                selectElement.innerHTML = '<option value="">Error Ambil Satuan</option>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- Initialization and Setup ---
        
        // Jika baris pertama ada, pastikan itemIndex diinisialisasi untuk baris berikutnya
        const initialRow = document.querySelector('.item-row');
        if (initialRow) {
            itemIndex = 0; // Baris pertama diinisialisasi 0
            
            // Ambil Satuan untuk baris pertama (jika barang sudah dipilih saat old input)
            const initialBarangSelect = initialRow.querySelector('.barang-select');
            const initialBarangId = initialBarangSelect.value;
            const initialSatuanSelect = initialRow.querySelector('.satuan-select');
            if (initialBarangId) {
                fetchSatuanKonversi(initialBarangId, initialSatuanSelect);
            }
        }
        itemIndex++; // Persiapan index untuk baris baru (mulai dari 1)

        // --- Event Listeners ---

        // 1. Tambah Barang
        document.getElementById('add-item-btn').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            container.insertAdjacentHTML('beforeend', getItemTemplate(itemIndex));
            itemIndex++;
        });

        // 2. Delegasi Event Listener untuk Aksi Item
        document.getElementById('items-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const row = e.target.closest('.item-row');
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateGrandTotal(); 
                } else {
                    alert('Minimal harus ada 1 barang dalam pembelian.');
                }
            }
        });

        // 3. Delegasi Event Listener untuk Perubahan Item (select, qty, harga)
        document.getElementById('items-container').addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');

            if (e.target.classList.contains('barang-select')) {
                const barangId = e.target.value;
                const satuanSelect = row.querySelector('.satuan-select');
                fetchSatuanKonversi(barangId, satuanSelect);
                
                // Reset Qty/Harga saat barang berubah
                row.querySelector('.qty-input').value = 1;
                row.querySelector('.harga-beli-input').value = 0;
                row.querySelector('.harga-jual-input').value = 0;
                calculateSubtotal(row); 

            } else if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });
        
        // 4. Delegasi Event Listener untuk Input Cepat (qty, harga)
        document.getElementById('items-container').addEventListener('input', function(e) {
            const row = e.target.closest('.item-row');
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });


        // 5. Event listener untuk Diskon dan PPN
        document.getElementById('diskon').addEventListener('input', calculateGrandTotal);
        document.getElementById('ppn').addEventListener('input', calculateGrandTotal);

        // Hitung total awal saat halaman dimuat
        calculateGrandTotal();
    });

</script>
@endpush
@endsection