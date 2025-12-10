<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <button class="btn btn-link d-lg-none me-2" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4" style="color: var(--primary-color);"></i>
        </button>
        
        {{-- ✅ LOGO BARU dengan Bootstrap Icon & Gradient Box --}}
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}" style="gap: 12px;">
            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35); transition: all 0.3s ease;">
                <i class="bi bi-capsule-pill text-white" style="font-size: 1.5rem; transform: rotate(-45deg);"></i>
            </div>
            <span style="font-weight: 700; font-size: 1.35rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.5px;">
                Apotek JM Farma
            </span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                
                @php
                    $activeShift = \App\Models\Shift::where('user_id', auth()->id())
                        ->where('status', 'aktif')
                        ->whereNull('waktu_tutup')
                        ->first();
                @endphp
                
                <li class="nav-item me-3">
                    @if($activeShift)
                        <span class="badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 8px 14px; border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-clock-fill me-1"></i>
                            Shift Aktif - {{ \Carbon\Carbon::parse($activeShift->waktu_buka)->format('H:i') }}
                        </span>
                    @endif
                </li>
                
                <li class="nav-item me-3 d-flex align-items-center">
                    <span class="navbar-text" style="color: var(--text-secondary); font-weight: 500; font-size: 0.9rem;">
                        <i class="bi bi-calendar3 me-2" style="color: var(--primary-color);"></i>
                        <span id="current-datetime"></span>
                    </span>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" style="gap: 8px;">
                        <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                        <span style="font-weight: 600;">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 12px; padding: 0.5rem;">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile') }}" style="border-radius: 8px; padding: 0.65rem 1rem; transition: all 0.2s ease;">
                                <i class="bi bi-person me-2" style="color: var(--primary-color);"></i>Profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.index') }}" style="border-radius: 8px; padding: 0.65rem 1rem; transition: all 0.2s ease;">
                                <i class="bi bi-gear me-2" style="color: var(--primary-color);"></i>Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider" style="margin: 0.5rem 0;"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger" style="border-radius: 8px; padding: 0.65rem 1rem; transition: all 0.2s ease;">
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

<style>
    /* ✅ Hover effect untuk logo */
    .navbar-brand:hover div {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
    }
    
    /* ✅ Hover effect untuk dropdown items */
    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        transform: translateX(5px);
    }
    
    .dropdown-item.text-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
    }
</style>

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
        document.getElementById('current-datetime').textContent = 
            now.toLocaleDateString('id-ID', options);
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>