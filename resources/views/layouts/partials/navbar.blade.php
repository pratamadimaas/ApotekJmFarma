<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <button class="btn btn-link d-lg-none me-2" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4" style="color: var(--primary-color);"></i>
        </button>
        
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-heart-pulse-fill"></i> Apotek JM Farma
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                
                @php
                    // Asumsi: status 'open' adalah yang benar, dan waktu_tutup = NULL juga bisa digunakan
                    $activeShift = \App\Models\Shift::where('user_id', auth()->id())
                        ->where('status', 'aktif') // Menggunakan status 'aktif' sesuai Controller Shift Anda
                        ->whereNull('waktu_tutup') // Tambahkan pengecekan ini untuk lebih aman
                        ->first();
                @endphp
                
                <li class="nav-item me-3">
                    @if($activeShift)
                        <span class="badge bg-success">
                            <i class="bi bi-clock me-1"></i>
                            Shift Aktif - {{ \Carbon\Carbon::parse($activeShift->waktu_buka)->format('H:i') }}
                        </span>
                    {{-- Blok @else dihilangkan sesuai permintaan --}}
                    @endif
                </li>
                
                <li class="nav-item me-3">
                    <span class="navbar-text text-white">
                        <i class="bi bi-calendar3 me-1"></i>
                        <span id="current-datetime"></span>
                    </span>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ auth()->user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="bi bi-person me-2"></i>Profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.index') }}">
                                <i class="bi bi-gear me-2"></i>Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
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