@extends('layouts.app')

@section('title', 'Cetak Barcode - ' . $pembelian->nomor_pembelian)

@push('styles')
<style>
    /* ========================================
       GENERAL STYLES
       ======================================== */
    .barcode-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .barcode-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }
    
    .barcode-preview {
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        padding: 20px;
        text-align: center;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ========================================
       BARCODE ITEM - UNIVERSAL STYLE
       ======================================== */
    .barcode-item {
        border: 2px solid #000;
        padding: 3mm;
        margin: 2mm;
        display: inline-block;
        text-align: center;
        background: white;
        page-break-inside: avoid;
        vertical-align: top;
        box-sizing: border-box;
    }
    
    .barcode-item svg {
        display: block;
        margin: 0 auto 1mm;
    }
    
    .barcode-item .item-name {
        font-family: Arial, sans-serif;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1mm;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .barcode-item .item-price {
        font-family: Arial, sans-serif;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1mm;
        color: #000;
    }
    
    .barcode-item .barcode-number {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-align: center;
        letter-spacing: 0.5px;
        margin-top: 1mm;
    }

    /* Size: 2 Line (33x15mm) - MINIMAL */
    .barcode-item.size-2line {
        width: 33mm;
        height: 15mm;
        padding: 1mm;
    }
    
    .barcode-item.size-2line svg {
        width: 30mm;
        height: 7mm;
    }
    
    .barcode-item.size-2line .item-name {
        font-size: 5pt;
        max-height: 2.5mm;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
    }
    
    .barcode-item.size-2line .item-price {
        font-size: 6pt;
    }
    
    .barcode-item.size-2line .barcode-number {
        font-size: 5pt;
    }

    /* Size: 1 Line (50x30mm) - COMFORTABLE */
    .barcode-item.size-1line {
        width: 50mm;
        height: 30mm;
        padding: 2mm;
    }
    
    .barcode-item.size-1line svg {
        width: 45mm;
        height: 14mm;
    }
    
    .barcode-item.size-1line .item-name {
        font-size: 8pt;
        max-height: 8mm;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .barcode-item.size-1line .item-price {
        font-size: 10pt;
    }
    
    .barcode-item.size-1line .barcode-number {
        font-size: 8pt;
    }

    /* Size: A4 Grid (60x25mm per label) - STANDARD */
    .barcode-item.size-a4 {
        width: 60mm;
        height: 25mm;
        padding: 2mm;
    }
    
    .barcode-item.size-a4 svg {
        width: 55mm;
        height: 12mm;
    }
    
    .barcode-item.size-a4 .item-name {
        font-size: 7pt;
        max-height: 6mm;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .barcode-item.size-a4 .item-price {
        font-size: 9pt;
    }
    
    .barcode-item.size-a4 .barcode-number {
        font-size: 7pt;
    }

    /* ========================================
       PRINT STYLES
       ======================================== */
    @media print {
        /* Hide non-print elements */
        .no-print {
            display: none !important;
        }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Page settings for A4 */
        @page {
            size: A4 portrait;
            margin: 5mm;
        }
        
        /* Container for print */
        #barcodeContainer {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }
        
        /* Barcode items */
        .barcode-item {
            border: 2px solid #000 !important;
            margin: 1mm !important;
            page-break-inside: avoid;
        }
        
        /* Make sure all elements are visible */
        .barcode-item svg,
        .barcode-item .item-name,
        .barcode-item .item-price,
        .barcode-item .barcode-number {
            display: block !important;
            visibility: visible !important;
        }
    }
    
    /* Size badges */
    .size-badge {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
        width: 100%;
        text-align: center;
        border: 2px solid;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .size-badge.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .size-badge:not(.active) {
        background: white;
        color: #6c757d;
        border-color: #dee2e6;
    }
    
    .size-badge:not(.active):hover {
        border-color: #007bff;
        color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Cetak Barcode</h2>
            <p class="text-muted mb-0">{{ $pembelian->nomor_pembelian }} - {{ $pembelian->supplier->nama_supplier }}</p>
        </div>
        <div>
            <a href="{{ route('pembelian.show', $pembelian->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary" id="btnPrint" disabled>
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-lg-4 no-print">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-gear-fill me-2"></i>Opsi Cetak Barcode
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Ukuran Label --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">
                            <i class="bi bi-rulers me-2"></i>Ukuran Label:
                        </label>
                        <div class="d-grid gap-2 mb-3">
                            <span class="size-badge active" data-size="2line" onclick="setLabelSize('2line')">
                                📏 33 x 15mm (Thermal 2 Line)
                            </span>
                            <span class="size-badge" data-size="1line" onclick="setLabelSize('1line')">
                                📏 50 x 30mm (Thermal 1 Line)
                            </span>
                            <span class="size-badge" data-size="a4" onclick="setLabelSize('a4')">
                                📄 60 x 25mm (A4 Grid)
                            </span>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 12px;" id="sizeInfo">
                            <strong>✓ 33x15mm:</strong> Kompak - Nama (1 baris) + Harga + Barcode<br>
                            <strong>Note:</strong> Untuk label thermal kecil
                        </div>
                    </div>

                    <hr>

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-per-item" data-bs-toggle="tab" data-bs-target="#per-item" type="button">
                                Per Item
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-semua" data-bs-toggle="tab" data-bs-target="#semua-item" type="button">
                                Semua Item
                            </button>
                        </li>
                    </ul>

                    {{-- Tab Content --}}
                    <div class="tab-content">
                        {{-- Per Item --}}
                        <div class="tab-pane fade show active" id="per-item" role="tabpanel">
                            <p class="text-sm text-muted mb-3">Pilih item dan tentukan jumlah:</p>
                            
                            @foreach($pembelian->detailPembelian as $detail)
                                <div class="barcode-card">
                                    <h6 class="mb-2">{{ $detail->barang->nama_barang ?? 'Barang Dihapus' }}</h6>
                                    <p class="text-xs text-muted mb-2">
                                        Kode: <strong>{{ $detail->barang->kode_barang ?? '-' }}</strong><br>
                                        Barcode: <strong>{{ $detail->barang->barcode ?? '-' }}</strong><br>
                                        Harga: <strong>Rp {{ number_format($detail->barang->harga_jual ?? 0, 0, ',', '.') }}</strong><br>
                                        Qty: {{ $detail->jumlah }} {{ $detail->satuan }}
                                    </p>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Qty</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="qty-{{ $detail->id }}" 
                                               min="1" 
                                               max="1000" 
                                               value="{{ $detail->jumlah }}">
                                        <button class="btn btn-primary" 
                                                type="button" 
                                                onclick="generateSingle({{ $detail->id }})">
                                            <i class="bi bi-upc-scan"></i> Generate
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Semua Item --}}
                        <div class="tab-pane fade" id="semua-item" role="tabpanel">
                            <p class="text-sm text-muted mb-3">Cetak semua item sekaligus:</p>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mode:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_cetak" id="mode_qty" value="qty" checked>
                                    <label class="form-check-label" for="mode_qty">
                                        Sesuai Qty Pembelian
                                        <small class="d-block text-muted">Cetak sesuai qty beli</small>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="mode_cetak" id="mode_custom" value="custom">
                                    <label class="form-check-label" for="mode_custom">
                                        Jumlah Custom
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3" id="custom_qty_input" style="display: none;">
                                <label for="jumlah_custom" class="form-label">Jumlah per Item:</label>
                                <input type="number" 
                                       class="form-control form-control-sm" 
                                       id="jumlah_custom" 
                                       min="1" 
                                       max="100" 
                                       value="5">
                            </div>

                            <button type="button" class="btn btn-success w-100" onclick="generateAll()">
                                <i class="bi bi-upc-scan me-1"></i> Generate Semua
                            </button>

                            <hr>
                            <p class="text-xs text-info mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Total Item: <strong>{{ $pembelian->detailPembelian->count() }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview Area --}}
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header no-print bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-eye me-2"></i>Preview Barcode
                        </h6>
                        <span class="badge bg-secondary" id="labelSizeDisplay">
                            33 x 15mm
                        </span>
                    </div>
                </div>
                <div class="card-body" id="barcodeContainer">
                    <div class="barcode-preview">
                        <div class="text-center">
                            <i class="bi bi-upc-scan text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mb-0 mt-3">
                                <strong>Pilih ukuran</strong>, lalu klik <strong>"Generate"</strong>
                            </p>
                            <small class="text-muted">Format: Nama + Harga + Barcode + Angka</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
let currentLabelSize = '2line';

function setLabelSize(size) {
    currentLabelSize = size;
    
    document.querySelectorAll('.size-badge').forEach(badge => {
        badge.classList.remove('active');
    });
    document.querySelector(`.size-badge[data-size="${size}"]`).classList.add('active');
    
    const sizeLabels = {
        '2line': '33 x 15mm',
        '1line': '50 x 30mm',
        'a4': '60 x 25mm (A4)'
    };
    document.getElementById('labelSizeDisplay').textContent = sizeLabels[size];
    
    const sizeInfos = {
        '2line': '<strong>✓ 33x15mm:</strong> Kompak - Nama (1 baris) + Harga + Barcode<br><strong>Note:</strong> Untuk label thermal kecil',
        '1line': '<strong>✓ 50x30mm:</strong> Nyaman - Nama (2 baris) + Harga + Barcode<br><strong>Note:</strong> Untuk gudang/warehouse',
        'a4': '<strong>✓ 60x25mm:</strong> Standard - Nama (2 baris) + Harga + Barcode<br><strong>Note:</strong> Bisa print di printer biasa. 3 kolom per baris.'
    };
    document.getElementById('sizeInfo').innerHTML = sizeInfos[size];
}

document.querySelectorAll('input[name="mode_cetak"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('custom_qty_input').style.display = 
            this.value === 'custom' ? 'block' : 'none';
    });
});

function generateSingle(detailId) {
    const qty = document.getElementById('qty-' + detailId).value;
    
    if (!qty || qty < 1) {
        alert('Jumlah minimal 1');
        return;
    }

    fetch(`/pembelian/barcode/generate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            detail_pembelian_id: detailId,
            jumlah_cetak: parseInt(qty)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderBarcodes(data.barcodes);
            document.getElementById('btnPrint').disabled = false;
        } else {
            alert('Gagal generate barcode');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function generateAll() {
    const mode = document.querySelector('input[name="mode_cetak"]:checked').value;
    const jumlahCustom = document.getElementById('jumlah_custom').value;

    if (mode === 'custom' && (!jumlahCustom || jumlahCustom < 1)) {
        alert('Jumlah custom minimal 1');
        return;
    }

    fetch(`/pembelian/{{ $pembelian->id }}/barcode/generate-all`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            mode: mode,
            jumlah_custom: mode === 'custom' ? parseInt(jumlahCustom) : null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderBarcodes(data.barcodes);
            document.getElementById('btnPrint').disabled = false;
            alert(`✓ Berhasil generate ${data.total_items} barcode!`);
        } else {
            alert('Gagal generate barcode');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function formatRupiah(number) {
    return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - 3) + '...';
}

function renderBarcodes(barcodes) {
    const container = document.getElementById('barcodeContainer');
    const size = currentLabelSize;
    
    container.innerHTML = '';

    barcodes.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = `barcode-item size-${size}`;
        
        // Nama Barang
        const nameDiv = document.createElement('div');
        nameDiv.className = 'item-name';
        let displayName = item.nama_barang || 'Nama Barang';
        
        // Truncate berdasarkan ukuran
        if (size === '2line') {
            displayName = truncateText(displayName, 20);
        } else if (size === '1line') {
            displayName = truncateText(displayName, 35);
        } else {
            displayName = truncateText(displayName, 30);
        }
        nameDiv.textContent = displayName;
        
        // Harga
        const priceDiv = document.createElement('div');
        priceDiv.className = 'item-price';
        priceDiv.textContent = formatRupiah(item.harga || 0);
        
        // SVG Barcode
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.id = 'barcode-' + index;
        
        // Nomor Barcode
        const numberDiv = document.createElement('div');
        numberDiv.className = 'barcode-number';
        numberDiv.textContent = item.barcode || item.kode;
        
        // Susun elemen
        div.appendChild(nameDiv);
        div.appendChild(priceDiv);
        div.appendChild(svg);
        div.appendChild(numberDiv);
        container.appendChild(div);
        
        // Generate barcode
        try {
            let barcodeOptions = {
                format: 'CODE128',
                displayValue: false,
                margin: 0,
                background: '#ffffff',
                lineColor: '#000000'
            };
            
            if (size === '2line') {
                barcodeOptions.width = 1.5;
                barcodeOptions.height = 25;
            } else if (size === '1line') {
                barcodeOptions.width = 2;
                barcodeOptions.height = 50;
            } else {
                barcodeOptions.width = 2;
                barcodeOptions.height = 45;
            }
            
            JsBarcode('#barcode-' + index, item.barcode || item.kode, barcodeOptions);
        } catch (e) {
            console.error('Error generating barcode:', e);
            svg.innerHTML = '<text x="50%" y="50%" text-anchor="middle" font-size="12" fill="red">ERROR</text>';
        }
    });
}
</script>
@endpush