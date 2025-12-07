<div class="sidebar">
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>
        
        <a class="nav-link {{ request()->routeIs('penjualan.*') ? 'active' : '' }}" href="{{ route('penjualan.index') }}">
            <i class="bi bi-cart-check"></i>
            Kasir (Penjualan)
        </a>
        
        <a class="nav-link {{ request()->routeIs('pembelian.*') ? 'active' : '' }}" href="{{ route('pembelian.index') }}">
            <i class="bi bi-bag-plus"></i>
            Pembelian
        </a>
        
        <hr class="text-white opacity-50 mx-3">
        
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs(['barang.*', 'supplier.*']) ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               href="#masterDataMenu" 
               role="button">
                <i class="bi bi-database"></i>
                Master Data
                <i class="bi bi-chevron-down float-end"></i>
            </a>
            <div class="collapse {{ request()->routeIs(['barang.*', 'supplier.*']) ? 'show' : '' }}" id="masterDataMenu">
                <nav class="nav flex-column ms-3">
                    <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" 
                        href="{{ route('barang.index') }}">
                        <i class="bi bi-capsule"></i>
                        Barang/Obat
                    </a>
                    <a class="nav-link {{ request()->routeIs('supplier.*') ? 'active' : '' }}" 
                        href="{{ route('supplier.index') }}">
                        <i class="bi bi-truck"></i>
                        Supplier
                    </a>
                </nav>
            </div>
        </div>
        
        <hr class="text-white opacity-50 mx-3">
        
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs('shift.*') ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               href="#shiftMenu" 
               role="button">
                <i class="bi bi-clock-history"></i>
                Shift Kasir
                <i class="bi bi-chevron-down float-end"></i>
            </a>

            <div class="collapse {{ request()->routeIs('shift.*') ? 'show' : '' }}" id="shiftMenu">

                <nav class="nav flex-column ms-3">

                    <a class="nav-link {{ request()->routeIs('shift.buka.form') ? 'active' : '' }}"
                    href="{{ route('shift.buka.form') }}">
                        <i class="bi bi-unlock"></i>
                        Buka Shift
                    </a>

                    <a class="nav-link {{ request()->routeIs('shift.tutup.form') ? 'active' : '' }}"
                    href="{{ route('shift.tutup.form') }}">
                        <i class="bi bi-lock"></i>
                        Tutup Shift
                    </a>
                    
                    <a class="nav-link {{ request()->routeIs(['shift.riwayat', 'shift.detail', 'shift.cetakLaporan']) ? 'active' : '' }}"
                    href="{{ route('shift.riwayat') }}">
                        <i class="bi bi-list-columns"></i>
                        Riwayat Shift
                    </a>

                </nav>
            </div>
        </div>
        
        {{-- 🟢 FITUR BARU: STOK OPNAME --}}
        <a class="nav-link {{ request()->routeIs('stokopname.*') ? 'active' : '' }}" 
            href="{{ route('stokopname.index') }}">
            <i class="bi bi-card-checklist"></i>
            Stok Opname
        </a>
        
        <a class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">
            <i class="bi bi-file-earmark-bar-graph"></i>
            Laporan
        </a>
        
        <hr class="text-white opacity-50 mx-3">
        
        @if(auth()->user()->role == 'admin')
        <div class="nav-item">
            <a class="nav-link {{ request()->routeIs(['users.*', 'settings.*']) ? 'active' : '' }}" 
               data-bs-toggle="collapse" 
               href="#settingsMenu" 
               role="button">
                <i class="bi bi-gear"></i>
                Pengaturan
                <i class="bi bi-chevron-down float-end"></i>
            </a>
            <div class="collapse {{ request()->routeIs(['users.*', 'settings.*']) ? 'show' : '' }}" id="settingsMenu">
                <nav class="nav flex-column ms-3">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" 
                        href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i>
                        Manajemen User
                    </a>
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" 
                        href="{{ route('settings.index') }}">
                        <i class="bi bi-sliders"></i>
                        Setting Aplikasi
                    </a>
                </nav>
            </div>
        </div>
        @endif
        
    </nav>
    
    <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center" style="opacity: 0.7;">
        <small>
            <i class="bi bi-info-circle"></i> 
            v1.0.0 | Laravel {{ app()->version() }}
        </small>
    </div>
</div>