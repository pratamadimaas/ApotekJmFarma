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
            <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST">
                @csrf
                @method('PUT')

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

                <h5>Detail Barang yang Dibeli <span class="text-danger">*</span></h5>
                <div id="items-container">
                    @php $itemIndex = 0; @endphp
                    @foreach($pembelian->detailPembelian as $detail)
                    <div class="item-row border rounded p-3 mb-3 bg-light" data-index="{{ $itemIndex }}" data-barang-id="{{ $detail->barang_id }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Barang <span class="text-danger">*</span></label>
                                <select class="form-control barang-select" name="items[{{ $itemIndex }}][barang_id]" required>
                                    <option value="">Pilih Barang</option>
                                    @foreach($barang as $brg)
                                        <option value="{{ $brg->id }}" 
                                            data-satuan-terkecil="{{ $brg->satuan_terkecil }}"
                                            {{ $detail->barang_id == $brg->id ? 'selected' : '' }}>
                                            {{ $brg->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Qty <span class="text-danger">*</span></label>
                                <input type="number" step="1" min="1" class="form-control qty-input" name="items[{{ $itemIndex }}][qty]" 
                                    value="{{ old('items.'.$itemIndex.'.qty', $detail->jumlah ?? $detail->qty) }}" required>
                            </div>
                            <div class="col-md-2">
                                <label>Satuan <span class="text-danger">*</span></label>
                                <select class="form-control satuan-select" name="items[{{ $itemIndex }}][satuan]" required 
                                    data-selected-satuan="{{ old('items.'.$itemIndex.'.satuan', $detail->satuan) }}">
                                    <option value="{{ $detail->satuan }}">{{ $detail->satuan }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Harga Beli <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control harga-beli-input" name="items[{{ $itemIndex }}][harga_beli]" 
                                    value="{{ old('items.'.$itemIndex.'.harga_beli', $detail->harga_beli) }}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-item w-100" {{ $itemIndex == 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>Tgl. Kadaluarsa</label>
                                <input type="date" class="form-control" name="items[{{ $itemIndex }}][tanggal_kadaluarsa]" 
                                    value="{{ old('items.'.$itemIndex.'.tanggal_kadaluarsa', $detail->tanggal_kadaluarsa ? $detail->tanggal_kadaluarsa->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Subtotal (Otomatis)</label>
                                <input type="text" class="form-control subtotal-input" 
                                    value="{{ old('items.'.$itemIndex.'.subtotal', $detail->subtotal) }}" readonly>
                            </div>
                        </div>

                        {{-- 🔥 SECTION SATUAN KONVERSI --}}
                        <div class="satuan-konversi-section mt-3 p-3 border rounded bg-white" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="bi bi-diagram-3"></i> Kelola Satuan & Harga Jual
                                </h6>
                                <button type="button" class="btn btn-success btn-sm add-satuan-btn">
                                    <i class="bi bi-plus-circle"></i> Tambah Satuan Baru
                                </button>
                            </div>
                            
                            <div class="satuan-list">
                                {{-- Will be populated by JS --}}
                            </div>
                        </div>
                    </div>
                    @php $itemIndex++; @endphp
                    @endforeach
                </div>
                
                <button type="button" id="add-item-btn" class="btn btn-success btn-sm mt-3">
                    <i class="bi bi-plus-circle"></i> Tambah Barang
                </button>
                <hr>
                
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
                                value="{{ old('ppn', $pembelian->pajak) }}">
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
                    <button type="submit" class="btn btn-primary" name="status" value="approved">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan & Approve
                    </button>
                    <button type="submit" class="btn btn-warning" name="status" value="pending">
                        <i class="bi bi-clock-history"></i> Simpan sebagai Pending
                    </button>
                    <a href="{{ route('pembelian.show', $pembelian->id) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .satuan-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .satuan-item:hover {
        background: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .satuan-item.is-default {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    
    .badge-satuan {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    // SAME SCRIPT AS CREATE, WITH EDIT-SPECIFIC INITIALIZATION
    let itemIndex = {{ $itemIndex }}; 
    
    function getSatuanItemTemplate(itemIdx, satuanData, isNew = false) {
        const isDefault = satuanData.is_default || false;
        const satuanName = satuanData.nama_satuan || '';
        const konversi = satuanData.jumlah_konversi || 1;
        const hargaJual = satuanData.harga_jual || 0;
        const satuanId = satuanData.id || '';
        
        return `
            <div class="satuan-item ${isDefault ? 'is-default' : ''}" data-satuan-name="${satuanName}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Nama Satuan</label>
                        <input type="text" 
                               class="form-control form-control-sm satuan-nama-input" 
                               name="items[${itemIdx}][satuan_konversi][${satuanName || 'new'}][nama_satuan]" 
                               value="${satuanName}" 
                               ${!isNew ? 'readonly' : ''} 
                               required>
                        ${satuanId ? `<input type="hidden" name="items[${itemIdx}][satuan_konversi][${satuanName}][id]" value="${satuanId}">` : ''}
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Konversi</label>
                        <input type="number" 
                               step="1" 
                               min="1" 
                               class="form-control form-control-sm konversi-input" 
                               name="items[${itemIdx}][satuan_konversi][${satuanName || 'new'}][jumlah_konversi]" 
                               value="${konversi}" 
                               ${isDefault ? 'readonly' : ''} 
                               required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Harga Jual</label>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               class="form-control form-control-sm harga-jual-satuan-input" 
                               name="items[${itemIdx}][satuan_konversi][${satuanName || 'new'}][harga_jual]" 
                               value="${hargaJual}" 
                               required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Default</label>
                        <div class="form-check">
                            <input class="form-check-input is-default-check" 
                                   type="checkbox" 
                                   name="items[${itemIdx}][satuan_konversi][${satuanName || 'new'}][is_default]" 
                                   value="1" 
                                   ${isDefault ? 'checked disabled' : ''}>
                            <label class="form-check-label small">
                                ${isDefault ? '<span class="badge badge-satuan bg-primary">Default</span>' : 'Set Default'}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        ${!isDefault ? `
                            <button type="button" class="btn btn-danger btn-sm remove-satuan-btn" title="Hapus Satuan">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : '<small class="text-muted">Satuan Dasar</small>'}
                    </div>
                </div>
            </div>
        `;
    }

    function getItemTemplate(index) {
        let barangOptions = `
            <option value="">Pilih Barang</option>
            @foreach($barang as $brg)
                <option value="{{ $brg->id }}" data-satuan-terkecil="{{ $brg->satuan_terkecil }}">{{ $brg->nama_barang }}</option>
            @endforeach
        `;

        return `
            <div class="item-row border rounded p-3 mb-3 bg-light" data-index="${index}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Barang <span class="text-danger">*</span></label>
                        <select class="form-control barang-select" name="items[${index}][barang_id]" required>
                            ${barangOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Qty <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" class="form-control qty-input" name="items[${index}][qty]" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label>Satuan <span class="text-danger">*</span></label>
                        <select class="form-control satuan-select" name="items[${index}][satuan]" required>
                            <option value="">Pilih Satuan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Harga Beli <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control harga-beli-input" name="items[${index}][harga_beli]" value="0" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item w-100">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Tgl. Kadaluarsa</label>
                        <input type="date" class="form-control" name="items[${index}][tanggal_kadaluarsa]">
                    </div>
                    <div class="col-md-4">
                        <label>Subtotal (Otomatis)</label>
                        <input type="text" class="form-control subtotal-input" value="0" readonly>
                    </div>
                </div>

                <div class="satuan-konversi-section mt-3 p-3 border rounded bg-white" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-diagram-3"></i> Kelola Satuan & Harga Jual
                        </h6>
                        <button type="button" class="btn btn-success btn-sm add-satuan-btn">
                            <i class="bi bi-plus-circle"></i> Tambah Satuan Baru
                        </button>
                    </div>
                    
                    <div class="satuan-list">
                    </div>
                </div>
            </div>
        `;
    }

    function calculateSubtotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const hargaBeli = parseFloat(row.querySelector('.harga-beli-input').value) || 0;
        const subtotal = (qty * hargaBeli).toFixed(2);
        row.querySelector('.subtotal-input').value = subtotal;
        calculateGrandTotal();
    }

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
        const nilaiDiskon = totalBayar * (diskonPersen / 100);
        totalBayar -= nilaiDiskon;
        
        const nilaiPpn = totalBayar * (ppnPersen / 100);
        totalBayar += nilaiPpn;

        document.getElementById('total_bayar').value = Math.max(0, totalBayar).toFixed(2);
    }

    function fetchSatuanKonversi(barangId, row) {
        if (!barangId) {
            row.querySelector('.satuan-select').innerHTML = '<option value="">Pilih Satuan</option>';
            row.querySelector('.satuan-konversi-section').style.display = 'none';
            return;
        }

        const itemIdx = row.dataset.index || Array.from(row.parentElement.children).indexOf(row);
        const satuanSelect = row.querySelector('.satuan-select');
        const selectedSatuan = satuanSelect.dataset.selectedSatuan || '';

        fetch(`/barang/${barangId}/satuan`)
            .then(response => response.json())
            .then(data => {
                const satuanList = row.querySelector('.satuan-list');
                
                let options = '<option value="">Pilih Satuan</option>';
                options += `<option value="${data.satuan_dasar}" ${selectedSatuan === data.satuan_dasar ? 'selected' : ''}>${data.satuan_dasar} (Dasar)</option>`;
                
                data.konversi.forEach(konv => {
                    options += `<option value="${konv.satuan_konversi}" ${selectedSatuan === konv.satuan_konversi ? 'selected' : ''}>${konv.satuan_konversi}</option>`;
                });
                satuanSelect.innerHTML = options;
                
                satuanList.innerHTML = '';
                
                const satuanDasar = {
                    nama_satuan: data.satuan_dasar,
                    jumlah_konversi: 1,
                    harga_jual: data.harga_jual || 0,
                    is_default: true,
                    id: ''
                };
                satuanList.insertAdjacentHTML('beforeend', getSatuanItemTemplate(itemIdx, satuanDasar));
                
                data.konversi.forEach(konv => {
                    const satuanData = {
                        nama_satuan: konv.satuan_konversi,
                        jumlah_konversi: konv.jumlah_konversi,
                        harga_jual: konv.harga_jual,
                        is_default: false,
                        id: konv.id
                    };
                    satuanList.insertAdjacentHTML('beforeend', getSatuanItemTemplate(itemIdx, satuanData));
                });
                
                row.querySelector('.satuan-konversi-section').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching satuan:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        
        // Initialize existing items
        document.querySelectorAll('.item-row').forEach(row => {
            const barangId = row.querySelector('.barang-select').value;
            if (barangId) {
                fetchSatuanKonversi(barangId, row);
            }
        });

        document.getElementById('add-item-btn').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            container.insertAdjacentHTML('beforeend', getItemTemplate(itemIndex));
            itemIndex++;
        });

        document.getElementById('items-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const row = e.target.closest('.item-row');
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateGrandTotal(); 
                } else {
                    alert('Minimal harus ada 1 barang dalam pembelian.');
                }
            }
            
            if (e.target.closest('.remove-satuan-btn')) {
                const satuanItem = e.target.closest('.satuan-item');
                if (confirm('Hapus satuan ini?')) {
                    satuanItem.remove();
                }
            }
            
            if (e.target.closest('.add-satuan-btn')) {
                const row = e.target.closest('.item-row');
                const itemIdx = row.dataset.index || Array.from(row.parentElement.children).indexOf(row);
                const satuanList = row.querySelector('.satuan-list');
                
                const namaSatuan = prompt('Masukkan nama satuan baru:');
                if (!namaSatuan) return;
                
                const existingSatuan = Array.from(satuanList.querySelectorAll('.satuan-nama-input'))
                    .map(input => input.value.toLowerCase());
                if (existingSatuan.includes(namaSatuan.toLowerCase())) {
                    alert('Satuan ini sudah ada!');
                    return;
                }
                
                const newSatuan = {
                    nama_satuan: namaSatuan,
                    jumlah_konversi: 1,
                    harga_jual: 0,
                    is_default: false,
                    id: ''
                };
                
                satuanList.insertAdjacentHTML('beforeend', getSatuanItemTemplate(itemIdx, newSatuan, true));
                
                const satuanSelect = row.querySelector('.satuan-select');
                const newOption = document.createElement('option');
                newOption.value = namaSatuan;
                newOption.textContent = namaSatuan;
                satuanSelect.appendChild(newOption);
            }
        });

        document.getElementById('items-container').addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');

            if (e.target.classList.contains('barang-select')) {
                const barangId = e.target.value;
                const satuanSelect = row.querySelector('.satuan-select');
                satuanSelect.removeAttribute('data-selected-satuan');
                
                fetchSatuanKonversi(barangId, row);
                
                row.querySelector('.qty-input').value = 1;
                row.querySelector('.harga-beli-input').value = 0;
                calculateSubtotal(row); 

            } else if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });
        
        document.getElementById('items-container').addEventListener('input', function(e) {
            const row = e.target.closest('.item-row');
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-beli-input')) {
                calculateSubtotal(row);
            }
        });

        document.getElementById('diskon').addEventListener('input', calculateGrandTotal);
        document.getElementById('ppn').addEventListener('input', calculateGrandTotal);

        calculateGrandTotal();
    });

</script>
@endpush
@endsection