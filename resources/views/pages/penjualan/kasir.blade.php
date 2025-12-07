@extends('layouts.app')

@section('title', 'Kasir POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">TRANSAKSI PENJUALAN</h4>
                <small class="text-muted">Kasir: {{ auth()->user()->name }} | Shift: #{{ $shift->id }}</small>
            </div>
            <span class="badge bg-success px-3 py-2">
                <i class="bi bi-clock me-1"></i> Shift Aktif
            </span>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT: Form Input & Keranjang -->
        <div class="col-lg-8">
            <!-- Form Input Barang -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-input-cursor me-2"></i>INPUT BARANG</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">No. Faktur</label>
                            <input type="text" class="form-control" value="AUTO" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Pembayaran</label>
                            <select class="form-select">
                                <option>Tunai</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Kode / ENTER</label>
                            <input type="text" id="inputKodeBarang" class="form-control form-control-lg" 
                                   placeholder="Ketik kode barang atau nama, tekan ENTER untuk cari..." autofocus>
                            <small class="text-muted">Tekan ENTER untuk membuka daftar barang</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keranjang Belanja -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-cart3 me-2"></i>DAFTAR BELANJA
                    </h6>
                    <span class="badge bg-light text-dark" id="totalItems">0 Item</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-bordered table-hover mb-0" id="cartTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode</th>
                                    <th width="25%">Nama</th>
                                    <th width="8%">Qty</th>
                                    <th width="12%">Satuan</th>
                                    <th width="12%">Harga</th>
                                    <th width="8%">Disc %</th>
                                    <th width="15%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                        Belum ada barang di keranjang
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Payment Section -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>PEMBAYARAN</h6>
                </div>
                <div class="card-body">
                    <!-- Total Section -->
                    <div class="mb-3">
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="form-label mb-0">Sub Total</label>
                                <input type="text" class="form-control" id="subTotal" value="0" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-0">Diskon</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="diskonPersen" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-0">Ongkos</label>
                            <input type="text" class="form-control" id="ongkos" value="0">
                        </div>
                    </div>

                    <!-- Grand Total -->
                    <div class="bg-primary text-white p-3 rounded text-center mb-3">
                        <div class="mb-1">GRAND TOTAL</div>
                        <h2 class="mb-0" id="grandTotal">Rp 0</h2>
                    </div>

                    <!-- Bayar Section -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Bayar (F2)</label>
                        <input type="text" id="uangDibayar" class="form-control form-control-lg text-end fw-bold" 
                               placeholder="0">
                    </div>

                    <!-- Quick Amounts -->
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="setUangPas()">
                                Uang Pas
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="addAmount(50000)">
                                +50K
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="addAmount(100000)">
                                +100K
                            </button>
                        </div>
                    </div>

                    <!-- Kembalian -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kembali</label>
                        <input type="text" id="kembalian" class="form-control form-control-lg text-end fw-bold bg-light" 
                               value="0" readonly>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning btn-lg" id="btnPending" onclick="simpanPending()">
                            <i class="bi bi-clock-history me-2"></i>Pending (F4)
                        </button>
                        <button class="btn btn-success btn-lg" id="btnSimpan" onclick="prosesTransaksi()" disabled>
                            <i class="bi bi-save me-2"></i>Simpan (F3)
                        </button>
                        <button class="btn btn-danger btn-lg" onclick="clearKeranjang()">
                            <i class="bi bi-x-circle me-2"></i>Batal (ESC)
                        </button>
                        <button class="btn btn-info btn-lg" onclick="tampilkanPending()">
                            <i class="bi bi-list-ul me-2"></i>Lihat Pending
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Obat/Barang -->
<div class="modal fade" id="modalPilihObat" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-search me-2"></i>PILIH OBAT
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Search Box -->
                <div class="mb-3">
                    <input type="text" id="searchObat" class="form-control form-control-lg" 
                           placeholder="Cari obat..." autofocus>
                </div>

                <!-- Table Daftar Obat -->
                <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="3%">No</th>
                                <th width="10%">Kode</th>
                                <th width="25%">Nama Obat</th>
                                <th width="21%">Satuan 1 (Dasar)</th>
                                <th width="20%">Satuan 2</th>
                                <th width="20%">Satuan 3</th>
                            </tr>
                        </thead>
                        <tbody id="listObat">
                            @foreach($barang as $key => $item)
                            <tr class="obat-row" 
                                data-id="{{ $item->id }}"
                                data-nama="{{ strtolower($item->nama_barang) }}" 
                                data-kode="{{ strtolower($item->kode_barang) }}"
                                onclick="pilihBarangLangsung({{ $item->id }})">
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td><strong class="text-primary">{{ $item->kode_barang }}</strong></td>
                                <td><strong>{{ $item->nama_barang }}</strong></td>
                                
                                <!-- Satuan Dasar (Satuan 1) -->
                                <td>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">{{ $item->satuan_terkecil }}</span>
                                        <strong class="text-success">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</strong>
                                    </div>
                                </td>
                                
                                <!-- Satuan 2 -->
                                <td>
                                    @if(isset($item->satuan_konversi[0]))
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary">{{ $item->satuan_konversi[0]->nama_satuan }}</span>
                                                <small class="text-muted ms-1">({{ $item->satuan_konversi[0]->jumlah_konversi }} {{ $item->satuan_terkecil }})</small>
                                            </div>
                                            <strong class="text-primary">Rp {{ number_format($item->satuan_konversi[0]->harga_jual, 0, ',', '.') }}</strong>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <!-- Satuan 3 -->
                                <td>
                                    @if(isset($item->satuan_konversi[1]))
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary">{{ $item->satuan_konversi[1]->nama_satuan }}</span>
                                                <small class="text-muted ms-1">({{ $item->satuan_konversi[1]->jumlah_konversi }} {{ $item->satuan_terkecil }})</small>
                                            </div>
                                            <strong class="text-primary">Rp {{ number_format($item->satuan_konversi[1]->harga_jual, 0, ',', '.') }}</strong>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto">
                    <i class="bi bi-info-circle me-1"></i>
                    Klik baris untuk tambah dengan satuan dasar | {{ count($barang) }} Item
                </span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Satuan -->
<div class="modal fade" id="modalPilihSatuan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i>
                    PILIH SATUAN - <span id="modalNamaBarang"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="tableSatuan">
                        <thead class="table-light">
                            <tr>
                                <th width="40%">Satuan</th>
                                <th width="20%">Jumlah</th>
                                <th width="40%">Harga</th>
                            </tr>
                        </thead>
                        <tbody id="listSatuan">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Pending -->
<div class="modal fade" id="modalPending" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>TRANSAKSI PENDING
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Waktu</th>
                                <th width="10%">Jumlah Item</th>
                                <th width="15%">Total</th>
                                <th width="25%">Catatan</th>
                                <th width="15%">Kasir</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="listPending">
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Belum ada transaksi pending
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Catatan Pending -->
<div class="modal fade" id="modalCatatanPending" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Catatan Pending</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Catatan (opsional)</label>
                <textarea class="form-control" id="catatanPending" rows="3" 
                          placeholder="Contoh: Pak Budi - Tunggu struk dokter"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="konfirmasiPending()">
                    <i class="bi bi-check-circle me-1"></i>Simpan Pending
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card {
    border: none;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Table Styling */
.table {
    font-size: 0.9rem;
}

.table thead th {
    background: #e9ecef;
    font-weight: 600;
    border: 1px solid #dee2e6;
}

.table tbody td {
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

/* Modal Obat Row Hover */
.obat-row {
    cursor: pointer;
}

.obat-row:hover {
    background-color: #e7f3ff !important;
}

.obat-row.selected {
    background-color: #cfe2ff !important;
}

/* Satuan Table Row */
#tableSatuan tbody tr {
    cursor: pointer;
}

#tableSatuan tbody tr:hover {
    background-color: #e7f3ff;
}

/* Input Focus */
input:focus, select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Number input remove arrows */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}
</style>

@endsection

@push('scripts')
<script>
let keranjang = [];
let currentBarangData = null;
let pendingList = [];
let modalPilihObat, modalPilihSatuan, modalPending, modalCatatanPending;

$(document).ready(function() {
    // Initialize modals
    modalPilihObat = new bootstrap.Modal(document.getElementById('modalPilihObat'));
    modalPilihSatuan = new bootstrap.Modal(document.getElementById('modalPilihSatuan'));
    modalPending = new bootstrap.Modal(document.getElementById('modalPending'));
    modalCatatanPending = new bootstrap.Modal(document.getElementById('modalCatatanPending'));
    
    // Load pending dari localStorage
    loadPendingFromStorage();
    
    // ENTER to open modal
    $('#inputKodeBarang').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            let keyword = $(this).val().trim();
            modalPilihObat.show();
            
            setTimeout(function() {
                $('#searchObat').val(keyword).focus().trigger('keyup');
            }, 500);
        }
    });
    
    // Search obat in modal
    $('#searchObat').on('keyup', function() {
        let keyword = $(this).val().toLowerCase();
        
        $('.obat-row').each(function() {
            let nama = $(this).data('nama');
            let kode = $(this).data('kode');
            
            if (nama.includes(keyword) || kode.includes(keyword)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});

// Pilih barang langsung (klik baris = langsung masuk keranjang dengan satuan dasar)
function pilihBarangLangsung(barangId) {
    $.get('/penjualan/barang/' + barangId, function(data) {
        currentBarangData = data;
        modalPilihObat.hide();
        
        // ✅ FIX: Gunakan data lengkap dari API
        tambahKeKeranjang(data.satuan_dasar, data.harga_jual, 1);
    }).fail(function() {
        alert('Gagal mengambil data barang');
    });
}

// Tambah ke keranjang
function tambahKeKeranjang(satuan, harga, konversi) {
    const data = currentBarangData;
    
    // ✅ FIX: Pastikan data lengkap
    if (!data || !data.id) {
        alert('Data barang tidak lengkap!');
        return;
    }
    
    // Cek apakah sudah ada di keranjang dengan satuan yang sama
    let existing = keranjang.find(item => 
        item.barang_id == data.id && item.satuan == satuan
    );
    
    if (existing) {
        let stokDalamSatuan = Math.floor(data.stok / konversi);
        
        if (existing.qty < stokDalamSatuan) {
            existing.qty++;
        } else {
            alert(`Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${satuan}`);
            return;
        }
    } else {
        keranjang.push({
            barang_id: data.id,
            kode_barang: data.kode_barang,  
            nama_barang: data.nama_barang,
            qty: 1,
            satuan: satuan,
            harga: harga,  
            diskon: 0,
            stok: data.stok,
            konversi: konversi,
            satuan_dasar: data.satuan_dasar,
            harga_dasar: data.harga_jual, // ✅ TAMBAH: Simpan harga satuan dasar
            satuan_konversi: data.satuan_konversi
        });
    }
    
    renderKeranjang();
    $('#inputKodeBarang').val('').focus();
}

// Ganti satuan dari keranjang
function gantiSatuan(index) {
    const item = keranjang[index];
    
    // Tampilkan modal pilih satuan
    $('#modalNamaBarang').text(item.nama_barang);
    
    let html = '';
    
    // ✅ FIX: Gunakan harga_dasar yang sudah disimpan
    let hargaSatuanDasar = item.harga_dasar || item.harga;
    
    // Satuan dasar
    html += `
        <tr onclick="gantiSatuanItem(${index}, '${item.satuan_dasar}', ${hargaSatuanDasar}, 1)">
            <td>
                <strong>${item.satuan_dasar}</strong>
                <span class="badge bg-success ms-2">Satuan Dasar</span>
            </td>
            <td class="text-center">1 ${item.satuan_dasar}</td>
            <td class="text-end">
                <strong class="text-primary">Rp ${formatRupiah(hargaSatuanDasar)}</strong>
            </td>
        </tr>
    `;
    
    // Satuan konversi
    if (item.satuan_konversi && item.satuan_konversi.length > 0) {
        item.satuan_konversi.forEach(function(satuan) {
            html += `
                <tr onclick="gantiSatuanItem(${index}, '${satuan.nama_satuan}', ${satuan.harga_jual}, ${satuan.jumlah_konversi})">
                    <td><strong>${satuan.nama_satuan}</strong></td>
                    <td class="text-center">
                        ${satuan.jumlah_konversi} ${item.satuan_dasar}
                    </td>
                    <td class="text-end">
                        <strong class="text-primary">Rp ${formatRupiah(satuan.harga_jual)}</strong>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#listSatuan').html(html);
    modalPilihSatuan.show();
}

// Fungsi ganti satuan item di keranjang
function gantiSatuanItem(index, namaSatuan, hargaSatuan, konversi) {
    keranjang[index].satuan = namaSatuan;
    keranjang[index].harga = hargaSatuan;
    keranjang[index].konversi = konversi;
    keranjang[index].qty = 1; // Reset qty ke 1
    
    modalPilihSatuan.hide();
    renderKeranjang();
}

// Render keranjang
function renderKeranjang() {
    let html = '';
    let total = 0;
    
    if (keranjang.length === 0) {
        html = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                    Belum ada barang di keranjang
                </td>
            </tr>
        `;
    } else {
        keranjang.forEach((item, index) => {
            let subtotalBeforeDisc = item.qty * item.harga;
            let discAmount = (subtotalBeforeDisc * item.diskon) / 100;
            let subtotal = subtotalBeforeDisc - discAmount;
            total += subtotal;
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${item.kode_barang}</td>
                    <td><strong>${item.nama_barang}</strong></td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.qty}" min="1"
                               onchange="updateQty(${index}, this.value)">
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="gantiSatuan(${index})" title="Ganti Satuan">
                            ${item.satuan} <i class="bi bi-pencil-square ms-1"></i>
                        </button>
                    </td>
                    <td class="text-end">${formatRupiah(item.harga)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.diskon}" min="0" max="100"
                               onchange="updateDiskon(${index}, this.value)">
                    </td>
                    <td class="text-end">
                        <strong>${formatRupiah(subtotal)}</strong>
                        <button class="btn btn-sm btn-danger ms-2" onclick="hapusItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#cartItems').html(html);
    $('#totalItems').text(keranjang.length + ' Item');
    $('#subTotal').val(formatRupiah(total));
    
    hitungGrandTotal();
}

// Update qty
function updateQty(index, newQty) {
    newQty = parseInt(newQty);
    
    if (newQty < 1) {
        hapusItem(index);
        return;
    }
    
    const item = keranjang[index];
    let stokDalamSatuan = Math.floor(item.stok / item.konversi);
    
    if (newQty > stokDalamSatuan) {
        alert(`Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${item.satuan}`);
        renderKeranjang();
        return;
    }
    
    keranjang[index].qty = newQty;
    renderKeranjang();
}

// Update diskon
function updateDiskon(index, diskon) {
    keranjang[index].diskon = parseFloat(diskon) || 0;
    renderKeranjang();
}

// Hapus item
function hapusItem(index) {
    keranjang.splice(index, 1);
    renderKeranjang();
}

// Clear keranjang
function clearKeranjang() {
    if (keranjang.length > 0 && confirm('Batalkan transaksi dan kosongkan keranjang?')) {
        keranjang = [];
        renderKeranjang();
        $('#uangDibayar').val('');
        $('#diskonPersen').val('0');
$('#ongkos').val('0');
$('#inputKodeBarang').val('').focus();
}
}
// Hitung Grand Total
function hitungGrandTotal() {
let subtotal = 0;
keranjang.forEach(item => {
let itemTotal = item.qty * item.harga;
let discAmount = (itemTotal * item.diskon) / 100;
subtotal += (itemTotal - discAmount);
});
let diskonPersen = parseFloat($('#diskonPersen').val()) || 0;
let ongkos = parseInt($('#ongkos').val().replace(/\D/g, '')) || 0;

let diskonNominal = (subtotal * diskonPersen) / 100;
let grandTotal = subtotal - diskonNominal + ongkos;

$('#grandTotal').text('Rp ' + formatRupiah(grandTotal));

hitungKembalian();
}
// Event listeners untuk diskon dan ongkos
$('#diskonPersen, #ongkos').on('keyup change', function() {
hitungGrandTotal();
});
// Input uang dibayar
$('#uangDibayar').on('keyup', function() {
let value = $(this).val().replace(/\D/g, '');
$(this).val(formatRupiah(value));
hitungKembalian();
});
// Hitung kembalian
function hitungKembalian() {
let subtotal = 0;
keranjang.forEach(item => {
let itemTotal = item.qty * item.harga;
let discAmount = (itemTotal * item.diskon) / 100;
subtotal += (itemTotal - discAmount);
});
let diskonPersen = parseFloat($('#diskonPersen').val()) || 0;
let ongkos = parseInt($('#ongkos').val().replace(/\D/g, '')) || 0;
let diskonNominal = (subtotal * diskonPersen) / 100;
let grandTotal = subtotal - diskonNominal + ongkos;

let uangDibayar = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;
let kembalian = uangDibayar - grandTotal;

$('#kembalian').val(formatRupiah(kembalian > 0 ? kembalian : 0));

if (uangDibayar >= grandTotal && grandTotal > 0) {
    $('#btnSimpan').prop('disabled', false);
} else {
    $('#btnSimpan').prop('disabled', true);
}
}
// Quick amount
function setUangPas() {
let grandTotal = $('#grandTotal').text().replace(/[^0-9]/g, '');
$('#uangDibayar').val(formatRupiah(grandTotal));
hitungKembalian();
}
function addAmount(amount) {
let current = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;
$('#uangDibayar').val(formatRupiah(current + amount));
hitungKembalian();
}
// ===========================================
// FITUR PENDING
// ===========================================
// Simpan ke pending
function simpanPending() {
if (keranjang.length === 0) {
alert('Keranjang masih kosong!');
return;
}
// Tampilkan modal catatan
$('#catatanPending').val('');
modalCatatanPending.show();
}
// Konfirmasi pending
function konfirmasiPending() {
let catatan = $('#catatanPending').val().trim();
let subtotal = 0;
keranjang.forEach(item => {
    let itemTotal = item.qty * item.harga;
    let discAmount = (itemTotal * item.diskon) / 100;
    subtotal += (itemTotal - discAmount);
});

let diskonPersen = parseFloat($('#diskonPersen').val()) || 0;
let ongkos = parseInt($('#ongkos').val().replace(/\D/g, '')) || 0;
let diskonNominal = (subtotal * diskonPersen) / 100;
let grandTotal = subtotal - diskonNominal + ongkos;

// Simpan ke pending list
let pendingData = {
    id: Date.now(),
    waktu: new Date().toLocaleString('id-ID'),
    items: JSON.parse(JSON.stringify(keranjang)),
    total: grandTotal,
    diskon_persen: diskonPersen,
    ongkos: ongkos,
    catatan: catatan || '-',
    kasir: '{{ auth()->user()->name }}'
};

pendingList.push(pendingData);
savePendingToStorage();

modalCatatanPending.hide();

// Reset form
keranjang = [];
renderKeranjang();
$('#uangDibayar').val('');
$('#diskonPersen').val('0');
$('#ongkos').val('0');
$('#inputKodeBarang').val('').focus();

alert('Transaksi berhasil di-pending!');
}
// Tampilkan daftar pending
function tampilkanPending() {
let html = '';
if (pendingList.length === 0) {
    html = `
        <tr>
            <td colspan="7" class="text-center py-4 text-muted">
                Belum ada transaksi pending
            </td>
        </tr>
    `;
} else {
    pendingList.forEach((item, index) => {
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${item.waktu}</td>
                <td class="text-center">
                    <span class="badge bg-info">${item.items.length} item</span>
                </td>
                <td class="text-end"><strong>Rp ${formatRupiah(item.total)}</strong></td>
                <td>${item.catatan}</td>
                <td>${item.kasir}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-success" onclick="muatPending(${index})" title="Lanjutkan">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="hapusPending(${index})" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}

$('#listPending').html(html);
modalPending.show();
}
// Muat pending ke keranjang
function muatPending(index) {
if (keranjang.length > 0) {
if (!confirm('Keranjang saat ini akan diganti dengan transaksi pending. Lanjutkan?')) {
return;
}
}
let pending = pendingList[index];

// Load ke keranjang
keranjang = JSON.parse(JSON.stringify(pending.items));
$('#diskonPersen').val(pending.diskon_persen);
$('#ongkos').val(pending.ongkos);

// Hapus dari pending list
pendingList.splice(index, 1);
savePendingToStorage();

modalPending.hide();
renderKeranjang();

alert('Transaksi pending berhasil dimuat!');
}
// Hapus pending
function hapusPending(index) {
if (confirm('Hapus transaksi pending ini?')) {
pendingList.splice(index, 1);
savePendingToStorage();
tampilkanPending();
}
}
// Save pending to localStorage
function savePendingToStorage() {
localStorage.setItem('pos_pending_{{ $shift->id }}', JSON.stringify(pendingList));
}
// Load pending from localStorage
function loadPendingFromStorage() {
let stored = localStorage.getItem('pos_pending_{{ $shift->id }}');
if (stored) {
pendingList = JSON.parse(stored);
}
}
// ===========================================
// PROSES TRANSAKSI
// ===========================================
// Proses transaksi
function prosesTransaksi() {
if (keranjang.length === 0) {
alert('Keranjang masih kosong!');
return;
}
let subtotal = 0;
keranjang.forEach(item => {
    let itemTotal = item.qty * item.harga;
    let discAmount = (itemTotal * item.diskon) / 100;
    subtotal += (itemTotal - discAmount);
});

let diskonPersen = parseFloat($('#diskonPersen').val()) || 0;
let ongkos = parseInt($('#ongkos').val().replace(/\D/g, '')) || 0;
let diskonNominal = (subtotal * diskonPersen) / 100;
let grandTotal = subtotal - diskonNominal + ongkos;

let uangDibayar = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;

if (uangDibayar < grandTotal) {
    alert('Uang tidak cukup!');
    return;
}

$('#btnSimpan').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Memproses...');

$.ajax({
    url: '/penjualan/store',
    method: 'POST',
    data: {
        items: keranjang,
        total_bayar: grandTotal,
        uang_dibayar: uangDibayar,
        diskon: diskonNominal,
        metode_pembayaran: 'cash',
        _token: $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
        alert('Transaksi berhasil! Invoice: ' + response.invoice);
        
        if (confirm('Cetak struk?')) {
            window.open('/penjualan/print/' + response.penjualan_id, '_blank');
        }
        
        // Reset form
        keranjang = [];
        renderKeranjang();
        $('#uangDibayar').val('');
        $('#diskonPersen').val('0');
        $('#ongkos').val('0');
        $('#inputKodeBarang').val('').focus();
        $('#btnSimpan').html('<i class="bi bi-save me-2"></i>Simpan (F3)');
    },
    error: function(xhr) {
        alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan'));
        $('#btnSimpan').prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan (F3)');
    }
});
}
// ===========================================
// UTILITIES
// ===========================================
// Format rupiah
function formatRupiah(angka) {
if (!angka) return '0';
let number_string = angka.toString().replace(/[^,\d]/g, '');
let split = number_string.split(',');
let sisa = split[0].length % 3;
let rupiah = split[0].substr(0, sisa);
let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
if (ribuan) {
    let separator = sisa ? '.' : '';
    rupiah += separator + ribuan.join('.');
}

rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
return rupiah;
}
// ===========================================
// KEYBOARD SHORTCUTS
// ===========================================
$(document).on('keydown', function(e) {
// F2 - Focus uang dibayar
if (e.key === 'F2') {
e.preventDefault();
$('#uangDibayar').focus().select();
}
// F3 - Simpan transaksi
if (e.key === 'F3') {
    e.preventDefault();
    if (!$('#btnSimpan').prop('disabled')) {
        prosesTransaksi();
    }
}

// F4 - Pending
if (e.key === 'F4') {
    e.preventDefault();
    if (keranjang.length > 0) {
        simpanPending();
    }
}

// ESC - Clear/Batal
if (e.key === 'Escape') {
    if ($('#modalPilihObat').hasClass('show')) {
        modalPilihObat.hide();
    } else if ($('#modalPilihSatuan').hasClass('show')) {
        modalPilihSatuan.hide();
    } else if ($('#modalPending').hasClass('show')) {
        modalPending.hide();
    } else if ($('#modalCatatanPending').hasClass('show')) {
        modalCatatanPending.hide();
    } else {
        clearKeranjang();
    }
}
});
// Focus kembali ke input setelah modal ditutup
$('#modalPilihObat, #modalPilihSatuan, #modalPending, #modalCatatanPending').on('hidden.bs.modal', function() {
$('#inputKodeBarang').focus();
});
</script>
@endpush