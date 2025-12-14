@extends('layouts.app')

@section('title', 'Stok Opname - Scan Barcode')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Stok Opname - Scan Barcode</h1>
            <p class="text-muted mb-0">Sesi: {{ $sesiAktif->keterangan }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFinalize">
                <i class="bi bi-check-circle me-1"></i> Selesaikan SO
            </button>
            <a href="{{ route('stokopname.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Panel Scan Barcode --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="bi bi-upc-scan me-2"></i>Input Barcode
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    {{-- Tab Mode Input --}}
                    <ul class="nav nav-tabs mb-3" id="inputModeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" 
                                    data-bs-target="#manual" type="button" role="tab">
                                <i class="bi bi-keyboard me-1"></i> Manual / Scanner
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="camera-tab" data-bs-toggle="tab" 
                                    data-bs-target="#camera" type="button" role="tab">
                                <i class="bi bi-camera me-1"></i> Scan dengan Kamera
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="search-tab" data-bs-toggle="tab" 
                                    data-bs-target="#search" type="button" role="tab">
                                <i class="bi bi-search me-1"></i> Cari Nama Barang
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="inputModeTabContent">
                        {{-- Tab Manual/Scanner --}}
                        <div class="tab-pane fade show active" id="manual" role="tabpanel">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-upc-scan"></i>
                                </span>
                                <input type="text" 
                                       id="barcodeInput" 
                                       class="form-control form-control-lg" 
                                       placeholder="Scan atau ketik barcode disini..." 
                                       autofocus>
                                <button class="btn btn-primary" type="button" id="btnScan">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                            <small class="text-muted">Tekan Enter atau klik tombol Cari setelah scan barcode</small>
                        </div>

                        {{-- Tab Kamera - IMPROVED VERSION --}}
                        <div class="tab-pane fade" id="camera" role="tabpanel">
                            <div class="text-center">
                                {{-- Camera Preview Container --}}
                                <div id="cameraContainer" class="mb-3" style="position: relative; max-width: 100%; margin: 0 auto;">
                                    <video id="cameraPreview" 
                                           playsinline 
                                           autoplay 
                                           style="width: 100%; max-width: 640px; border: 2px solid #ddd; border-radius: 8px; display: none;">
                                    </video>
                                    
                                    <canvas id="canvasElement" 
                                            style="display: none; width: 100%; max-width: 640px;">
                                    </canvas>
                                    
                                    <div id="cameraPlaceholder" class="p-5 bg-light border rounded">
                                        <i class="bi bi-camera-video" style="font-size: 4rem; color: #ccc;"></i>
                                        <p class="mt-3 text-muted">Klik tombol "Aktifkan Kamera" untuk memulai scan</p>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle"></i> Pastikan browser memiliki izin akses kamera
                                        </small>
                                    </div>
                                </div>
                                
                                {{-- Camera Controls --}}
                                <div class="btn-group mb-3" role="group">
                                    <button type="button" id="btnStartCamera" class="btn btn-success btn-lg">
                                        <i class="bi bi-camera-video me-2"></i>Aktifkan Kamera
                                    </button>
                                    <button type="button" id="btnStopCamera" class="btn btn-danger btn-lg" style="display: none;">
                                        <i class="bi bi-stop-circle me-2"></i>Matikan Kamera
                                    </button>
                                </div>
                                
                                {{-- Camera Status --}}
                                <div id="cameraStatus" class="mt-3"></div>
                                
                                {{-- Debug Info (dapat dihapus di production) --}}
                                <div id="debugInfo" class="mt-2 text-start" style="display: none;">
                                    <small class="text-muted">
                                        <strong>Debug Info:</strong><br>
                                        <span id="debugText"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- Tab Pencarian --}}
                        <div class="tab-pane fade" id="search" role="tabpanel">
                            <div class="mb-3">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           id="searchInput" 
                                           class="form-control form-control-lg" 
                                           placeholder="Cari nama barang atau kode barang...">
                                </div>
                            </div>
                            
                            <div id="searchResults" class="list-group" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-search"></i> Ketik untuk mencari barang...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0">
                        <strong>Total Item:</strong> <span id="totalItems">{{ $itemsScanned->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Selisih --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selisih Lebih</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="selisihPlus">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-up-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Selisih Kurang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="selisihMinus">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-down-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Item Expired Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="itemExpired">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Hasil Scan --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Barang yang Di-scan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tableScanned">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="20%">Barang</th>
                            <th width="10%" class="text-center">Lokasi Rak</th>
                            <th width="8%" class="text-center">Stok Sistem</th>
                            <th width="12%">Stok Fisik</th>
                            <th width="8%" class="text-center">Selisih</th>
                            <th width="12%">Expired Date</th>
                            <th width="15%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyScanned">
                        @forelse($itemsScanned as $index => $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->barang->nama_barang }}</strong>
                                <small class="d-block text-muted">{{ $item->barang->kode_barang }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item->barang->lokasi_rak ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $item->stok_sistem }}</strong>
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control form-control-sm stok-fisik-input" 
                                       value="{{ $item->stok_fisik }}" 
                                       min="0"
                                       data-id="{{ $item->id }}">
                            </td>
                            <td class="text-center selisih-cell">
                                @if($item->selisih > 0)
                                    <span class="badge bg-success">+{{ $item->selisih }}</span>
                                @elseif($item->selisih < 0)
                                    <span class="badge bg-danger">{{ $item->selisih }}</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                <input type="date" 
                                       class="form-control form-control-sm expired-date-input" 
                                       value="{{ $item->expired_date?->format('Y-m-d') }}"
                                       data-id="{{ $item->id }}">
                            </td>
                            <td class="status-cell">
                                @php
                                    $isExpiringSoon = $item->expired_date && $item->expired_date->lte(now()->addDays(30));
                                @endphp
                                @if($isExpiringSoon)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-triangle"></i> Segera Expired
                                    </span>
                                @else
                                    <span class="badge bg-success">Normal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $item->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="9" class="text-center text-muted">Belum ada barang yang di-scan. Silakan scan barcode untuk memulai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Finalize SO --}}
<div class="modal fade" id="modalFinalize" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stokopname.finalize', $sesiAktif->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Selesaikan Stok Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menyelesaikan sesi Stok Opname ini. Stok sistem akan diupdate sesuai dengan stok fisik yang telah dicatat.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Keterangan Akhir (Opsional)</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Catatan tambahan...">{{ $sesiAktif->keterangan }}</textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Proses ini tidak dapat dibatalkan!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Ya, Selesaikan SO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ZXing Library untuk Barcode Scanner (Better mobile support) -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcodeInput');
    const btnScan = document.getElementById('btnScan');
    const tbody = document.getElementById('tbodyScanned');
    const sesiId = {{ $sesiAktif->id }};
    const csrfToken = '{{ csrf_token() }}';
    
    let cameraStream = null;
    let isScanning = false;
    let codeReader = null;

    // ========================
    // 1. MANUAL INPUT / SCANNER
    // ========================
    barcodeInput.focus();
    
    setInterval(function() {
        if (document.activeElement.tagName !== 'INPUT' || 
            (document.activeElement.id !== 'barcodeInput' && 
             !document.activeElement.classList.contains('stok-fisik-input') &&
             !document.activeElement.classList.contains('expired-date-input') &&
             document.activeElement.id !== 'searchInput')) {
            if (document.getElementById('manual').classList.contains('show')) {
                barcodeInput.focus();
            }
        }
    }, 2000);

    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            scanBarcode();
        }
    });

    btnScan.addEventListener('click', scanBarcode);

    // ========================
    // 2. IMPROVED CAMERA BARCODE SCANNER
    // ========================
    const btnStartCamera = document.getElementById('btnStartCamera');
    const btnStopCamera = document.getElementById('btnStopCamera');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const cameraStatus = document.getElementById('cameraStatus');
    const debugInfo = document.getElementById('debugInfo');
    const debugText = document.getElementById('debugText');

    btnStartCamera.addEventListener('click', startCameraImproved);
    btnStopCamera.addEventListener('click', stopCameraImproved);

    // ✅ IMPROVED: Better camera initialization dengan error handling
    async function startCameraImproved() {
        try {
            // Update UI
            cameraPlaceholder.style.display = 'none';
            btnStartCamera.style.display = 'none';
            btnStopCamera.style.display = 'inline-block';
            cameraStatus.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memulai kamera...';
            
            // Debug mode
            debugInfo.style.display = 'block';
            debugText.innerHTML = 'Checking camera permission...';

            // ✅ Check if getUserMedia is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Browser tidak mendukung akses kamera. Gunakan browser modern seperti Chrome atau Safari.');
            }

            // ✅ Request camera permission dengan constraints yang lebih spesifik
            debugText.innerHTML = 'Requesting camera access...';
            
            const constraints = {
                video: {
                    facingMode: { ideal: 'environment' }, // Prefer back camera
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
            
            debugText.innerHTML = 'Camera stream obtained. Video tracks: ' + cameraStream.getVideoTracks().length;

            // ✅ Set stream ke video element
            cameraPreview.srcObject = cameraStream;
            cameraPreview.style.display = 'block';
            
            // ✅ Wait for video to be ready
            await new Promise((resolve, reject) => {
                cameraPreview.onloadedmetadata = () => {
                    debugText.innerHTML += '<br>Video metadata loaded';
                    resolve();
                };
                cameraPreview.onerror = (error) => {
                    debugText.innerHTML += '<br>Video error: ' + error;
                    reject(error);
                };
                
                // Timeout fallback
                setTimeout(() => resolve(), 2000);
            });

            await cameraPreview.play();
            debugText.innerHTML += '<br>Video playing';

            // ✅ Initialize ZXing Code Reader
            codeReader = new ZXing.BrowserMultiFormatReader();
            
            debugText.innerHTML += '<br>ZXing initialized';
            
            cameraStatus.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-camera-video-fill me-2"></i>Kamera aktif! Arahkan ke barcode...
                </div>
            `;
            
            isScanning = true;
            
            // ✅ Start continuous scanning
            scanFromVideoDevice();

        } catch (error) {
            console.error('Camera Error:', error);
            
            let errorMessage = 'Gagal mengakses kamera: ';
            
            if (error.name === 'NotAllowedError') {
                errorMessage += 'Izin kamera ditolak. Silakan berikan izin akses kamera di pengaturan browser.';
            } else if (error.name === 'NotFoundError') {
                errorMessage += 'Kamera tidak ditemukan. Pastikan device memiliki kamera.';
            } else if (error.name === 'NotReadableError') {
                errorMessage += 'Kamera sedang digunakan oleh aplikasi lain.';
            } else if (error.name === 'OverconstrainedError') {
                errorMessage += 'Kamera tidak mendukung konfigurasi yang diminta.';
            } else {
                errorMessage += error.message;
            }
            
            cameraStatus.innerHTML = `<div class="alert alert-danger">${errorMessage}</div>`;
            debugText.innerHTML += '<br>ERROR: ' + error.name + ' - ' + error.message;
            
            stopCameraImproved();
        }
    }

    // ✅ IMPROVED: Continuous scanning function
    function scanFromVideoDevice() {
        if (!isScanning || !cameraPreview) return;

        codeReader.decodeFromVideoElement(cameraPreview, (result, error) => {
            if (result && isScanning) {
                const code = result.text;
                
                // Play beep
                playBeep();
                
                // Show detected code
                cameraStatus.innerHTML = `
                    <div class="alert alert-info">
                        ✓ Terdeteksi: <strong>${code}</strong>
                    </div>
                `;
                
                // Process barcode
                processBarcode(code);
                
                // Pause scanning temporarily
                isScanning = false;
                setTimeout(() => {
                    if (cameraStream && cameraStream.active) {
                        isScanning = true;
                        cameraStatus.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-camera-video-fill me-2"></i>Kamera aktif! Arahkan ke barcode...
                            </div>
                        `;
                    }
                }, 2000);
            }
            
            if (error && error instanceof ZXing.NotFoundException) {
                // No barcode found - this is normal, keep scanning
                debugText.innerHTML = 'Scanning... (no barcode detected yet)';
            }
            
            if (error && !(error instanceof ZXing.NotFoundException)) {
                console.error('Decode error:', error);
            }
        });
    }

    // ✅ IMPROVED: Stop camera function
    function stopCameraImproved() {
        isScanning = false;
        
        // Stop code reader
        if (codeReader) {
            try {
                codeReader.reset();
            } catch (e) {
                console.error('Error resetting code reader:', e);
            }
            codeReader = null;
        }
        
        // Stop media stream
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => {
                track.stop();
            });
            cameraStream = null;
        }
        
        // Reset video element
        if (cameraPreview) {
            cameraPreview.srcObject = null;
            cameraPreview.style.display = 'none';
        }
        
        // Reset UI
        cameraPlaceholder.style.display = 'block';
        btnStartCamera.style.display = 'inline-block';
        btnStopCamera.style.display = 'none';
        cameraStatus.innerHTML = '';
        debugInfo.style.display = 'none';
    }

    function playBeep() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            console.error('Error playing beep:', e);
        }
    }

    // ✅ Tab change handler - stop camera when switching tabs
    document.querySelectorAll('#inputModeTabs button').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            if (e.target.id !== 'camera-tab') {
                stopCameraImproved();
            }
        });
    });

    // ========================
    // 3. SEARCH BARANG
    // ========================
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-search"></i> Ketik minimal 2 karakter...</div>';
            return;
        }

        searchResults.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Mencari...</div>';

        searchTimeout = setTimeout(() => {
            fetch(`/barang/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="text-center text-muted py-3">Tidak ada barang ditemukan</div>';
                    return;
                }

                searchResults.innerHTML = data.map(barang => `
                    <a href="#" class="list-group-item list-group-item-action search-item" data-barcode="${barang.barcode || barang.kode_barang}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${barang.nama_barang}</h6>
                            <small class="text-muted">Stok: ${barang.stok}</small>
                        </div>
                        <p class="mb-1"><small class="text-muted">${barang.kode_barang}</small></p>
                    </a>
                `).join('');

                // Add click handlers
                document.querySelectorAll('.search-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const barcode = this.getAttribute('data-barcode');
                        processBarcode(barcode);
                        searchInput.value = '';
                        searchResults.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-search"></i> Ketik untuk mencari barang...</div>';
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                searchResults.innerHTML = '<div class="alert alert-danger">Gagal mencari barang</div>';
            });
        }, 500);
    });

    // ========================
    // SHARED FUNCTIONS
    // ========================
    function scanBarcode() {
        const barcode = barcodeInput.value.trim();
        processBarcode(barcode);
    }

    function processBarcode(barcode) {
        if (!barcode) {
            showToast('warning', 'Silakan masukkan barcode!');
            return;
        }

        btnScan.disabled = true;
        btnScan.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

        fetch('{{ route("stokopname.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                barcode: barcode,
                sesi_id: sesiId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const emptyRow = document.getElementById('emptyRow');
                if (emptyRow) emptyRow.remove();

                addItemRow(data.detail);
                updateRingkasan();
                
                barcodeInput.value = '';
                barcodeInput.focus();
                
                showToast('success', data.message);
            } else {
                showToast('warning', data.message);
                barcodeInput.select();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Terjadi kesalahan saat memproses barcode!');
        })
        .finally(() => {
            btnScan.disabled = false;
            btnScan.innerHTML = '<i class="bi bi-search me-1"></i> Cari';
        });
    }

    function addItemRow(detail) {
        const rowCount = tbody.querySelectorAll('tr:not(#emptyRow)').length + 1;
        const row = document.createElement('tr');
        row.setAttribute('data-id', detail.id);
        
        row.innerHTML = `
            <td class="text-center">${rowCount}</td>
            <td>
                <strong>${detail.barang.nama_barang}</strong>
                <small class="d-block text-muted">${detail.barang.kode_barang}</small>
            </td>
            <td class="text-center">
                <span class="badge bg-info">${detail.barang.lokasi_rak || '-'}</span>
            </td>
            <td class="text-center"><strong>${detail.stok_sistem}</strong></td>
            <td>
                <input type="number" class="form-control form-control-sm stok-fisik-input" 
                       value="${detail.stok_fisik}" min="0" data-id="${detail.id}">
            </td>
            <td class="text-center selisih-cell">
                <span class="badge bg-secondary">0</span>
            </td>
            <td>
                <input type="date" class="form-control form-control-sm expired-date-input" 
                       value="" data-id="${detail.id}">
            </td>
            <td class="status-cell">
                <span class="badge bg-success">Normal</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${detail.id}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
    }

    // Update item
    tbody.addEventListener('change', function(e) {
        if (e.target.classList.contains('stok-fisik-input') || 
            e.target.classList.contains('expired-date-input')) {
            
            const id = e.target.getAttribute('data-id');
            const row = e.target.closest('tr');
            const stokFisik = row.querySelector('.stok-fisik-input').value;
            const expiredDate = row.querySelector('.expired-date-input').value;

            updateItem(id, stokFisik, expiredDate, row);
        }
    });

    function updateItem(id, stokFisik, expiredDate, row) {
        fetch(`/stokopname/item/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                stok_fisik: stokFisik,
                expired_date: expiredDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selisih = data.detail.selisih;
                const selisihCell = row.querySelector('.selisih-cell');
                
                if (selisih > 0) {
                    selisihCell.innerHTML = `<span class="badge bg-success">+${selisih}</span>`;
                } else if (selisih < 0) {
                    selisihCell.innerHTML = `<span class="badge bg-danger">${selisih}</span>`;
                } else {
                    selisihCell.innerHTML = `<span class="badge bg-secondary">0</span>`;
                }

                updateExpiredStatus(row, expiredDate);
                updateRingkasan();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Gagal update data!');
        });
    }

    function updateExpiredStatus(row, expiredDate) {
        const statusCell = row.querySelector('.status-cell');
        
        if (expiredDate) {
            const today = new Date();
            const expDate = new Date(expiredDate);
            const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));
            
            if (diffDays <= 30 && diffDays >= 0) {
                statusCell.innerHTML = `<span class="badge bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i> Segera Expired
                </span>`;
            } else if (diffDays < 0) {
                statusCell.innerHTML = `<span class="badge bg-danger">
                    <i class="bi bi-x-circle"></i> Sudah Expired
                </span>`;
            } else {
                statusCell.innerHTML = `<span class="badge bg-success">Normal</span>`;
            }
        } else {
            statusCell.innerHTML = `<span class="badge bg-success">Normal</span>`;
        }
    }

    // Delete item
    tbody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            const btn = e.target.closest('.btn-delete');
            const id = btn.getAttribute('data-id');
            const row = btn.closest('tr');
            
            if (confirm('Hapus item ini dari daftar?')) {
                deleteItem(id, row);
            }
        }
    });

    function deleteItem(id, row) {
        fetch(`/stokopname/item/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                updateRingkasan();
                showToast('success', 'Item berhasil dihapus!');
                
                updateRowNumbers();
                
                if (tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = `<tr id="emptyRow">
                        <td colspan="9" class="text-center text-muted">Belum ada barang yang di-scan.</td>
                    </tr>`;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Gagal menghapus item!');
        });
    }

    function updateRowNumbers() {
        const rows = tbody.querySelectorAll('tr:not(#emptyRow)');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    function updateRingkasan() {
        const rows = tbody.querySelectorAll('tr:not(#emptyRow)');
        let totalItems = rows.length;
        let selisihPlus = 0;
        let selisihMinus = 0;
        let itemExpired = 0;

        rows.forEach(row => {
            const badgeText = row.querySelector('.selisih-cell span').textContent;
            const selisih = parseInt(badgeText.replace('+', '')) || 0;
            
            if (selisih > 0) selisihPlus += selisih;
            if (selisih < 0) selisihMinus += selisih;
            
            const statusBadge = row.querySelector('.status-cell span');
            if (statusBadge.classList.contains('bg-warning') || statusBadge.classList.contains('bg-danger')) {
                itemExpired++;
            }
        });

        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('selisihPlus').textContent = selisihPlus;
        document.getElementById('selisihMinus').textContent = selisihMinus;
        document.getElementById('itemExpired').textContent = itemExpired;
    }

    function showToast(type, message) {
        const colors = {
            success: 'bg-success',
            warning: 'bg-warning',
            error: 'bg-danger'
        };
        
        const toast = document.createElement('div');
        toast.className = `alert alert-dismissible fade show position-fixed ${colors[type]} text-white`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Initial ringkasan calculation
    updateRingkasan();

    // ✅ Clean up when page unloads
    window.addEventListener('beforeunload', () => {
        stopCameraImproved();
    });
});
</script>
@endpush