@extends('layouts.app')

@section('title', 'Kasir POS')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                    <i class="bi bi-cart-check-fill"></i>
                </div>
                <div>
                    <h2 class="page-title mb-1">Point of Sale</h2>
                    <p class="page-subtitle mb-0">Kasir: {{ auth()->user()->name }} | Shift: #{{ $shift->id }}</p>
                </div>
            </div>
            <div>
                <span class="badge badge-shift">
                    <i class="bi bi-clock me-1"></i>
                    Shift Aktif
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Pilih Barang -->
        <div class="col-lg-7">
            <div class="card-custom">
                <div class="card-header">
                    <i class="bi bi-search me-2"></i>
                    <strong>Pilih Barang</strong>
                </div>
                <div class="card-body">
                    <!-- Search Bar -->
                    <div class="search-box mb-4">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="searchBarang" class="form-control search-input" 
                               placeholder="Cari barang (nama atau kode barang)...">
                    </div>
                    
                    <!-- List Barang -->
                    <div id="listBarang" class="row g-3">
                        @forelse($barang as $item)
                        <div class="col-xl-3 col-lg-4 col-md-6 barang-col">
                            <div class="barang-card" data-id="{{ $item->id }}" 
                                 data-nama="{{ strtolower($item->nama_barang) }}" 
                                 data-kode="{{ strtolower($item->kode_barang) }}">
                                <div class="barang-badge">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="barang-info">
                                    <span class="barang-kode">{{ $item->kode_barang }}</span>
                                    <h6 class="barang-nama">{{ $item->nama_barang }}</h6>
                                    <div class="barang-price">
                                        Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                    </div>
                                    <div class="barang-stock">
                                        <i class="bi bi-archive me-1"></i>
                                        Stok: <strong>{{ $item->stok }}</strong> {{ $item->satuan_terkecil }}
                                    </div>
                                </div>
                                <button class="btn-add-cart" onclick="tambahKeKeranjang({{ $item->id }})">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada barang tersedia</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Keranjang -->
        <div class="col-lg-5">
            <div class="card-custom cart-sticky">
                <div class="card-header">
                    <i class="bi bi-cart3 me-2"></i>
                    <strong>Keranjang Belanja</strong>
                    <button class="btn-clear-cart ms-auto" onclick="clearKeranjang()" style="display: none;">
                        <i class="bi bi-trash me-1"></i> Kosongkan
                    </button>
                </div>
                <div class="card-body">
                    <!-- Cart Items -->
                    <div class="cart-items" id="cartItems">
                        <div class="empty-cart">
                            <i class="bi bi-cart-x"></i>
                            <p>Keranjang masih kosong</p>
                            <small>Pilih barang untuk memulai transaksi</small>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotalText">Rp 0</span>
                        </div>
                        <div class="summary-row summary-total">
                            <strong>Total Bayar</strong>
                            <strong class="total-price">Rp <span id="totalHarga">0</span></strong>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="payment-section">
                        <div class="form-group-modern mb-3">
                            <label class="form-label">
                                <i class="bi bi-cash-stack me-2"></i>Uang Dibayar
                            </label>
                            <div class="input-with-icon">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" id="uangDibayar" class="form-control currency-input" 
                                       placeholder="0">
                            </div>
                        </div>

                        <div class="form-group-modern mb-3">
                            <label class="form-label">
                                <i class="bi bi-arrow-left-right me-2"></i>Kembalian
                            </label>
                            <div class="input-with-icon">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" id="kembalian" class="form-control currency-input" 
                                       value="0" readonly>
                            </div>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="quick-amount mb-3">
                            <button class="btn-quick-amount" onclick="setUangPas()">Uang Pas</button>
                            <button class="btn-quick-amount" onclick="addAmount(50000)">+50K</button>
                            <button class="btn-quick-amount" onclick="addAmount(100000)">+100K</button>
                        </div>

                        <!-- Checkout Button -->
                        <button class="btn-checkout" id="btnCheckout" onclick="prosesTransaksi()" disabled>
                            <i class="bi bi-check-circle me-2"></i>
                            Proses Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ MODAL PILIH SATUAN --}}
<div class="modal fade" id="modalPilihSatuan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i>
                    Pilih Satuan - <span id="modalNamaBarang"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="listSatuan"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* ... (CSS yang sama seperti sebelumnya) ... */

/* ✅ TAMBAHAN CSS UNTUK SATUAN */
.satuan-option {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.satuan-option:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.satuan-option .satuan-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.satuan-option .satuan-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.satuan-option .satuan-price {
    font-weight: 700;
    font-size: 1.25rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.satuan-badge {
    display: inline-block;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.cart-item-satuan {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

/* Sama seperti CSS sebelumnya */
.page-header {
    animation: fadeInDown 0.5s ease;
}

.badge-shift {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.5rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 1.1rem;
    z-index: 10;
}

.search-input {
    padding-left: 3.5rem !important;
    height: 56px;
    border-radius: 16px;
    border: 2px solid var(--border-color);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.barang-card {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 1.25rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.barang-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
    border-color: var(--primary-color);
}

.barang-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.2rem;
}

.barang-info {
    flex: 1;
}

.barang-kode {
    display: inline-block;
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.barang-nama {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0.5rem 0;
    line-height: 1.3;
    min-height: 2.6rem;
}

.barang-price {
    font-size: 1.1rem;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0.5rem 0;
}

.barang-stock {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

.btn-add-cart {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
    background: var(--primary-gradient);
    border: none;
    border-radius: 10px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.barang-card:hover .btn-add-cart {
    opacity: 1;
    transform: scale(1);
}

.btn-add-cart:hover {
    transform: scale(1.1);
}

.cart-sticky {
    position: sticky;
    top: 100px;
}

.btn-clear-cart {
    background: none;
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-clear-cart:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.cart-items {
    max-height: 320px;
    overflow-y: auto;
    margin-bottom: 1.5rem;
    padding-right: 0.5rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-cart i {
    font-size: 4rem;
    color: #e2e8f0;
    margin-bottom: 1rem;
}

.empty-cart p {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.cart-item {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.cart-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.cart-item-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.75rem;
}

.cart-item-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-primary);
    flex: 1;
}

.btn-remove-item {
    background: rgba(239, 68, 68, 0.1);
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove-item:hover {
    background: #ef4444;
    color: white;
    transform: scale(1.1);
}

.cart-item-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border-radius: 10px;
    padding: 0.25rem;
}

.btn-qty {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--primary-gradient);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-qty:hover {
    transform: scale(1.1);
}

.qty-input {
    width: 50px;
    text-align: center;
    border: none;
    font-weight: 600;
    color: var(--text-primary);
}

.cart-item-price {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1rem;
}

.cart-summary {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    color: var(--text-secondary);
}

.summary-total {
    border-top: 2px solid var(--border-color);
    margin-top: 0.5rem;
    padding-top: 1rem;
    color: var(--text-primary);
}

.total-price {
    font-size: 1.75rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.currency-symbol {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 600;
    color: var(--text-secondary);
    z-index: 10;
}

.currency-input {
    padding-left: 3rem !important;
    font-weight: 600;
    font-size: 1.1rem;
}

.quick-amount {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.btn-quick-amount {
    background: white;
    border: 2px solid var(--border-color);
    padding: 0.75rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.btn-quick-amount:hover {
    background: var(--primary-gradient);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.btn-checkout {
    width: 100%;
    background: var(--primary-gradient);
    border: none;
    color: white;
    padding: 1.25rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-checkout:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);
}

.btn-checkout:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 5rem;
    color: #e2e8f0;
    margin-bottom: 1rem;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cart-items::-webkit-scrollbar {
    width: 6px;
}

.cart-items::-webkit-scrollbar-thumb {
    background: var(--primary-gradient);
    border-radius: 10px;
}

@media (max-width: 992px) {
    .cart-sticky {
        position: static;
    }
}
</style>

@endsection

@push('scripts')
<script>
let keranjang = [];
let currentBarangData = null; // Simpan data barang sementara untuk modal

// Search barang
$('#searchBarang').on('keyup', function() {
    let keyword = $(this).val().toLowerCase();
    
    $('.barang-col').each(function() {
        let nama = $(this).find('.barang-card').data('nama');
        let kode = $(this).find('.barang-card').data('kode');
        
        if (nama.includes(keyword) || kode.includes(keyword)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// ✅ Tambah ke keranjang - BUKA MODAL JIKA ADA SATUAN KONVERSI
function tambahKeKeranjang(barangId) {
    $.get('/penjualan/barang/' + barangId, function(data) {
        currentBarangData = data;
        
        // Jika ada satuan konversi, tampilkan modal
        if (data.satuan_konversi && data.satuan_konversi.length > 0) {
            tampilkanModalSatuan(data);
        } else {
            // Langsung tambah dengan satuan dasar
            tambahKeKeranjangDenganSatuan(data.satuan_dasar, data.harga_jual);
        }
    }).fail(function() {
        alert('Gagal mengambil data barang');
    });
}

// ✅ Tampilkan modal pilih satuan
function tampilkanModalSatuan(data) {
    $('#modalNamaBarang').text(data.nama_barang);
    
    let html = '';
    
    // Satuan dasar
    html += `
        <div class="satuan-option" onclick="pilihSatuan('${data.satuan_dasar}', ${data.harga_jual})">
            <div class="satuan-name">
                ${data.satuan_dasar}
                <span class="satuan-badge">Satuan Dasar</span>
            </div>
            <div class="satuan-info">1 ${data.satuan_dasar}</div>
            <div class="satuan-price">Rp ${formatRupiah(data.harga_jual)}</div>
        </div>
    `;
    
    // Satuan konversi
    data.satuan_konversi.forEach(function(satuan) {
        html += `
            <div class="satuan-option" onclick="pilihSatuan('${satuan.nama_satuan}', ${satuan.harga_jual})">
                <div class="satuan-name">${satuan.nama_satuan}</div>
                <div class="satuan-info">
                    ${satuan.jumlah_konversi} ${data.satuan_dasar} = 1 ${satuan.nama_satuan}
                </div>
                <div class="satuan-price">Rp ${formatRupiah(satuan.harga_jual)}</div>
            </div>
        `;
    });
    
    $('#listSatuan').html(html);
    
    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById('modalPilihSatuan'));
    modal.show();
}

// ✅ Pilih satuan dari modal
function pilihSatuan(namaSatuan, hargaSatuan) {
    tambahKeKeranjangDenganSatuan(namaSatuan, hargaSatuan);
    
    // Tutup modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalPilihSatuan'));
    modal.hide();
}

// ✅ Tambah ke keranjang dengan satuan tertentu
function tambahKeKeranjangDenganSatuan(satuan, harga) {
    const data = currentBarangData;
    
    // Cari apakah sudah ada di keranjang dengan satuan yang sama
    let existing = keranjang.find(item => 
        item.barang_id == data.id && item.satuan == satuan
    );
    
    if (existing) {
        // Hitung stok dalam satuan terkecil
        let konversi = 1;
        if (satuan !== data.satuan_dasar) {
            let satuanData = data.satuan_konversi.find(s => s.nama_satuan == satuan);
            if (satuanData) {
                konversi = satuanData.jumlah_konversi;
            }
        }
        
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
            nama_barang: data.nama_barang,
            qty: 1,
            satuan: satuan,
            harga: harga,
            stok: data.stok,
            satuan_dasar: data.satuan_dasar,
            satuan_konversi: data.satuan_konversi
        });
    }
    
    renderKeranjang();
}

// ✅ Render keranjang - TAMPILKAN SATUAN
function renderKeranjang() {
    let html = '';
    let total = 0;
    
    if (keranjang.length === 0) {
        html = `
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <p>Keranjang masih kosong</p>
                <small>Pilih barang untuk memulai transaksi</small>
            </div>
        `;
        $('.btn-clear-cart').hide();
        $('#btnCheckout').prop('disabled', true);
    } else {
        keranjang.forEach((item, index) => {
            let subtotal = item.qty * item.harga;
            total += subtotal;
            
            html += `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <div>
                            <div class="cart-item-name">${item.nama_barang}</div>
                            <div class="cart-item-satuan">
                                <i class="bi bi-box-seam me-1"></i>${item.satuan}
                                <span class="text-muted">@ Rp ${formatRupiah(item.harga)}</span>
                            </div>
                        </div>
                        <button class="btn-remove-item" onclick="hapusItem(${index})">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="cart-item-controls">
                        <div class="qty-control">
                            <button class="btn-qty" onclick="updateQty(${index}, ${item.qty - 1})">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" value="${item.qty}" class="qty-input" readonly>
                            <button class="btn-qty" onclick="updateQty(${index}, ${item.qty + 1})">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="cart-item-price">
                            Rp ${formatRupiah(subtotal)}
                        </div>
                    </div>
                </div>
            `;
        });
        $('.btn-clear-cart').show();
        $('#btnCheckout').prop('disabled', false);
    }
    
    $('#cartItems').html(html);
    $('#subtotalText').text('Rp ' + formatRupiah(total));
    $('#totalHarga').text(formatRupiah(total));
    
    hitungKembalian();
}

// ✅ Update qty - CEK STOK PER SATUAN
function updateQty(index, newQty) {
    if (newQty < 1) {
        hapusItem(index);
        return;
    }
    
    const item = keranjang[index];
    
    // Hitung konversi ke satuan terkecil
    let konversi = 1;
    if (item.satuan !== item.satuan_dasar && item.satuan_konversi) {
        let satuanData = item.satuan_konversi.find(s => s.nama_satuan == item

        .satuan);
if (satuanData) {
konversi = satuanData.jumlah_konversi;
}
}
let stokDalamSatuan = Math.floor(item.stok / konversi);

if (newQty > stokDalamSatuan) {
    alert(`Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${item.satuan}`);
    return;
}

keranjang[index].qty = newQty;
renderKeranjang();
}
function hapusItem(index) {
keranjang.splice(index, 1);
renderKeranjang();
}
function clearKeranjang() {
if (confirm('Kosongkan semua barang di keranjang?')) {
keranjang = [];
renderKeranjang();
$('#uangDibayar').val('');
}
}
$('#uangDibayar').on('keyup', function() {
let value = $(this).val().replace(/\D/g, '');
$(this).val(formatRupiah(value));
hitungKembalian();
});
function hitungKembalian() {
let total = keranjang.reduce((sum, item) => sum + (item.qty * item.harga), 0);
let uangDibayar = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;
let kembalian = uangDibayar - total;
$('#kembalian').val(formatRupiah(kembalian > 0 ? kembalian : 0));

if (uangDibayar >= total && total > 0) {
    $('#btnCheckout').prop('disabled', false);
} else {
    $('#btnCheckout').prop('disabled', true);
}
}
function setUangPas() {
let total = keranjang.reduce((sum, item) => sum + (item.qty * item.harga), 0);
$('#uangDibayar').val(formatRupiah(total));
hitungKembalian();
}
function addAmount(amount) {
let current = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;
$('#uangDibayar').val(formatRupiah(current + amount));
hitungKembalian();
}
function prosesTransaksi() {
if (keranjang.length === 0) {
alert('Keranjang masih kosong!');
return;
}
let total = keranjang.reduce((sum, item) => sum + (item.qty * item.harga), 0);
let uangDibayar = parseInt($('#uangDibayar').val().replace(/\D/g, '')) || 0;

if (uangDibayar < total) {
    alert('Uang tidak cukup!');
    return;
}

$('#btnCheckout').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Memproses...');

$.ajax({
    url: '/penjualan/store',
    method: 'POST',
    data: {
        items: keranjang,
        total_bayar: total,
        uang_dibayar: uangDibayar,
        diskon: 0,                      
        metode_pembayaran: 'cash',      
        _token: $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
        alert('Transaksi berhasil! Invoice: ' + response.invoice);
        
        if (confirm('Cetak struk?')) {
            window.open('/penjualan/print/' + response.penjualan_id, '_blank');
        }
        
        keranjang = [];
        renderKeranjang();
        $('#uangDibayar').val('');
        $('#btnCheckout').html('<i class="bi bi-check-circle me-2"></i>Proses Pembayaran');
    },
    error: function(xhr) {
        alert('Error: ' + (xhr.responseJSON?.error || 'Terjadi kesalahan'));
        $('#btnCheckout').prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Proses Pembayaran');
    }
});
}
function formatRupiah(angka) {
return new Intl.NumberFormat('id-ID').format(angka);
}
</script>
@endpush