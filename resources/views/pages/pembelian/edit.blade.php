@extends('layouts.app') 

@section('title', 'Edit Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="h3 mb-4 text-gray-800">Edit Pembelian #{{ $pembelian->nomor_pembelian }}</h1>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Pembelian</h6>
        </div>
        <div class="card-body">
            {{-- Menggunakan method PUT/PATCH untuk update --}}
            <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- 1. HEADER PEMBELIAN --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" 
                                value="{{ old('tanggal', $pembelian->tanggal_pembelian->format('Y-m-d')) }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_faktur">Nomor Faktur <span class="text-danger">*</span></label>
                            {{-- Field ini memetakan ke nomor_pembelian di Controller --}}
                            <input type="text" class="form-control @error('no_faktur') is-invalid @enderror" id="no_faktur" name="no_faktur" 
                                value="{{ old('no_faktur', $pembelian->nomor_pembelian) }}" required>
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
                                    <option value="{{ $supplier->id }}" 
                                        {{ old('supplier_id', $pembelian->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan', $pembelian->keterangan) }}</textarea>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- 2. DETAIL ITEM PEMBELIAN --}}
                <h5>Detail Barang yang Dibeli <span class="text-danger">*</span></h5>
                <div id="items-container">
                    @php $itemIndex = 0; @endphp
                    @foreach($pembelian->detailPembelian as $detail)
                    <div class="item-row row mb-2 border p-2" data-index="{{ $itemIndex }}">
                        <div class="col-md-4">
                            <label>Barang</label>
                            <select class="form-control barang-select" name="items[{{ $itemIndex }}][barang_id]" required>
                                <option value="">Pilih Barang</option>
                                @foreach($barang as $brg)
                                    <option value="{{ $brg->id }}" 
                                        {{ $detail->barang_id == $brg->id ? 'selected' : '' }}>
                                        {{ $brg->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Qty</label>
                            <input type="number" step="1" min="1" class="form-control qty-input" name="items[{{ $itemIndex }}][qty]" 
                                value="{{ old('items.'.$itemIndex.'.qty', $detail->jumlah ?? $detail->qty) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Satuan</label>
                            {{-- Opsi satuan akan diisi oleh JS/AJAX saat DOMContentLoaded --}}
                            <select class="form-control satuan-select" name="items[{{ $itemIndex }}][satuan]" required 
                                data-selected-satuan="{{ old('items.'.$itemIndex.'.satuan', $detail->satuan) }}">
                                <option value="{{ $detail->satuan }}">{{ $detail->satuan }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Harga Beli (Satuan)</label>
                            <input type="number" step="0.01" min="0" class="form-control harga-beli-input" name="items[{{ $itemIndex }}][harga_beli]" 
                                value="{{ old('items.'.$itemIndex.'.harga_beli', $detail->harga_beli) }}" required>
                        </div>
                        <div class="col-md-2"> 
                            <label>Harga Jual (Satuan)</label>
                            <input type="number" step="0.01" min="0" class="form-control harga-jual-input" name="items[{{ $itemIndex }}][harga_jual]" 
                                value="{{ old('items.'.$itemIndex.'.harga_jual', $detail->barang->harga_jual) }}" required>
                            </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-item" style="height: 38px;" {{ $itemIndex == 0 ? 'disabled' : '' }}>-</button>
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Tgl. Kadaluarsa (Opsional)</label>
                            <input type="date" class="form-control" name="items[{{ $itemIndex }}][tanggal_kadaluarsa]" 
                                value="{{ old('items.'.$itemIndex.'.tanggal_kadaluarsa', $detail->tanggal_kadaluarsa ? $detail->tanggal_kadaluarsa->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label>Subtotal (Otomatis)</label>
                            <input type="text" class="form-control subtotal-input" 
                                value="{{ old('items.'.$itemIndex.'.subtotal', $detail->subtotal) }}" readonly>
                        </div>
                    </div>
                    @php $itemIndex++; @endphp
                    @endforeach
                </div>
                
                <button type="button" id="add-item-btn" class="btn btn-success btn-sm mt-3">+ Tambah Barang</button>
                <hr>
                
                {{-- 3. TOTAL & DISKON --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_harga">Total Harga (Sebelum Diskon/PPN) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_harga') is-invalid @enderror" id="total_harga" name="total_harga" 
                                value="{{ old('total_harga', $pembelian->total_pembelian) }}" readonly required>
                            @error('total_harga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="diskon">Diskon (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('diskon') is-invalid @enderror" id="diskon" name="diskon" 
                                value="{{ old('diskon', $pembelian->diskon) }}">
                            @error('diskon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="ppn">PPN (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('ppn') is-invalid @enderror" id="ppn" name="ppn" 
                                value="{{ old('ppn', $pembelian->pajak) }}"> {{-- Mapping ke kolom DB 'pajak' --}}
                            @error('ppn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_bayar">Total Bayar Akhir <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_bayar') is-invalid @enderror" id="total_bayar" name="total_bayar" 
                                value="{{ old('total_bayar', $pembelian->grand_total) }}" readonly required>
                            @error('total_bayar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>


                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('pembelian.show', $pembelian->id) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Pastikan itemIndex diinisialisasi dengan nilai setelah perulangan PHP
    let itemIndex = {{ $itemIndex }}; 
    
    // Fungsi untuk mendapatkan template baris item baru (sama seperti create.blade.php)
    function getItemTemplate(index) {
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

        const selectedSatuan = selectElement.dataset.selectedSatuan || ''; // Ambil nilai satuan yang sudah dipilih (untuk mode edit)

        fetch(`/barang/${barangId}/satuan`) 
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Pilih Satuan</option>';
                
                // Tambahkan satuan dasar
                options += `<option value="${data.satuan_dasar}" ${selectedSatuan === data.satuan_dasar ? 'selected' : ''}>${data.satuan_dasar} (Dasar)</option>`;
                
                // Tambahkan satuan konversi
                data.konversi.forEach(konv => {
                    options += `<option value="${konv.satuan_konversi}" ${selectedSatuan === konv.satuan_konversi ? 'selected' : ''}>${konv.satuan_konversi}</option>`;
                });
                selectElement.innerHTML = options;
            })
            .catch(error => {
                console.error('Error fetching satuan:', error);
                selectElement.innerHTML = '<option value="">Error Ambil Satuan</option>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Inisialisasi Satuan untuk Item yang Sudah Ada
        document.querySelectorAll('.item-row').forEach(row => {
            const barangId = row.querySelector('.barang-select').value;
            const satuanSelect = row.querySelector('.satuan-select');
            
            if (barangId) {
                // Panggil fetchSatuanKonversi untuk mengisi dropdown dan memilih nilai lama
                fetchSatuanKonversi(barangId, satuanSelect);
            }
        });

        // 2. Event listener untuk tombol tambah barang (sudah benar)
        document.getElementById('add-item-btn').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            container.insertAdjacentHTML('beforeend', getItemTemplate(itemIndex));
            // Tambahkan event listener untuk tombol hapus pada item baru (jika diperlukan)
            itemIndex++;
        });

        // 3. Delegasi Event Listener untuk Aksi Item (Hapus)
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

        // 4. Delegasi Event Listener untuk Perubahan Item (select, qty, harga)
        document.getElementById('items-container').addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');

            if (e.target.classList.contains('barang-select')) {
                const barangId = e.target.value;
                const satuanSelect = row.querySelector('.satuan-select');
                
                // Hapus data-selected-satuan agar JS tidak otomatis memilih nilai lama dari data-attribute saat form disubmit ulang/diganti
                satuanSelect.removeAttribute('data-selected-satuan'); 
                
                fetchSatuanKonversi(barangId, satuanSelect);
                
                // Reset Qty/Harga saat barang berubah
                row.querySelector('.qty-input').value = 1;
                row.querySelector('.harga-beli-input').value = 0;
                calculateSubtotal(row); 

            } else if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });
        
        // 5. Delegasi Event Listener untuk Input Cepat (qty, harga)
        document.getElementById('items-container').addEventListener('input', function(e) {
            const row = e.target.closest('.item-row');
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });


        // 6. Event listener untuk Diskon dan PPN (sudah benar)
        document.getElementById('diskon').addEventListener('input', calculateGrandTotal);
        document.getElementById('ppn').addEventListener('input', calculateGrandTotal);

        // Hitung total awal saat halaman dimuat
        calculateGrandTotal();
    });

</script>
@endpush
@endsection