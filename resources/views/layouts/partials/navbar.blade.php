<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        {{-- Tombol Toggle Sidebar untuk Mobile (d-lg-none) --}}
        <button class="btn btn-link d-lg-none me-2" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4" style="color: var(--primary-color);"></i>
        </button>
        
        {{-- LOGO BRAND --}}
        <a class="navbar-brand d-flex align-items-center me-auto" href="{{ route('dashboard') }}" style="gap: 12px;">
            <div class="brand-icon-box">
                <i class="bi bi-capsule-pill text-white" style="font-size: 1.5rem; transform: rotate(-45deg);"></i>
            </div>
            <span class="brand-text">
                Apotek JM Farma
            </span>
        </a>
        
        {{-- Tombol Toggler Navigasi Utama (d-flex/ms-auto) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        {{-- Navigasi Kanan --}}
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                {{-- 🏢 FILTER CABANG (Super Admin Only) --}}
                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item me-3 d-flex align-items-center">
                    <select class="form-select form-select-sm" id="filterCabang" 
                            style="min-width: 200px; border-radius: 8px; border: 2px solid var(--primary-color); font-weight: 600;">
                        @foreach(\App\Models\Cabang::aktif()->orderBy('nama_cabang')->get() as $c)
                        <option value="{{ $c->id }}" {{ session('selected_cabang_id') == $c->id ? 'selected' : '' }}>
                            🏢 {{ $c->nama_cabang }}
                        </option>
                        @endforeach
                    </select>
                </li>
                @else
                {{-- TAMPILKAN CABANG USER (Admin Cabang & Kasir) --}}
                @if(auth()->user()->cabang)
                <li class="nav-item me-3">
                    <span class="badge" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 8px 14px; border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);">
                        <i class="bi bi-building me-1"></i>
                        {{ auth()->user()->cabang->nama_cabang }}
                    </span>
                </li>
                @endif
                @endif

                {{-- ⏳ SHIFT AKTIF (Kasir/Admin) --}}
                @php
                    $activeShift = \App\Models\Shift::where('user_id', auth()->id())
                        ->where('status', 'aktif')
                        ->whereNull('waktu_tutup')
                        ->first();
                @endphp
                
                @if($activeShift)
                <li class="nav-item me-3 d-flex align-items-center">
                    <span class="badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 8px 14px; border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                        <i class="bi bi-clock-fill me-1"></i>
                        Shift Aktif - {{ \Carbon\Carbon::parse($activeShift->waktu_buka)->format('H:i') }}
                    </span>
                </li>
                @endif
                
                {{-- 📅 WAKTU SAAT INI --}}
                <li class="nav-item me-3 d-flex align-items-center d-none d-md-block"> {{-- Sembunyikan di layar sangat kecil --}}
                    <span class="navbar-text" style="color: var(--text-secondary); font-weight: 500; font-size: 0.9rem;">
                        <i class="bi bi-calendar3 me-2" style="color: var(--primary-color);"></i>
                        <span id="current-datetime"></span>
                    </span>
                </li>
                
                {{-- 👤 DROPDOWN USER --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" style="gap: 8px;">
                        <div class="user-avatar-box">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                        <span style="font-weight: 600;">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 12px; padding: 0.5rem;">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="bi bi-person me-2" style="color: var(--primary-color);"></i>Profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.index') }}">
                                <i class="bi bi-gear me-2" style="color: var(--primary-color);"></i>Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider" style="margin: 0.5rem 0;"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- STYLES: Digabungkan dan Disederhanakan --}}
@push('styles')
<style>
    :root {
        /* Asumsi Anda memiliki variabel CSS ini */
        --primary-color: #667eea; 
        --text-secondary: #4b5563;
    }
    .brand-icon-box {
        width: 42px; 
        height: 42px; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        border-radius: 12px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35); 
        transition: all 0.3s ease;
    }
    .brand-text {
        font-weight: 700; 
        font-size: 1.35rem; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        -webkit-background-clip: text; 
        -webkit-text-fill-color: transparent; 
        letter-spacing: -0.5px;
    }
    .user-avatar-box {
        width: 32px; 
        height: 32px; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        border-radius: 8px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    }
    
    /* Hover effect untuk logo */
    .navbar-brand:hover .brand-icon-box {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
    }
    
    /* Hover effect untuk dropdown items */
    .dropdown-item {
        border-radius: 8px; 
        padding: 0.65rem 1rem; 
        transition: all 0.2s ease;
    }
    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: translateX(5px);
    }
    .dropdown-item.text-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
    }
</style>
@endpush

{{-- SCRIPTS: Dipindahkan ke @push('scripts') --}}
@push('scripts')
<script>
    // Update waktu real-time
    function updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const dtElement = document.getElementById('current-datetime');
        if (dtElement) {
             dtElement.textContent = now.toLocaleDateString('id-ID', options);
        }
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Logic AJAX untuk filter cabang
    const filterCabang = document.getElementById('filterCabang');
    if (filterCabang) {
        filterCabang.addEventListener('change', function() {
            const selectedCabangId = this.value;
            
            fetch("{{ route('set-cabang-filter') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cabang_id: selectedCabangId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Muat ulang halaman setelah filter berhasil diset
                    window.location.reload(); 
                } else {
                    alert('Gagal mengatur filter cabang: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses filter.');
            });
        });
    }
</script>
@endpush