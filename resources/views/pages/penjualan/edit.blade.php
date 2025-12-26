@extends('layouts.app')

@section('title', 'Edit Penjualan')

@push('styles')
<style>
    .item-row {
        background: #f8f9fa;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .item-row:hover {
        background: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-remove {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .total-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .obat-row {
        cursor: pointer;
    }
    
    .obat-row:hover {
        background-color: #e7f3ff !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-pencil-square me-2"></i> Edit Penjualan
                        </h5>
                        <a href="{{ route('penjualan.show', $penjualan->id) }}" class="btn btn-sm btn-light">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('penjualan.update', $penjualan->id) }}" method="POST" id="formEditPenjualan">
                        @csrf
                        @method('PUT')

                        {{-- Info Transaksi --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <strong>No. Nota:</strong> {{ $penjualan->nomor_nota }}<br>
                                    <strong>Tanggal:</strong> {{ $penjualan->tanggal_penjualan->format('d/m/Y H:i') }}<br>
                                    <strong>Kasir:</strong> {{ $penjualan->user->name }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nama Pelanggan (Opsional)</label>
                                <input type="text" class="form-control" name="nama_pelanggan" 
                                       value="{{ old('nama_pelanggan', $penjualan->nama_pelanggan) }}" 
                                       placeholder="Nama pelanggan...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select" name="metode_pembayaran" required>
                                    <option value="cash" {{ $penjualan->metode_pembayaran == 'cash' ? 'selected' : '' }}>💵 Cash</option>
                                    <option value="transfer" {{ $penjualan->metode_pembayaran == 'transfer' ? 'selected' : '' }}>🏦 Transfer</option>
                                    <option value="qris" {{ $penjualan->metode_pembayaran == 'qris' ? 'selected' : '' }}>📱 QRIS</option>
                                    <option value="debit" {{ $penjualan->metode_pembayaran == 'debit' ? 'selected' : '' }}>💳 Debit</option>
                                    <option value="credit" {{ $penjualan->metode_pembayaran == 'credit' ? 'selected' : '' }}>💳 Credit</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pilih Barang --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-cart-plus text-primary"></i> Tambah/Edit Item
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Pilih Barang</label>
                                        <select class="form-select" id="selectBarang">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach($barang as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-nama="{{ $item->nama_barang }}"
                                                        data-harga="{{ $item->harga_jual }}"
                                                        data-stok="{{ $item->stok }}"
                                                        data-satuan-dasar="{{ $item->satuan_terkecil }}"
                                                        data-satuan-konversi="{{ json_encode($item->satuanKonversi) }}">
                                                    {{ $item->nama_barang }} (Stok: {{ $item->stok }} {{ $item->satuan_terkecil }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Qty</label>
                                        <input type="number" class="form-control" id="inputQty" placeholder="0" min="1" step="0.01">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Satuan</label>
                                        <select class="form-select" id="selectSatuan">
                                            <option value="">-</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Harga</label>
                                        <input type="number" class="form-control" id="inputHarga" placeholder="0" min="0" readonly>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary w-100" id="btnTambahItem">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Daftar Item --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-list-check text-success"></i> Daftar Item (<span id="totalItems">0</span> item)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="itemsContainer">
                                    {{-- Items akan ditampilkan di sini via JavaScript --}}
                                </div>
                            </div>
                        </div>

                        {{-- Total Section --}}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Diskon (Rp)</label>
                                        <input type="number" class="form-control" name="diskon" id="inputDiskon" 
                                               value="{{ old('diskon', $penjualan->diskon) }}" min="0" placeholder="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Referensi (Opsional)</label>
                                        <input type="text" class="form-control" name="nomor_referensi" 
                                               value="{{ old('nomor_referensi', $penjualan->nomor_referensi) }}" 
                                               placeholder="No. Ref / Bukti Transfer">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Keterangan (Opsional)</label>
                                    <textarea class="form-control" name="keterangan" rows="3" 
                                              placeholder="Catatan tambahan...">{{ old('keterangan', $penjualan->keterangan) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="total-section">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <strong id="displaySubtotal">Rp 0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Diskon:</span>
                                        <strong id="displayDiskon">Rp 0</strong>
                                    </div>
                                    <hr style="border-color: rgba(255,255,255,0.3);">
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5 class="mb-0">TOTAL:</h5>
                                        <h5 class="mb-0" id="displayTotal">Rp 0</h5>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-white">Uang Dibayar <span class="text-warning">*</span></label>
                                        <input type="number" class="form-control form-control-lg" name="uang_dibayar" 
                                               id="inputUangDibayar" value="{{ old('uang_dibayar', $penjualan->jumlah_bayar) }}" 
                                               placeholder="0" min="0" required>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kembalian:</span>
                                        <strong id="displayKembalian">Rp 0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden Inputs --}}
                        <input type="hidden" name="total_bayar" id="hiddenTotalBayar" value="0">

                        {{-- Submit Button --}}
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-lg btn-success">
                                <i class="bi bi-check-circle me-2"></i> Update Penjualan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Satuan -->
<div class="modal fade" id="modalPilihSatuan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i>PILIH SATUAN - <span id="modalNamaBarang"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-2">
                    <i class="bi bi-info-circle me-1"></i> Klik satuan yang diinginkan
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="tableSatuan">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">No</th>
                                <th>Satuan</th>
                                <th class="text-center">Konversi</th>
                                <th class="text-end">Harga</th>
                            </tr>
                        </thead>
                        <tbody id="listSatuan"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let items = [];
let barangData = @json($barang);
let itemIndex = 0;
let modalPilihSatuan;
let currentEditIndex = -1;

// Load existing items from penjualan
@foreach($penjualan->detailPenjualan as $detail)
    items.push({
        index: itemIndex++,
        barang_id: {{ $detail->barang_id }},
        nama_barang: "{{ $detail->barang->nama_barang }}",
        qty: {{ $detail->jumlah }},
        satuan: "{{ $detail->satuan }}",
        harga: {{ $detail->harga_jual }},
        subtotal: {{ $detail->subtotal }},
        stok: {{ $detail->barang->stok ?? 0 }},
        satuan_dasar: "{{ $detail->barang->satuan_terkecil ?? '' }}",
        satuan_konversi: @json($detail->barang->satuanKonversi ?? [])
    });
@endforeach

$(document).ready(function() {
    modalPilihSatuan = new bootstrap.Modal(document.getElementById('modalPilihSatuan'));
    
    renderItems();
    calculateTotal();
    
    // Pilih barang
    $('#selectBarang').on('change', function() {
        const barangId = $(this).val();
        if (!barangId) return;
        
        const selectedOption = $(this).find('option:selected');
        const selectedBarang = barangData.find(b => b.id == barangId);
        
        if (!selectedBarang) return;
        
        // Load satuan konversi
        let satuanHtml = `<option value="${selectedBarang.satuan_terkecil}">${selectedBarang.satuan_terkecil}</option>`;
        
        if (selectedBarang.satuan_konversi && selectedBarang.satuan_konversi.length > 0) {
            selectedBarang.satuan_konversi.forEach(konv => {
                satuanHtml += `<option value="${konv.nama_satuan}" data-harga="${konv.harga_jual}" data-konversi="${konv.jumlah_konversi}">${konv.nama_satuan}</option>`;
            });
        }
        
        $('#selectSatuan').html(satuanHtml);
        
        // Set harga default (satuan terkecil)
        $('#inputHarga').val(selectedBarang.harga_jual);
        $('#selectSatuan').val(selectedBarang.satuan_terkecil);
        $('#inputQty').val(1).focus();
    });
    
    // Ubah satuan -> update harga
    $('#selectSatuan').on('change', function() {
        const barangId = $('#selectBarang').val();
        const selectedBarang = barangData.find(b => b.id == barangId);
        const satuan = $(this).val();
        const selectedOption = $(this).find('option:selected');
        
        if (satuan === selectedBarang.satuan_terkecil) {
            $('#inputHarga').val(selectedBarang.harga_jual);
        } else {
            const harga = selectedOption.data('harga');
            $('#inputHarga').val(harga);
        }
    });
    
    // Tambah item
    $('#btnTambahItem').on('click', function() {
        const barangId = $('#selectBarang').val();
        const qty = parseFloat($('#inputQty').val());
        const satuan = $('#selectSatuan').val();
        const harga = parseFloat($('#inputHarga').val());
        
        if (!barangId || !qty || !satuan || !harga) {
            alert('Lengkapi data barang!');
            return;
        }
        
        const selectedOption = $('#selectBarang option:selected');
        const selectedBarang = barangData.find(b => b.id == barangId);
        const namaBarang = selectedOption.data('nama');
        const stok = selectedOption.data('stok');
        
        items.push({
            index: itemIndex++,
            barang_id: barangId,
            nama_barang: namaBarang,
            qty: qty,
            satuan: satuan,
            harga: harga,
            subtotal: qty * harga,
            stok: stok,
            satuan_dasar: selectedBarang.satuan_terkecil,
            satuan_konversi: selectedBarang.satuan_konversi
        });
        
        renderItems();
        calculateTotal();
        
        // Reset form
        $('#selectBarang').val('');
        $('#inputQty').val('');
        $('#selectSatuan').html('<option value="">-</option>');
        $('#inputHarga').val('');
    });
    
    // Hapus item
    $(document).on('click', '.btn-remove-item', function() {
        const index = $(this).data('index');
        items = items.filter(item => item.index !== index);
        renderItems();
        calculateTotal();
    });
    
    // Ganti satuan item
    $(document).on('click', '.btn-ganti-satuan', function() {
        const arrayIndex = $(this).data('array-index');
        currentEditIndex = arrayIndex;
        gantiSatuanItem(arrayIndex);
    });
    
    // Hitung diskon & uang dibayar
    $('#inputDiskon, #inputUangDibayar').on('input', calculateTotal);
});

function gantiSatuanItem(arrayIndex) {
    const item = items[arrayIndex];
    
    $('#modalNamaBarang').text(item.nama_barang);
    
    let html = '';
    let counter = 1;
    
    // Satuan dasar
    html += `
        <tr onclick="updateSatuanItem(${arrayIndex}, '${item.satuan_dasar}', ${item.harga})">
            <td class="text-center">${counter}</td>
            <td><strong>${item.satuan_dasar}</strong> <span class="badge bg-success">Dasar</span></td>
            <td class="text-center">1</td>
            <td class="text-end"><strong>Rp ${formatRupiah(item.harga)}</strong></td>
        </tr>
    `;
    counter++;
    
    // Satuan konversi
    if (item.satuan_konversi && item.satuan_konversi.length > 0) {
        item.satuan_konversi.forEach(function(satuan) {
            html += `
                <tr onclick="updateSatuanItem(${arrayIndex}, '${satuan.nama_satuan}', ${satuan.harga_jual})">
                    <td class="text-center">${counter}</td>
                    <td><strong>${satuan.nama_satuan}</strong></td>
                    <td class="text-center">${satuan.jumlah_konversi} ${item.satuan_dasar}</td>
                    <td class="text-end"><strong>Rp ${formatRupiah(satuan.harga_jual)}</strong></td>
                </tr>
            `;
            counter++;
        });
    }
    
    $('#listSatuan').html(html);
    modalPilihSatuan.show();
}

function updateSatuanItem(arrayIndex, satuan, harga) {
    items[arrayIndex].satuan = satuan;
    items[arrayIndex].harga = harga;
    items[arrayIndex].subtotal = items[arrayIndex].qty * harga;
    
    modalPilihSatuan.hide();
    renderItems();
    calculateTotal();
}

function renderItems() {
    const container = $('#itemsContainer');
    container.empty();
    
    if (items.length === 0) {
        container.html('<div class="alert alert-warning text-center">Belum ada item. Silakan tambahkan barang.</div>');
        $('#totalItems').text(0);
        return;
    }
    
    items.forEach((item, i) => {
        container.append(`
            <div class="item-row">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <strong>${item.nama_barang}</strong>
                        <input type="hidden" name="items[${i}][barang_id]" value="${item.barang_id}">
                    </div>
                    <div class="col-md-2 text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.qty}" min="1" step="0.01"
                               onchange="updateQty(${i}, this.value)">
                        <input type="hidden" name="items[${i}][qty]" value="${item.qty}">
                    </div>
                    <div class="col-md-2 text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ganti-satuan" 
                                data-array-index="${i}">
                            ${item.satuan}
                        </button>
                        <input type="hidden" name="items[${i}][satuan]" value="${item.satuan}">
                    </div>
                    <div class="col-md-2 text-end">
                        Rp ${formatRupiah(item.harga)}
                        <input type="hidden" name="items[${i}][harga]" value="${item.harga}">
                    </div>
                    <div class="col-md-1 text-end">
                        <strong>Rp ${formatRupiah(item.subtotal)}</strong>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-item" data-index="${item.index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
    });
    
    $('#totalItems').text(items.length);
}

function updateQty(index, qty) {
    qty = parseFloat(qty) || 1;
    items[index].qty = qty;
    items[index].subtotal = qty * items[index].harga;
    renderItems();
    calculateTotal();
}

function calculateTotal() {
    const subtotal = items.reduce((sum, item) => sum + item.subtotal, 0);
    const diskon = parseFloat($('#inputDiskon').val()) || 0;
    const total = subtotal - diskon;
    const uangDibayar = parseFloat($('#inputUangDibayar').val()) || 0;
    const kembalian = uangDibayar - total;
    
    $('#displaySubtotal').text('Rp ' + formatRupiah(subtotal));
    $('#displayDiskon').text('Rp ' + formatRupiah(diskon));
    $('#displayTotal').text('Rp ' + formatRupiah(total));
    $('#displayKembalian').text('Rp ' + formatRupiah(kembalian >= 0 ? kembalian : 0));
    
    $('#hiddenTotalBayar').val(total);
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(Math.floor(angka));
}

// Validasi sebelum submit
$('#formEditPenjualan').on('submit', function(e) {
    if (items.length === 0) {
        e.preventDefault();
        alert('Minimal harus ada 1 item!');
        return false;
    }
    
    const total = parseFloat($('#hiddenTotalBayar').val());
    const uangDibayar = parseFloat($('#inputUangDibayar').val());
    
    if (uangDibayar < total) {
        e.preventDefault();
        alert('Uang dibayar kurang dari total!');
        return false;
    }
    
    return confirm('Yakin ingin mengupdate penjualan ini?');
});
</script>
@endpush