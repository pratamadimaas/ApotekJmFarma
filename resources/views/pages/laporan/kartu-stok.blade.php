@extends('layouts.app')

@section('title', 'Kartu Stok Barang')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">📋 Kartu Stok Barang</h5>
                            <p class="text-sm mb-0 text-muted">Riwayat keluar-masuk stok per barang</p>
                        </div>
                        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Form Filter --}}
                    <form method="GET" action="{{ route('laporan.kartuStok') }}" class="mb-4" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Cari Barang</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           id="searchBarang" 
                                           class="form-control" 
                                           placeholder="Ketik nama atau kode barang..."
                                           autocomplete="off"
                                           value="{{ $barang ? $barang->kode_barang . ' - ' . $barang->nama_barang : '' }}">
                                    <input type="hidden" name="barang_id" id="barang_id" value="{{ request('barang_id') }}">
                                    
                                    {{-- Dropdown hasil pencarian --}}
                                    <div id="searchResults" class="list-group position-absolute w-100" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                                </div>
                                <small class="text-muted">Contoh: ketik "amoxi" untuk mencari Amoxicillin</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control" 
                                    value="{{ $tanggalDari }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control" 
                                    value="{{ $tanggalSampai }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($barang)
                        {{-- Header Info Barang --}}
                        <div class="card mb-3 border border-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-1 fw-bold">{{ $barang->nama_barang }}</h5>
                                        <p class="mb-0">
                                            <span class="badge bg-white text-dark fw-bold">{{ $barang->kode_barang }}</span>
                                            <span class="ms-2">Kemasan: <strong>{{ $barang->satuan_terkecil }}</strong></span>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <small class="d-block opacity-75">Periode:</small>
                                        <strong>{{ \Carbon\Carbon::parse($tanggalDari)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ INFO STOK AWAL --}}
                        <div class="alert alert-info d-flex align-items-center mb-3" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Stok Awal Periode ({{ \Carbon\Carbon::parse($tanggalDari)->subDay()->format('d/m/Y') }}):</strong>
                                <span class="ms-2 badge bg-primary">{{ number_format($stokAwal, 0, ',', '.') }} {{ $barang->satuan_terkecil }}</span>
                            </div>
                        </div>

                        {{-- Tabel Kartu Stok --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="10%">Tanggal</th>
                                        <th width="30%">Keterangan</th>
                                        <th class="text-center" width="12%">Masuk</th>
                                        <th class="text-center" width="12%">Keluar</th>
                                        <th class="text-center" width="12%">Sisa Stok</th>
                                        <th class="text-center" width="12%">Paraf</th>
                                        <th class="text-center" width="12%">ED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- ✅ TAMPILKAN SEMUA TRANSAKSI (TERMASUK SALDO AWAL JIKA ADA) --}}
                                    @forelse($kartuStok as $item)
                                        <tr class="{{ $item['type'] === 'saldo_awal' ? 'table-primary fw-bold' : '' }}">
                                            <td class="text-center">
                                                <small>{{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                @if($item['type'] === 'saldo_awal')
                                                    <span class="fw-bold">{{ $item['keterangan'] }}</span>
                                                @else
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                        {{ $item['nomor'] }}
                                                    </small>
                                                    <span class="fw-semibold">{{ $item['keterangan'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item['masuk'] !== '-')
                                                    <span class="badge bg-success">{{ $item['masuk'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item['keluar'] !== '-')
                                                    <span class="badge bg-danger">{{ $item['keluar'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ number_format($item['sisa'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">{{ $item['paraf'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($item['ed'] && $item['ed'] !== '-')
                                                    <small class="badge bg-warning text-dark">{{ $item['ed'] }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Tidak ada data transaksi pada periode yang dipilih
                                            </td>
                                        </tr>
                                    @endforelse

                                    {{-- ✅ BARIS STOK AKHIR (hanya jika ada transaksi) --}}
                                    @if($kartuStok->isNotEmpty())
                                        <tr class="table-success fw-bold">
                                            <td class="text-center">{{ \Carbon\Carbon::parse($tanggalSampai)->format('d/m/Y') }}</td>
                                            <td>STOK AKHIR PERIODE</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">{{ number_format($stokAkhir, 0, ',', '.') }}</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Summary Cards --}}
                        <div class="row mt-4 g-3">
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Stok Awal</small>
                                        <h4 class="text-info mb-0">
                                            {{ number_format($stokAwal, 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Total Masuk</small>
                                        <h4 class="text-success mb-0">
                                            {{ number_format(abs($kartuStok->where('type', 'masuk')->sum('qty_dasar')), 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Total Keluar</small>
                                        <h4 class="text-danger mb-0">
                                            {{ number_format(abs($kartuStok->where('type', 'keluar')->sum('qty_dasar')), 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center py-3">
                                        <small class="text-muted d-block mb-1">Stok Akhir Periode</small>
                                        <h4 class="text-primary mb-0">{{ number_format($stokAkhir, 0, ',', '.') }}</h4>
                                        <small class="text-muted">{{ $barang->satuan_terkecil }}</small>
                                        <div class="mt-2">
                                            <small class="text-muted">Stok Real-time: <strong>{{ number_format($barang->stok, 0, ',', '.') }}</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Export --}}
                        <div class="mt-4 text-end">
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Cetak Kartu Stok
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted fs-5">Cari dan pilih barang untuk melihat kartu stok</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    .btn, .card-header a, nav, footer, form, .alert {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 2px solid #000 !important;
        page-break-inside: avoid;
    }
    table {
        font-size: 10pt;
    }
    .badge {
        border: 1px solid #000;
    }
    .table-primary {
        background-color: #cfe2ff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Style untuk search results */
#searchResults {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    border-radius: 0.375rem;
    margin-top: 2px;
}

#searchResults .list-group-item {
    cursor: pointer;
    border: none;
    border-bottom: 1px solid #f0f0f0;
}

#searchResults .list-group-item:hover {
    background-color: #f8f9fa;
}

#searchResults .list-group-item:last-child {
    border-bottom: none;
}

.search-highlight {
    background-color: #fff3cd;
    font-weight: 600;
}
</style>

{{-- JavaScript untuk Search Autocomplete --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBarang');
    const searchResults = document.getElementById('searchResults');
    const barangIdInput = document.getElementById('barang_id');
    
    // Data barang dari server
    const daftarBarang = @json($daftarBarang);
    
    let debounceTimer;
    
    // Event listener untuk input search
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim().toLowerCase();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            barangIdInput.value = '';
            return;
        }
        
        debounceTimer = setTimeout(() => {
            searchBarang(query);
        }, 300);
    });
    
    // Function untuk search barang
    function searchBarang(query) {
        const filtered = daftarBarang.filter(item => {
            const nama = item.nama_barang.toLowerCase();
            const kode = item.kode_barang.toLowerCase();
            return nama.includes(query) || kode.includes(query);
        });
        
        displayResults(filtered, query);
    }
    
    // Function untuk display hasil search
    function displayResults(items, query) {
        if (items.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item text-muted">Tidak ada barang ditemukan</div>';
            searchResults.style.display = 'block';
            return;
        }
        
        let html = '';
        items.slice(0, 10).forEach(item => { // Batasi 10 hasil
            const namaHighlight = highlightText(item.nama_barang, query);
            const kodeHighlight = highlightText(item.kode_barang, query);
            
            html += `
                <a href="#" class="list-group-item list-group-item-action" data-id="${item.id}" data-text="${item.kode_barang} - ${item.nama_barang}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${namaHighlight}</strong>
                            <br>
                            <small class="text-muted">${kodeHighlight}</small>
                        </div>
                        <span class="badge bg-info">${item.stok} ${item.satuan_terkecil}</span>
                    </div>
                </a>
            `;
        });
        
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
        
        // Event listener untuk klik hasil
        searchResults.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                selectBarang(this.dataset.id, this.dataset.text);
            });
        });
    }
    
    // Function untuk highlight text
    function highlightText(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }
    
    // Function untuk select barang
    function selectBarang(id, text) {
        barangIdInput.value = id;
        searchInput.value = text;
        searchResults.style.display = 'none';
    }
    
    // Close dropdown ketika klik di luar
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Clear search saat focus jika sudah ada nilai
    searchInput.addEventListener('focus', function() {
        if (this.value && barangIdInput.value) {
            // Tampilkan hasil untuk barang yang dipilih
            const query = this.value.toLowerCase();
            searchBarang(query);
        }
    });
});
</script>
@endsection