@extends('layouts.app')

@section('title', 'Cetak Barcode - ' . $pembelian->nomor_pembelian)

@push('styles')
<style>
    /* ========================================
       RESET & BASE STYLES
       ======================================== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ========================================
       BARCODE PAGE CONTAINER - FLEX GRID
       ======================================== */
    .barcode-page {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: flex-start;
        justify-content: flex-start;
        margin: 0;
        padding: 0;
        gap: 3mm; /* NO GAP antara label */
        page-break-after: always;
    }

    /* ========================================
       BARCODE ITEM - 33x15mm PRECISE
       ======================================== */
    .barcode-item {
        /* Dimensi Statis */
        width: 33mm !important;
        height: 15mm !important;
        min-width: 33mm;
        max-width: 33mm;
        min-height: 15mm;
        max-height: 15mm;
        
        /* Box Model */
        box-sizing: border-box;
        overflow: hidden;
        
        /* Border (Preview Only) */
        border: 0.5mm solid #333;
        
        /* Internal Layout */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        
        /* Spacing */
        padding: 0.5mm 1mm;
        margin: 0;
        
        /* Positioning */
        position: relative;
        flex-shrink: 0;
        
        /* Background */
        background: white;
    }

    /* ========================================
       HEADER ROW: NAMA + HARGA (SATU BARIS)
       ======================================== */
    .barcode-header {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
        height: 3.5mm;
        margin-bottom: 0.3mm;
        gap: 1mm;
    }

    .item-name {
        font-family: Arial, sans-serif;
        font-size: 6pt;
        font-weight: bold;
        line-height: 1.1;
        color: #000;
        
        /* Text Handling */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        
        /* Sizing */
        flex: 1;
        max-width: 18mm;
    }

    .item-price {
        font-family: Arial, sans-serif;
        font-size: 6.5pt;
        font-weight: bold;
        color: #000;
        white-space: nowrap;
        flex-shrink: 0;
        text-align: right;
    }

    /* ========================================
       BARCODE SVG SECTION
       ======================================== */
    .barcode-svg-container {
        width: 100%;
        height: 9mm; /* Increased from 8mm */
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 0;
    }

    .barcode-item svg {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 31mm !important;
        max-height: 9mm !important;
    }

    /* ========================================
       BARCODE NUMBER (HUMAN READABLE)
       ======================================== */
    .barcode-number {
        font-family: 'Courier New', monospace;
        font-size: 5pt;
        font-weight: bold;
        text-align: center;
        color: #000;
        letter-spacing: 0.3px;
        width: 100%;
        margin-top: 0.2mm;
        line-height: 1;
    }

    /* ========================================
       SIZE VARIANTS (MULTI-COLUMN)
       ======================================== */
    /* 1 COLUMN - 1 Label per Row */
    .barcode-page.size-1line {
        width: 33mm;
    }

    /* 2 COLUMNS - 2 Labels per Row */
    .barcode-page.size-2line {
        width: 66mm; /* 33mm x 2 */
    }

    /* 3 COLUMNS - 3 Labels per Row */
    .barcode-page.size-3line {
        width: 99mm; /* 33mm x 3 */
    }

    /* A4 GRID - Different Size */
    .barcode-item.size-a4 {
        width: 60mm !important;
        height: 25mm !important;
        min-width: 60mm;
        max-width: 60mm;
        min-height: 25mm;
        max-height: 25mm;
        padding: 2mm;
    }

    .barcode-page.size-a4 {
        width: auto;
        flex-wrap: wrap;
        gap: 2mm;
    }

    .size-a4 .barcode-header {
        height: 6mm;
        margin-bottom: 1mm;
    }

    .size-a4 .item-name {
        font-size: 7pt;
        max-width: 35mm;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .size-a4 .item-price {
        font-size: 9pt;
    }

    .size-a4 .barcode-svg-container {
        height: 12mm;
    }

    .size-a4 svg {
        max-width: 56mm;
        max-height: 12mm;
    }

    .size-a4 .barcode-number {
        font-size: 7pt;
    }

    /* ========================================
       PRINT STYLES
       ======================================== */
    @media print {
        @page {
            size: auto;
            margin: 0;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        /* ✅ HIDE NAVBAR, SIDEBAR, HEADERS */
        .container-fluid > .row > .col-lg-4,
        .container-fluid > div:first-child,
        .card-header,
        nav,
        .navbar,
        header,
        footer,
        .no-print {
            display: none !important;
        }

        /* ✅ MAKE BARCODE CONTAINER FULL SCREEN */
        .container-fluid {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .row {
            margin: 0 !important;
        }

        .col-lg-8 {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }

        .card-body {
            padding: 0 !important;
        }

        #barcodeContainer {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .barcode-page {
            page-break-after: always;
            page-break-inside: avoid;
            display: flex !important;
        }

        .barcode-item {
            border: none !important;
            page-break-inside: avoid;
            display: flex !important;
            flex-direction: column !important;
        }

        .barcode-header {
            display: flex !important;
        }

        .item-name,
        .item-price,
        .barcode-number {
            display: block !important;
            visibility: visible !important;
        }

        .barcode-svg-container {
            display: flex !important;
            visibility: visible !important;
        }

        .barcode-item svg {
            display: block !important;
            visibility: visible !important;
        }

        /* Force print colors */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
    }

    /* ========================================
       UI ELEMENTS (NO PRINT)
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
            <h2 class="h3 mb-1 text-gray-800">Cetak Barcode Thermal 33x15mm</h2>
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
                            <span class="size-badge active" data-size="1line" onclick="setLabelSize('1line')">
                                📏 1 Kolom (33x15mm)
                            </span>
                            <span class="size-badge" data-size="2line" onclick="setLabelSize('2line')">
                                📏 2 Kolom (66mm)
                            </span>
                            <span class="size-badge" data-size="3line" onclick="setLabelSize('3line')">
                                📏 3 Kolom (99mm)
                            </span>
                            <span class="size-badge" data-size="a4" onclick="setLabelSize('a4')">
                                📄 A4 Grid (60x25mm)
                            </span>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 12px;" id="sizeInfo">
                            <strong>✓ 1 Kolom:</strong> 1 label per baris (33x15mm)<br>
                            <strong>Note:</strong> Cocok untuk printer thermal roll
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
                            1 Kolom (33x15mm)
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
                            <small class="text-muted">Format: Nama + Harga | Barcode | Kode</small>
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
let currentLabelSize = '1line';

function setLabelSize(size) {
    currentLabelSize = size;
    
    document.querySelectorAll('.size-badge').forEach(badge => {
        badge.classList.remove('active');
    });
    document.querySelector(`.size-badge[data-size="${size}"]`).classList.add('active');
    
    const sizeLabels = {
        '1line': '1 Kolom (33x15mm)',
        '2line': '2 Kolom (66mm)',
        '3line': '3 Kolom (99mm)',
        'a4': '60 x 25mm (A4)'
    };
    document.getElementById('labelSizeDisplay').textContent = sizeLabels[size];
    
    const sizeInfos = {
        '1line': '<strong>✓ 1 Kolom:</strong> 1 label per baris (33x15mm)<br><strong>Note:</strong> Cocok untuk printer thermal roll',
        '2line': '<strong>✓ 2 Kolom:</strong> 2 label per baris (66mm total)<br><strong>Note:</strong> Cocok untuk gap/die-cut label 2 kolom',
        '3line': '<strong>✓ 3 Kolom:</strong> 3 label per baris (99mm total)<br><strong>Note:</strong> Maksimal untuk kertas thermal 110mm',
        'a4': '<strong>✓ A4 Grid:</strong> Standard 60x25mm<br><strong>Note:</strong> Untuk printer laser biasa'
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

    // Tentukan berapa label per baris
    const labelsPerRow = size === '1line' ? 1 : (size === '2line' ? 2 : (size === '3line' ? 3 : 3));
    
    let currentRow = null;
    
    for (let i = 0; i < barcodes.length; i++) {
        // Buat baris baru setiap labelsPerRow
        if (i % labelsPerRow === 0) {
            currentRow = document.createElement('div');
            currentRow.className = `barcode-page size-${size}`;
            container.appendChild(currentRow);
        }
        
        const item = barcodes[i];
        
        // Container Label
        const labelDiv = document.createElement('div');
        labelDiv.className = `barcode-item ${size === 'a4' ? 'size-a4' : ''}`;
        
        // Header: Nama + Harga (Satu Baris)
        const headerDiv = document.createElement('div');
        headerDiv.className = 'barcode-header';
        
        const nameDiv = document.createElement('div');
        nameDiv.className = 'item-name';
        let displayName = item.nama_barang || 'Nama Barang';
        displayName = truncateText(displayName, size === 'a4' ? 30 : 15);
        nameDiv.textContent = displayName;
        
        const priceDiv = document.createElement('div');
        priceDiv.className = 'item-price';
        priceDiv.textContent = formatRupiah(item.harga || 0);
        
        headerDiv.appendChild(nameDiv);
        headerDiv.appendChild(priceDiv);
        
        // SVG Barcode Container
        const svgContainer = document.createElement('div');
        svgContainer.className = 'barcode-svg-container';
        
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.id = 'barcode-' + i;
        svgContainer.appendChild(svg);
        
        // Nomor Barcode (Human Readable)
        const numberDiv = document.createElement('div');
        numberDiv.className = 'barcode-number';
        numberDiv.textContent = item.barcode || item.kode;
        
        // Susun Elemen
        labelDiv.appendChild(headerDiv);
        labelDiv.appendChild(svgContainer);
        labelDiv.appendChild(numberDiv);
        currentRow.appendChild(labelDiv);
        
        // Generate Barcode dengan JsBarcode
        try {
            // Konfigurasi berdasarkan ukuran
            let barcodeConfig = {
                format: 'CODE128',
                displayValue: false, // Kita render manual
                margin: 0,
                background: '#ffffff',
                lineColor: '#000000',
            };
            
            if (size === 'a4') {
                barcodeConfig.width = 2;
                barcodeConfig.height = 50;
            } else if (size === '3line') {
                barcodeConfig.width = 1.5; // Increased from 1.0
                barcodeConfig.height = 35; // Increased from 25
            } else if (size === '2line') {
                barcodeConfig.width = 1.8; // Increased from 1.2
                barcodeConfig.height = 35; // Increased from 25
            } else {
                barcodeConfig.width = 2; // Increased from 1.5
                barcodeConfig.height = 35; // Increased from 25
            }
            
            JsBarcode('#barcode-' + i, item.barcode || item.kode, barcodeConfig);
            
            // Force render SVG
            svg.setAttribute('style', 'display:block;visibility:visible;');
        } catch (e) {
            console.error('Error generating barcode:', e);
            svg.innerHTML = '<text x="50%" y="50%" text-anchor="middle" font-size="8" fill="red">ERROR</text>';
        }
    }
    
    // ✅ FORCE RENDER ALL SVG AFTER GENERATION
    setTimeout(() => {
        document.querySelectorAll('.barcode-item svg').forEach(svg => {
            svg.style.display = 'block';
            svg.style.visibility = 'visible';
            svg.style.opacity = '1';
            
            // Force repaint
            svg.offsetHeight;
        });
        console.log('✅ SVG rendering forced');
    }, 100);
}
</script>
@endpush