@extends('layouts.app')

@section('title', 'Kasir POS')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold">No Transaksi: <span class="text-primary">Auto</span></h5>
            <small class="text-muted">{{ date('d/m/Y') }} | Kasir: {{ auth()->user()->name }}</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div>
                <label class="form-label mb-1 small">Metode Pembayaran <kbd>F7</kbd></label>
                <select class="form-select form-select-sm" id="metodePembayaran" style="min-width: 200px;" tabindex="-1">
                    <option value="cash">💵 Cash</option>
                    <option value="transfer">🏦 Transfer Bank</option>
                    <option value="qris">📱 QRIS</option>
                    <option value="debit">💳 Debit Card</option>
                    <option value="credit">💳 Credit Card</option>
                </select>

                <!-- Input Nomor Referensi (untuk non-cash) -->
                <div class="mt-2" id="divReferensi" style="display: none;">
                    <label class="form-label mb-1 small">No. Referensi / Approval Code</label>
                    <input type="text" class="form-control form-control-sm" id="nomorReferensi" 
                        placeholder="Masukkan nomor referensi..." tabindex="-1">
                </div>
            </div>
            <div>
                <label class="form-label mb-1 small">Sales</label>
                <select class="form-select form-select-sm" style="min-width: 150px;" tabindex="-1">
                    <option>-</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <!-- LEFT: Input & Table -->
        <div class="col-lg-8">
            <!-- Input Barang -->
            <div class="card shadow-sm mb-2">
                <div class="card-body p-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label mb-1 small fw-bold">Jumlah <kbd>F1</kbd></label>
                            <input type="number" id="inputJumlah" class="form-control" value="1" min="1" tabindex="-1">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label mb-1 small fw-bold">Kode Item / Barcode <kbd>ENTER</kbd></label>
                            <input type="text" id="inputKodeBarang" class="form-control" 
                                   placeholder="Scan barcode atau ketik kode..." autofocus>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="bukaModalBarang()" tabindex="-1">
                                <i class="bi bi-search me-1"></i>Detail Item <kbd>F2</kbd>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Belanja -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive" style="height: calc(100vh - 280px); overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" id="cartTable">
                            <thead class="table-secondary sticky-top">
                                <tr>
                                    <th width="3%" class="text-center">No</th>
                                    <th width="12%">Kode Item</th>
                                    <th width="30%">Keterangan</th>
                                    <th width="10%" class="text-center">Jumlah</th>
                                    <th width="10%" class="text-center">Satuan</th>
                                    <th width="13%" class="text-end">Harga</th>
                                    <th width="8%" class="text-center">Pot (%)</th>
                                    <th width="14%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                                        Belum ada item
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Bottom -->
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-info" onclick="tampilkanPending()" tabindex="-1">
                    <i class="bi bi-list-ul me-1"></i>Lihat Pending <kbd>F6</kbd>
                </button>
                <button class="btn btn-danger ms-auto" onclick="clearKeranjang()" tabindex="-1">
                    <i class="bi bi-x-circle me-1"></i>Batal <kbd>ESC</kbd>
                </button>
            </div>
        </div>

        <!-- RIGHT: Payment -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <!-- Total Besar -->
                    <div class="text-center bg-dark text-white p-4 rounded mb-3">
                        <div class="small mb-1">GRAND TOTAL</div>
                        <h1 class="display-3 fw-bold mb-0" id="grandTotalDisplay">0</h1>
                    </div>

                    <!-- Detail Perhitungan -->
                    <div class="row g-2 mb-3 small">
                        <div class="col-6">
                            <label class="form-label mb-1">Sub Total</label>
                            <input type="text" class="form-control form-control-sm text-end" id="subTotal" value="0" readonly tabindex="-1">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">Potongan <kbd>F4</kbd></label>
                            <input type="text" class="form-control form-control-sm text-end" id="potongan" value="0" tabindex="-1">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">Pajak</label>
                            <input type="text" class="form-control form-control-sm text-end" id="pajak" value="0" readonly tabindex="-1">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">Biaya Lain</label>
                            <input type="text" class="form-control form-control-sm text-end" id="biayaLain" value="0" tabindex="-1">
                        </div>
                    </div>

                    <!-- Pembayaran -->
                    <div class="mb-2">
                        <label class="form-label mb-1 fw-bold">PPn</label>
                        <select class="form-select form-select-sm" tabindex="-1">
                            <option>Non</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1 fw-bold">Keterangan</label>
                        <textarea class="form-control form-control-sm" rows="2" id="keterangan" tabindex="-1"></textarea>
                    </div>

                    <!-- Bayar -->
                    <div class="mb-3">
                        <label class="form-label mb-1 fw-bold" id="labelBayar">Bayar <kbd>F3</kbd></label>
                        <input type="text" id="uangDibayar" class="form-control form-control-lg text-end fw-bold bg-light" 
                               placeholder="0" tabindex="-1">
                    </div>

                    <!-- Quick Amount (only for cash) -->
                    <div class="row g-1 mb-3" id="divQuickAmount">
                        <div class="col-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="setUangPas()" tabindex="-1">Uang Pas</button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="addAmount(10000)" tabindex="-1">10K</button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="addAmount(50000)" tabindex="-1">50K</button>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="addAmount(100000)" tabindex="-1">100K</button>
                        </div>
                    </div>

                    <!-- Kembalian (only for cash) -->
                    <div class="mb-3" id="divKembalian">
                        <label class="form-label mb-1 fw-bold">Kembalian</label>
                        <input type="text" id="kembalian" class="form-control form-control-lg text-end fw-bold bg-warning" 
                               value="0" readonly tabindex="-1">
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning btn-lg" onclick="simpanPending()" tabindex="-1">
                            <i class="bi bi-save me-2"></i>Pending <kbd>F5</kbd>
                        </button>
                        <button class="btn btn-secondary btn-lg" onclick="bukaModalReturn()" tabindex="-1">
                            <i class="bi bi-arrow-return-left me-2"></i>Return <kbd>F8</kbd>
                        </button>
                        <button class="btn btn-success btn-lg" id="btnSimpan" onclick="prosesTransaksi()" tabindex="-1">
                            <i class="bi bi-check-circle me-2"></i>Simpan <kbd>CTRL+S</kbd>
                        </button>
                        <button class="btn btn-danger btn-lg" onclick="tutupTransaksi()" tabindex="-1">
                            <i class="bi bi-box-arrow-right me-2"></i>Tutup <kbd>F10</kbd>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keyboard Shortcuts Help -->
<div id="keyboardHelp" class="position-fixed bg-dark text-white p-2 rounded" 
     style="bottom: 10px; right: 10px; font-size: 0.75rem; opacity: 0.7; z-index: 1000;">
    <div><kbd>F1</kbd> Jumlah | <kbd>F2</kbd> Cari | <kbd>F3</kbd> Bayar | <kbd>F4</kbd> Diskon</div>
    <div><kbd>F5</kbd> Pending | <kbd>F6</kbd> Lihat Pending | <kbd>F7</kbd> Metode | <kbd>F8</kbd> Return</div>
    <div><kbd>CTRL+S</kbd> Simpan | <kbd>ESC</kbd> Batal | <kbd>F10</kbd> Tutup</div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>

<!-- Modal Pilih Obat/Barang -->
<div class="modal fade" id="modalPilihObat" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-xl-down" style="max-width: 98%; margin: 1vh auto;">
        <div class="modal-content" style="height: 98vh;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-search me-2"></i>PILIH OBAT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="overflow: hidden; display: flex; flex-direction: column;">
                <input type="text" id="searchObat" class="form-control form-control-lg mb-3" 
                       placeholder="Cari obat... (ESC untuk tutup)" autofocus>

                <div class="table-responsive" style="flex: 1; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="3%">No</th>
                                <th width="8%">Kode</th>
                                <th width="10%">Barcode</th>
                                <th width="18%">Nama Obat</th>
                                <th width="8%" class="text-center">Stok</th>
                                <th width="19%">Satuan 1 (Dasar)</th>
                                <th width="21%">Satuan 2</th>
                                <th width="21%">Satuan 3</th>
                            </tr>
                        </thead>
                        <tbody id="listObat">
                            @foreach($barang as $key => $item)
                            <tr class="obat-row" 
                                data-id="{{ $item->id }}"
                                data-nama="{{ strtolower($item->nama_barang) }}"
                                data-kode="{{ strtolower($item->kode_barang) }}" 
                                data-barcode="{{ strtolower($item->barcode) }}"
                                onclick="pilihBarang({{ $item->id }})">
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td><strong>{{ $item->kode_barang }}</strong></td>
                                <td>{{ $item->barcode ?? '-' }}</td>
                                <td><strong>{{ $item->nama_barang }}</strong></td>
                                
                                {{-- STOK --}}
                                <td class="text-center">
                                    @php
                                        $badgeClass = 'bg-success';
                                        if ($item->stok <= 0) {
                                            $badgeClass = 'bg-dark';
                                        } elseif ($item->stok <= $item->stok_minimal) {
                                            $badgeClass = 'bg-danger';
                                        } elseif ($item->stok <= ($item->stok_minimal * 2)) {
                                            $badgeClass = 'bg-warning text-dark';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} fw-bold">
                                        {{ number_format($item->stok, 0, ',', '.') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $item->satuan_terkecil }}</small>
                                </td>
                                
                                {{-- SATUAN 1 (DASAR) --}}
                                <td>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-success">{{ $item->satuan_terkecil }}</span>
                                            <small class="text-muted d-block">(Stok: {{ number_format($item->stok, 0, ',', '.') }})</small>
                                        </div>
                                        <strong class="text-success">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</strong>
                                    </div>
                                </td>
                                
                                {{-- SATUAN 2 (KONVERSI INDEX 0) --}}
                                <td>
                                    @php
                                        $satuanKonversi = $item->satuanKonversi;
                                        $satuan2 = $satuanKonversi->get(0);
                                    @endphp
                                    
                                    @if($satuan2)
                                        @php
                                            $stokSatuan2 = floor($item->stok / $satuan2->jumlah_konversi);
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary">{{ $satuan2->nama_satuan }}</span>
                                                <small class="text-muted d-block">({{ $satuan2->jumlah_konversi }} | Stok: {{ $stokSatuan2 }})</small>
                                            </div>
                                            <strong>Rp {{ number_format($satuan2->harga_jual, 0, ',', '.') }}</strong>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                {{-- SATUAN 3 (KONVERSI INDEX 1) --}}
                                <td>
                                    @php
                                        $satuan3 = $satuanKonversi->get(1);
                                    @endphp
                                    
                                    @if($satuan3)
                                        @php
                                            $stokSatuan3 = floor($item->stok / $satuan3->jumlah_konversi);
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary">{{ $satuan3->nama_satuan }}</span>
                                                <small class="text-muted d-block">({{ $satuan3->jumlah_konversi }} | Stok: {{ $stokSatuan3 }})</small>
                                            </div>
                                            <strong>Rp {{ number_format($satuan3->harga_jual, 0, ',', '.') }}</strong>
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
                    <i class="bi bi-info-circle me-1"></i> Gunakan angka <kbd>1</kbd> <kbd>2</kbd> <kbd>3</kbd> untuk pilih cepat
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

<!-- Modal Return Barang -->
<div class="modal fade" id="modalReturn" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>RETURN BARANG</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">No Nota</label>
                    <input type="text" class="form-control" id="returnNota" placeholder="Masukkan nomor nota (ENTER untuk cari)">
                </div>
                <button class="btn btn-primary" onclick="cariNota()">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                
                <div id="returnDetail" class="mt-3" style="display: none;">
                    <h6>Detail Transaksi</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAllReturn"></th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="returnItems"></tbody>
                        </table>
                    </div>
                    <button class="btn btn-danger" onclick="prosesReturn()">
                        <i class="bi bi-check-circle me-1"></i>Proses Return
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pending -->
<div class="modal fade" id="modalPending" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>TRANSAKSI PENDING</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>Jumlah Item</th>
                                <th>Total</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="listPending">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    font-size: 0.9rem;
}

.table-sm {
    font-size: 0.85rem;
}

.obat-row {
    cursor: pointer;
}

.obat-row:hover {
    background-color: #e7f3ff !important;
}

#tableSatuan tbody tr {
    cursor: pointer;
}

#tableSatuan tbody tr:hover {
    background-color: #e7f3ff;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

kbd {
    background-color: #343a40;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.75rem;
}

/* Highlight untuk active focus */
input:focus, textarea:focus, select:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

/* Animation untuk toast */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-notification {
    animation: slideInRight 0.3s ease-out;
}
</style>

@endsection

@push('scripts')
<script>
let keranjang = [];
let currentBarangData = null;
let pendingList = [];
let modalPilihObat, modalPilihSatuan, modalReturn, modalPending;
let isProcessing = false;

$(document).ready(function() {
    modalPilihObat = new bootstrap.Modal(document.getElementById('modalPilihObat'));
    modalPilihSatuan = new bootstrap.Modal(document.getElementById('modalPilihSatuan'));
    modalReturn = new bootstrap.Modal(document.getElementById('modalReturn'));
    modalPending = new bootstrap.Modal(document.getElementById('modalPending'));
    
    loadPendingFromStorage();
    setupKeyboardShortcuts();
    
    $('#metodePembayaran').on('change', function() {
        const metode = $(this).val();
        
        if (metode === 'cash') {
            $('#divReferensi').hide();
            $('#nomorReferensi').val('');
            $('#divQuickAmount').show();
            $('#divKembalian').show();
            $('#labelBayar').text('Bayar [F3]');
        } else {
            $('#divReferensi').show();
            $('#divQuickAmount').hide();
            $('#divKembalian').hide();
            
            const grandTotal = parseFloat($('#grandTotalDisplay').text().replace(/\./g, '')) || 0;
            $('#uangDibayar').val(formatRupiah(grandTotal));
            
            if (metode === 'transfer') {
                $('#labelBayar').text('Jumlah Transfer');
            } else if (metode === 'qris') {
                $('#labelBayar').text('Jumlah Pembayaran QRIS');
            } else {
                $('#labelBayar').text('Jumlah Pembayaran');
            }
        }
        
        hitungKembalian();
        $('#inputKodeBarang').focus();
    });
    
    $(document).on('keyup input', '#searchObat', function() {
        let keyword = $(this).val().toLowerCase().trim();
        
        $('.obat-row').each(function() {
            let nama = ($(this).data('nama') || '').toString().toLowerCase();
            let kode = ($(this).data('kode') || '').toString().toLowerCase();
            let barcode = ($(this).data('barcode') || '').toString().toLowerCase();
            
            if (nama.includes(keyword) || kode.includes(keyword) || barcode.includes(keyword)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#checkAllReturn').on('change', function() {
        $('.return-check').prop('checked', $(this).is(':checked'));
    });
    
    $('#uangDibayar, #potongan, #biayaLain').on('input', function() {
        hitungKembalian();
    });
    
    $('#returnNota').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            cariNota();
        }
    });
    
    $('#modalPilihObat, #modalPilihSatuan').on('hidden.bs.modal', function() {
        $('#inputKodeBarang').focus();
    });
    
    $('#inputKodeBarang').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const kode = $(this).val().trim();
            
            if (kode) {
                $(this).prop('disabled', true);
                
                $.get('/penjualan/search-barang', { q: kode }, function(data) {
                    if (data.length === 1) {
                        tambahBarangOtomatis(data[0].id);
                    } else if (data.length > 1) {
                        bukaModalBarang();
                        $('#searchObat').val(kode).trigger('keyup');
                        $('#inputKodeBarang').prop('disabled', false);
                    } else {
                        showErrorToast('❌ Barang tidak ditemukan!');
                        $('#inputKodeBarang').val('').prop('disabled', false).focus();
                    }
                }).fail(function() {
                    showErrorToast('❌ Terjadi kesalahan!');
                    $('#inputKodeBarang').val('').prop('disabled', false).focus();
                });
            } else {
                bukaModalBarang();
            }
        }
    });
});

function setupKeyboardShortcuts() {
    $(document).on('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            $('#inputJumlah').focus().select();
            return;
        }
        
        if (e.key === 'F2') {
            e.preventDefault();
            bukaModalBarang();
            return;
        }
        
        if (e.key === 'F3') {
            e.preventDefault();
            $('#uangDibayar').focus().select();
            return;
        }
        
        if (e.key === 'F4') {
            e.preventDefault();
            $('#potongan').focus().select();
            return;
        }
        
        if (e.key === 'F5') {
            e.preventDefault();
            simpanPending();
            return;
        }
        
        if (e.key === 'F6') {
            e.preventDefault();
            tampilkanPending();
            return;
        }
        
        if (e.key === 'F7') {
            e.preventDefault();
            $('#metodePembayaran').focus();
            return;
        }
        
        if (e.key === 'F8') {
            e.preventDefault();
            bukaModalReturn();
            return;
        }
        
        if (e.key === 'F10') {
            e.preventDefault();
            tutupTransaksi();
            return;
        }
        
        if (e.key === 'Escape') {
            e.preventDefault();
            if ($('.modal.show').length > 0) {
                $('.modal.show').modal('hide');
            } else {
                clearKeranjang();
            }
            return;
        }
        
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            prosesTransaksi();
            return;
        }
        
        if ($('#modalPilihSatuan').hasClass('show') && ['1', '2', '3'].includes(e.key)) {
            e.preventDefault();
            const row = $(`#tableSatuan tbody tr:nth-child(${e.key})`);
            if (row.length) {
                row.click();
            }
            return;
        }
    });
}

function bukaModalBarang() {
    modalPilihObat.show();
    setTimeout(() => $('#searchObat').focus(), 500);
}

function bukaModalReturn() {
    modalReturn.show();
    setTimeout(() => $('#returnNota').focus(), 500);
}

function pilihBarang(barangId) {
    $.get('/penjualan/barang/' + barangId, function(data) {
        currentBarangData = data;
        modalPilihObat.hide();
        tampilkanPilihSatuan(data);
    }).fail(function() {
        showErrorToast('❌ Gagal mengambil data barang');
    });
}

function tampilkanPilihSatuan(data) {
    $('#modalNamaBarang').text(data.nama_barang);
    
    let html = '';
    let counter = 1;
    
    html += `
        <tr onclick="tambahKeKeranjang('${data.satuan_dasar}', ${data.harga_jual}, 1)">
            <td class="text-center"><kbd>${counter}</kbd></td>
            <td><strong>${data.satuan_dasar}</strong> <span class="badge bg-success">Dasar</span></td>
            <td class="text-center">1</td>
            <td class="text-end"><strong>Rp ${formatRupiah(data.harga_jual)}</strong></td>
        </tr>
    `;
    counter++;
    
    if (data.satuan_konversi && data.satuan_konversi.length > 0) {
        data.satuan_konversi.forEach(function(satuan) {
            if (counter <= 3) {
                html += `
                    <tr onclick="tambahKeKeranjang('${satuan.nama_satuan}', ${satuan.harga_jual}, ${satuan.jumlah_konversi})">
                        <td class="text-center"><kbd>${counter}</kbd></td>
                        <td><strong>${satuan.nama_satuan}</strong></td>
                        <td class="text-center">${satuan.jumlah_konversi} ${data.satuan_dasar}</td>
                        <td class="text-end"><strong>Rp ${formatRupiah(satuan.harga_jual)}</strong></td>
                    </tr>
                `;
                counter++;
            }
        });
    }
    
    $('#listSatuan').html(html);
    modalPilihSatuan.show();
}

function tambahBarangOtomatis(barangId) {
    $.get('/penjualan/barang/' + barangId, function(data) {
        const qty = parseInt($('#inputJumlah').val()) || 1;
        
        if (data.stok <= 0) {
            showErrorToast(`❌ Stok ${data.nama_barang} habis!`);
            $('#inputKodeBarang').val('').prop('disabled', false).focus();
            return;
        }
        
        let satuanTerpilih, hargaTerpilih, konversiTerpilih;
        const satuanDefault = data.satuan_konversi?.find(s => s.is_default === true || s.is_default === 1);
        
        if (satuanDefault) {
            satuanTerpilih = satuanDefault.nama_satuan;
            hargaTerpilih = satuanDefault.harga_jual;
            konversiTerpilih = satuanDefault.jumlah_konversi;
        } else {
            satuanTerpilih = data.satuan_dasar;
            hargaTerpilih = data.harga_jual;
            konversiTerpilih = 1;
        }
        
        let stokDalamSatuan = Math.floor(data.stok / konversiTerpilih);
        
        if (stokDalamSatuan <= 0) {
            showErrorToast(`❌ Stok ${data.nama_barang} tidak cukup!`);
            $('#inputKodeBarang').val('').prop('disabled', false).focus();
            return;
        }
        
        let existing = keranjang.find(item => 
            item.barang_id == data.id && item.satuan == satuanTerpilih
        );
        
        if (existing) {
            if (existing.qty + qty <= stokDalamSatuan) {
                existing.qty += qty;
            } else {
                showErrorToast(`❌ Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${satuanTerpilih}`);
                $('#inputKodeBarang').val('').prop('disabled', false).focus();
                return;
            }
        } else {
            if (qty <= stokDalamSatuan) {
                keranjang.push({
                    barang_id: data.id,
                    kode_barang: data.kode_barang,
                    nama_barang: data.nama_barang,
                    qty: qty,
                    satuan: satuanTerpilih,
                    harga: hargaTerpilih,
                    diskon: 0,
                    stok: data.stok,
                    konversi: konversiTerpilih,
                    satuan_dasar: data.satuan_dasar,
                    harga_dasar: data.harga_jual,
                    satuan_konversi: data.satuan_konversi
                });
            } else {
                showErrorToast(`❌ Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${satuanTerpilih}`);
                $('#inputKodeBarang').val('').prop('disabled', false).focus();
                return;
            }
        }
        
        renderKeranjang();
        $('#inputJumlah').val(1);
        $('#inputKodeBarang').val('').prop('disabled', false).focus();
        showSuccessToast(`✓ ${data.nama_barang} ditambahkan (${qty} ${satuanTerpilih})`);
        
    }).fail(function() {
        showErrorToast('❌ Gagal mengambil data barang');
        $('#inputKodeBarang').val('').prop('disabled', false).focus();
    });
}

function tambahKeKeranjang(satuan, harga, konversi) {
    const data = currentBarangData;
    const qty = parseInt($('#inputJumlah').val()) || 1;
    
    if (!data || !data.id) {
        showErrorToast('❌ Data barang tidak lengkap!');
        return;
    }
    
    let existing = keranjang.find(item => 
        item.barang_id == data.id && item.satuan == satuan
    );
    
    if (existing) {
        let stokDalamSatuan = Math.floor(data.stok / konversi);
        if (existing.qty + qty <= stokDalamSatuan) {
            existing.qty += qty;
        } else {
            showErrorToast(`❌ Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${satuan}`);
            return;
        }
    } else {
        keranjang.push({
            barang_id: data.id,
            kode_barang: data.kode_barang,  
            nama_barang: data.nama_barang,
            qty: qty,
            satuan: satuan,
            harga: harga,  
            diskon: 0,
            stok: data.stok,
            konversi: konversi,
            satuan_dasar: data.satuan_dasar,
            harga_dasar: data.harga_jual,
            satuan_konversi: data.satuan_konversi
        });
    }
    
    modalPilihSatuan.hide();
    renderKeranjang();
    $('#inputJumlah').val(1);
    showSuccessToast(`✓ ${data.nama_barang} ditambahkan (${qty} ${satuan})`);
}

function gantiSatuan(index) {
    const item = keranjang[index];
    currentBarangData = item;
    
    $('#modalNamaBarang').text(item.nama_barang);
    
    let html = '';
    let hargaDasar = item.harga_dasar || item.harga;
    let counter = 1;
    
    html += `
        <tr onclick="gantiSatuanItem(${index}, '${item.satuan_dasar}', ${hargaDasar}, 1)">
            <td class="text-center"><kbd>${counter}</kbd></td>
            <td><strong>${item.satuan_dasar}</strong> <span class="badge bg-success">Dasar</span></td>
            <td class="text-center">1</td>
            <td class="text-end"><strong>Rp ${formatRupiah(hargaDasar)}</strong></td>
        </tr>
    `;
    counter++;
    
    if (item.satuan_konversi && item.satuan_konversi.length > 0) {
        item.satuan_konversi.forEach(function(satuan) {
            if (counter <= 3) {
                html += `
                    <tr onclick="gantiSatuanItem(${index}, '${satuan.nama_satuan}', ${satuan.harga_jual}, ${satuan.jumlah_konversi})">
                        <td class="text-center"><kbd>${counter}</kbd></td>
                        <td><strong>${satuan.nama_satuan}</strong></td>
                        <td class="text-center">${satuan.jumlah_konversi} ${item.satuan_dasar}</td>
                        <td class="text-end"><strong>Rp ${formatRupiah(satuan.harga_jual)}</strong></td>
                    </tr>
                `;
                counter++;
            }
        });
    }
    
    $('#listSatuan').html(html);
    modalPilihSatuan.show();
}

function gantiSatuanItem(index, satuan, harga, konversi) {
    keranjang[index].satuan = satuan;
    keranjang[index].harga = harga;
    keranjang[index].konversi = konversi;
    
    modalPilihSatuan.hide();
    renderKeranjang();
    showSuccessToast('✓ Satuan berhasil diubah');
}

function renderKeranjang() {
    let html = '';
    
    if (keranjang.length === 0) {
        html = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Belum ada item
                </td>
            </tr>
        `;
    } else {
        keranjang.forEach((item, index) => {
            let total = item.qty * item.harga;
            let diskon = (total * item.diskon) / 100;
            let subtotal = total - diskon;
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${item.kode_barang}</td>
                    <td>${item.nama_barang}</td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.qty}" min="1" 
                               onchange="updateQty(${index}, this.value)"
                               style="width: 70px;" tabindex="-1">
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="gantiSatuan(${index})" tabindex="-1">
                            ${item.satuan}
                        </button>
                    </td>
                    <td class="text-end">${formatRupiah(item.harga)}</td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.diskon}" min="0" max="100" 
                               onchange="updateDiskon(${index}, this.value)"
                               style="width: 60px;" tabindex="-1">
                    </td>
                    <td class="text-end">
                        <strong>${formatRupiah(subtotal)}</strong>
                        <button class="btn btn-sm btn-danger ms-2" onclick="hapusItem(${index})" tabindex="-1">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#cartItems').html(html);
    updateTotal();
}

function updateQty(index, qty) {
    qty = parseInt(qty) || 1;
    const item = keranjang[index];
    
    let stokDalamSatuan = Math.floor(item.stok / item.konversi);
    
    if (qty > stokDalamSatuan) {
        showErrorToast(`❌ Stok tidak cukup! Maksimal: ${stokDalamSatuan} ${item.satuan}`);
        renderKeranjang();
        return;
    }
    
    keranjang[index].qty = qty;
    renderKeranjang();
}

function updateDiskon(index, diskon) {
    diskon = parseFloat(diskon) || 0;
    if (diskon < 0) diskon = 0;
    if (diskon > 100) diskon = 100;
    
    keranjang[index].diskon = diskon;
    renderKeranjang();
}

function hapusItem(index) {
    if (confirm('Hapus item ini?')) {
        keranjang.splice(index, 1);
        renderKeranjang();
        showSuccessToast('✓ Item dihapus dari keranjang');
    }
}

function clearKeranjang() {
    if (keranjang.length > 0 && confirm('Yakin ingin membatalkan transaksi ini?')) {
        keranjang = [];
        renderKeranjang();
        $('#inputJumlah').val(1);
        $('#inputKodeBarang').val('');
        $('#uangDibayar').val('');
        $('#potongan').val('0');
        $('#biayaLain').val('0');
        $('#keterangan').val('');
        $('#nomorReferensi').val('');
        $('#metodePembayaran').val('cash').trigger('change');
        showSuccessToast('✓ Transaksi dibatalkan');
    }
}

function updateTotal() {
    let subtotal = 0;
    
    keranjang.forEach(item => {
        let total = item.qty * item.harga;
        let diskon = (total * item.diskon) / 100;
        subtotal += (total - diskon);
    });
    
    let potongan = parseFloat($('#potongan').val()) || 0;
    let biayaLain = parseFloat($('#biayaLain').val()) || 0;
    let pajak = 0;
    
    let grandTotal = subtotal - potongan + biayaLain + pajak;
    
    $('#subTotal').val(formatRupiah(subtotal));
    $('#pajak').val(formatRupiah(pajak));
    $('#grandTotalDisplay').text(formatRupiah(grandTotal));
    
    const metode = $('#metodePembayaran').val();
    if (metode !== 'cash') {
        $('#uangDibayar').val(formatRupiah(grandTotal));
    }
    
    hitungKembalian();
}

function hitungKembalian() {
    const metode = $('#metodePembayaran').val();
    let grandTotal = parseFloat($('#grandTotalDisplay').text().replace(/\./g, '')) || 0;
    let uangDibayar = parseFloat($('#uangDibayar').val().replace(/\./g, '')) || 0;
    
    if (metode === 'cash') {
        let kembalian = uangDibayar - grandTotal;
        $('#kembalian').val(formatRupiah(kembalian >= 0 ? kembalian : 0));
    } else {
        $('#kembalian').val('0');
    }
}

function setUangPas() {
    let grandTotal = parseFloat($('#grandTotalDisplay').text().replace(/\./g, '')) || 0;
    $('#uangDibayar').val(formatRupiah(grandTotal));
    hitungKembalian();
    $('#inputKodeBarang').focus();
}

function addAmount(amount) {
    let current = parseFloat($('#uangDibayar').val().replace(/\./g, '')) || 0;
    $('#uangDibayar').val(formatRupiah(current + amount));
    hitungKembalian();
    $('#inputKodeBarang').focus();
}

function simpanPending() {
    if (keranjang.length === 0) {
        showErrorToast('❌ Keranjang masih kosong!');
        return;
    }
    
    const pending = {
        id: Date.now(),
        waktu: new Date().toLocaleString('id-ID'),
        items: JSON.parse(JSON.stringify(keranjang)),
        keterangan: $('#keterangan').val(),
        metode_pembayaran: $('#metodePembayaran').val(),
        nomor_referensi: $('#nomorReferensi').val()
    };
    
    pendingList.push(pending);
    savePendingToStorage();
    
    clearKeranjangTanpaKonfirmasi();
    showSuccessToast('✓ Transaksi berhasil disimpan ke pending!');
}

function clearKeranjangTanpaKonfirmasi() {
    keranjang = [];
    renderKeranjang();
    $('#inputJumlah').val(1);
    $('#inputKodeBarang').val('').focus();
    $('#uangDibayar').val('');
    $('#potongan').val('0');
    $('#biayaLain').val('0');
    $('#keterangan').val('');
    $('#nomorReferensi').val('');
    $('#metodePembayaran').val('cash').trigger('change');
}

function tampilkanPending() {
    let html = '';
    
    if (pendingList.length === 0) {
        html = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi pending</td></tr>';
    } else {
        pendingList.forEach((pending, index) => {
            let totalItem = pending.items.reduce((sum, item) => sum + item.qty, 0);
            let totalHarga = pending.items.reduce((sum, item) => {
                let total = item.qty * item.harga;
                let diskon = (total * item.diskon) / 100;
                return sum + (total - diskon);
            }, 0);
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${pending.waktu}</td>
                    <td>${totalItem} item</td>
                    <td class="text-end"><strong>${formatRupiah(totalHarga)}</strong></td>
                    <td>${pending.keterangan || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="muatPending(${index})">
                            <i class="bi bi-arrow-clockwise me-1"></i>Muat
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="hapusPending(${index})">
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

function muatPending(index) {
    const pending = pendingList[index];
    
    keranjang = JSON.parse(JSON.stringify(pending.items));
    $('#keterangan').val(pending.keterangan);
    $('#metodePembayaran').val(pending.metode_pembayaran).trigger('change');
    $('#nomorReferensi').val(pending.nomor_referensi);
    
    pendingList.splice(index, 1);
    savePendingToStorage();
    
    modalPending.hide();
    renderKeranjang();
    showSuccessToast('✓ Transaksi pending berhasil dimuat');
}

function hapusPending(index) {
    if (confirm('Hapus transaksi pending ini?')) {
        pendingList.splice(index, 1);
        savePendingToStorage();
        tampilkanPending();
        showSuccessToast('✓ Transaksi pending dihapus');
    }
}

function savePendingToStorage() {
    localStorage.setItem('pendingTransaksi', JSON.stringify(pendingList));
}

function loadPendingFromStorage() {
    const data = localStorage.getItem('pendingTransaksi');
    if (data) {
        pendingList = JSON.parse(data);
    }
}

function cariNota() {
    const nomorNota = $('#returnNota').val().trim();
    
    if (!nomorNota) {
        showErrorToast('❌ Masukkan nomor nota!');
        return;
    }
    
    $.get(`/penjualan/cari-nota/${nomorNota}`, function(response) {
        if (response.success) {
            let html = '';
            response.items.forEach((item, index) => {
                html += `
                    <tr>
                        <td><input type="checkbox" class="return-check" value="${item.id}"></td>
                        <td>${item.nama_barang}</td>
                        <td>${item.jumlah} ${item.satuan}</td>
                        <td class="text-end">${formatRupiah(item.harga_jual)}</td>
                        <td class="text-end"><strong>${formatRupiah(item.subtotal)}</strong></td>
                    </tr>
                `;
            });
            
            $('#returnItems').html(html);
            $('#returnDetail').show();
        } else {
            showErrorToast('❌ ' + response.message);
        }
    }).fail(function() {
        showErrorToast('❌ Nota tidak ditemukan!');
    });
}

function prosesReturn() {
    const selectedItems = [];
    $('.return-check:checked').each(function() {
        selectedItems.push($(this).val());
    });
    
    if (selectedItems.length === 0) {
        showErrorToast('❌ Pilih minimal 1 item untuk di-return!');
        return;
    }
    
    if (!confirm('Proses return item yang dipilih?')) {
        return;
    }
    
    $.ajax({
        url: '/penjualan/return',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            items: selectedItems
        },
        success: function(response) {
            if (response.success) {
                showSuccessToast('✓ Return berhasil diproses!');
                modalReturn.hide();
                $('#returnNota').val('');
                $('#returnDetail').hide();
            } else {
                showErrorToast('❌ ' + response.message);
            }
        },
        error: function() {
            showErrorToast('❌ Gagal memproses return!');
        }
    });
}

function prosesTransaksi() {
    if (isProcessing) return;
    
    if (keranjang.length === 0) {
        showErrorToast('❌ Keranjang masih kosong!');
        return;
    }
    
    const grandTotal = parseFloat($('#grandTotalDisplay').text().replace(/\./g, '')) || 0;
    const uangDibayar = parseFloat($('#uangDibayar').val().replace(/\./g, '')) || 0;
    const metode = $('#metodePembayaran').val();
    
    if (uangDibayar < grandTotal) {
        showErrorToast('❌ Uang yang dibayarkan kurang!');
        $('#uangDibayar').focus().select();
        return;
    }
    
    if (metode !== 'cash') {
        const nomorReferensi = $('#nomorReferensi').val().trim();
        if (!nomorReferensi) {
            showErrorToast('❌ Nomor referensi harus diisi untuk pembayaran non-tunai!');
            $('#nomorReferensi').focus();
            return;
        }
    }
    
    const potongan = parseFloat($('#potongan').val()) || 0;
    const biayaLain = parseFloat($('#biayaLain').val()) || 0;
    
    const data = {
        _token: '{{ csrf_token() }}',
        items: keranjang,
        total_bayar: grandTotal + potongan - biayaLain,
        uang_dibayar: uangDibayar,
        diskon: potongan,
        metode_pembayaran: metode,
        nomor_referensi: $('#nomorReferensi').val(),
        keterangan: $('#keterangan').val()
    };
    
    isProcessing = true;
    $('#btnSimpan').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Proses...');
    
    $.ajax({
        url: '/penjualan/store',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                showSuccessToastLarge(`✓ TRANSAKSI BERHASIL DISIMPAN!<br><strong>No. Invoice: ${response.invoice}</strong>`);
                
                clearKeranjangTanpaKonfirmasi();
                
                setTimeout(function() {
                    if (confirm('Apakah ingin cetak struk?')) {
                        window.open(`/penjualan/print/${response.penjualan_id}`, '_blank');
                    }
                }, 800);
            }
        },
        error: function(xhr) {
            showErrorToast('❌ ' + (xhr.responseJSON?.error || 'Terjadi kesalahan!'));
        },
        complete: function() {
            isProcessing = false;
            $('#btnSimpan').prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Simpan <kbd>CTRL+S</kbd>');
        }
    });
}

function tutupTransaksi() {
    if (keranjang.length > 0) {
        if (!confirm('Masih ada transaksi aktif. Yakin ingin keluar?')) {
            return;
        }
    }
    
    window.location.href = '/dashboard';
}

function showSuccessToast(message) {
    const toast = $(`
        <div class="alert alert-success alert-dismissible fade show shadow toast-notification" role="alert" style="min-width: 300px; max-width: 400px;">
            <i class="bi bi-check-circle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}

function showSuccessToastLarge(message) {
    const toast = $(`
        <div class="alert alert-success alert-dismissible fade show shadow toast-notification" role="alert" 
             style="min-width: 400px; max-width: 500px; font-size: 1.1rem; padding: 1.5rem;">
            <i class="bi bi-check-circle-fill me-2 fs-3"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 5000);
}

function showErrorToast(message) {
    const toast = $(`
        <div class="alert alert-danger alert-dismissible fade show shadow toast-notification" role="alert" style="min-width: 300px; max-width: 400px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 4000);
}

function showInfoToast(message) {
    const toast = $(`
        <div class="alert alert-info alert-dismissible fade show shadow toast-notification" role="alert" style="min-width: 300px; max-width: 400px;">
            <i class="bi bi-info-circle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}

function formatRupiah(angka) {
    return Math.floor(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
@endpush