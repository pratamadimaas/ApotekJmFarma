<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Apotek JM Farma')</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #5a67d8;
            --dark-bg: #0f172a;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: rgba(148, 163, 184, 0.1);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 0px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
            color: var(--text-primary);
            overflow-x: hidden;
            padding-top: 76px;
        }
        
        /* ========================================
           NAVBAR MODERN
           ======================================== */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1050;
            transition: all 0.3s ease;
        }
        
        .navbar-custom:hover {
            box-shadow: var(--shadow-md);
        }
        
        .navbar-custom .navbar-brand {
            color: var(--text-primary) !important;
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        
        .navbar-custom .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 1rem !important;
        }
        
        .navbar-custom .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .navbar-custom .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .navbar-custom .nav-link:hover::after {
            width: 80%;
        }
        
        /* ========================================
           🔥 TOMBOL TOGGLE SIDEBAR (Desktop)
           ======================================== */
        .sidebar-toggle-btn {
            position: fixed;
            top: 85px;
            left: 15px;
            width: 40px;
            height: 40px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1045;
            box-shadow: var(--shadow-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar-toggle-btn:hover {
            background: var(--primary-gradient);
            color: white;
            transform: scale(1.1);
            box-shadow: var(--shadow-lg);
        }
        
        .sidebar-toggle-btn i {
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        /* Tombol tetap di posisi kiri saat sidebar collapsed */
        body.sidebar-collapsed .sidebar-toggle-btn {
            left: 15px;
        }
        
        /* ========================================
           SIDEBAR OVERLAY (Mobile)
           ======================================== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1030;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
        
        /* ========================================
           🔥 SIDEBAR ULTRA MODERN (DENGAN COLLAPSE)
           ======================================== */
        .sidebar {
            min-height: calc(100vh - 76px);
            background: linear-gradient(180deg, #ffffff 0%, #fafbff 100%);
            color: var(--text-primary);
            position: fixed;
            top: 76px;
            left: 0;
            width: var(--sidebar-width);
            padding: 2rem 0;
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: var(--primary-gradient);
            opacity: 0.03;
            border-radius: 0 0 50% 50%;
        }
        
        /* 🔥 STATE: Sidebar Collapsed - BENAR-BENAR TERSEMBUNYI */
        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
            overflow: hidden;
            border-right: none;
        }
        
        /* Hide semua konten saat collapsed */
        body.sidebar-collapsed .sidebar * {
            opacity: 0;
            visibility: hidden;
        }
        
        .sidebar .nav-link {
            color: var(--text-secondary);
            padding: 0.875rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            left: 0;
            opacity: 1;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 28px;
            height: 28px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .sidebar .nav-link span {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            transform: scale(1.1) rotate(5deg);
        }
        
        /* ========================================
           🔥 MAIN CONTENT (RESPONSIVE TO SIDEBAR)
           ======================================== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 0;
            padding: 2.5rem;
            min-height: calc(100vh - 76px);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* ========================================
           CARD ULTRA MODERN
           ======================================== */
        .card-custom {
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            background: var(--card-bg);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .card-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card-custom:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(102, 126, 234, 0.2);
        }
        
        .card-custom:hover::before {
            opacity: 1;
        }
        
        .card-custom .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }
        
        .card-custom .card-body {
            padding: 1.75rem;
        }
        
        /* ========================================
           BUTTON MODERN
           ======================================== */
        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 100%);
            transition: left 0.5s ease;
        }
        
        .btn-primary-custom:hover::before {
            left: 100%;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary-custom:active {
            transform: translateY(0);
        }
        
        /* ========================================
           ALERT MODERN
           ======================================== */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
            border-left: 4px solid;
            animation: slideInDown 0.4s ease;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            color: #065f46;
            border-left-color: #10b981;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
            color: #991b1b;
            border-left-color: #ef4444;
        }
        
        /* ========================================
           🔥 FOOTER (RESPONSIVE TO SIDEBAR)
           ======================================== */
        .footer {
            margin-left: var(--sidebar-width);
            padding: 1.5rem 2.5rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body.sidebar-collapsed .footer {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* ========================================
           TABLE MODERN
           ======================================== */
        .table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        
        .table thead th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            color: var(--text-primary);
            font-weight: 600;
            border: none;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody tr {
            background: white;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow-md);
        }
        
        .table tbody td {
            padding: 1rem 1.25rem;
            border: none;
            vertical-align: middle;
        }
        
        .table tbody tr td:first-child {
            border-radius: 12px 0 0 12px;
        }
        
        .table tbody tr td:last-child {
            border-radius: 0 12px 12px 0;
        }
        
        /* ========================================
           FORM MODERN
           ======================================== */
        .form-control,
        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        /* ========================================
           BADGE MODERN
           ======================================== */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }
        
        /* ========================================
           SCROLLBAR CUSTOM
           ======================================== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b3fa0 100%);
        }
        
        /* ========================================
           LOADING ANIMATION
           ======================================== */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* ========================================
           RESPONSIVE MOBILE
           ======================================== */
        @media (max-width: 991px) {
            body {
                padding-top: 70px;
            }
            
            /* Sidebar di mobile - keluar dari kiri */
            .sidebar {
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                box-shadow: var(--shadow-xl);
                z-index: 1045;
                width: var(--sidebar-width) !important;
                border-right: none;
            }
            
            /* Tampilkan semua konten di mobile */
            .sidebar *,
            body.sidebar-collapsed .sidebar * {
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            .sidebar .nav-link {
                justify-content: flex-start !important;
                padding: 0.875rem 1.5rem !important;
            }
            
            .sidebar .nav-link i {
                margin-right: 12px !important;
            }
            
            /* Sidebar muncul saat show */
            .sidebar.show {
                transform: translateX(0);
            }
            
            /* Reset margin di mobile */
            .main-content,
            .footer {
                margin-left: 0 !important;
            }
            
            .main-content {
                padding: 1.5rem;
                margin-top: 0;
            }
            
            .navbar-custom {
                padding: 1rem 1.5rem;
            }
            
            /* Hide toggle button di mobile */
            .sidebar-toggle-btn {
                display: none !important;
            }
            
            /* Disable collapse state di mobile */
            body.sidebar-collapsed .sidebar {
                width: var(--sidebar-width) !important;
                overflow-y: auto;
                overflow-x: hidden;
            }
        }
        
        /* Prevent body scroll saat sidebar terbuka di mobile */
        body.sidebar-open {
            overflow: hidden;
        }
        
        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0);
            }
            
            #sidebarToggle,
            #sidebarClose {
                display: none !important;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- 🔥 TOMBOL TOGGLE SIDEBAR (Desktop Only) -->
    <div class="sidebar-toggle-btn d-none d-lg-flex" id="desktopSidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left"></i>
    </div>
    
    <!-- Navbar -->
    @include('layouts.partials.navbar')
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    @include('layouts.partials.sidebar')
    
    <!-- Main Content -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p class="mb-0">© 2024 Apotek JM Farma | Version 1.0 | Powered by Laravel</p>
    </footer>
    
    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (Optional, untuk AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Chart.js (untuk Dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // Setup CSRF Token untuk AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // ========================================
        // 🔥 SIDEBAR TOGGLE (Desktop & Mobile)
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
            
            // Function untuk buka sidebar (Mobile)
            function openSidebar() {
                sidebar.classList.add('show');
                sidebarOverlay.classList.add('show');
                document.body.classList.add('sidebar-open');
            }
            
            // Function untuk tutup sidebar (Mobile)
            function closeSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
            
            // 🔥 Function untuk toggle collapse sidebar (Desktop)
            function toggleSidebarCollapse() {
                document.body.classList.toggle('sidebar-collapsed');
                
                // Update icon
                const icon = desktopSidebarToggle.querySelector('i');
                if (document.body.classList.contains('sidebar-collapsed')) {
                    icon.classList.remove('bi-chevron-left');
                    icon.classList.add('bi-chevron-right');
                    desktopSidebarToggle.title = 'Expand Sidebar';
                    
                    // Simpan state ke localStorage
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-left');
                    desktopSidebarToggle.title = 'Collapse Sidebar';
                    
                    // Simpan state ke localStorage
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            }
            
            // 🔥 Load saved state dari localStorage
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                document.body.classList.add('sidebar-collapsed');
                const icon = desktopSidebarToggle.querySelector('i');
                icon.classList.remove('bi-chevron-left');
                icon.classList.add('bi-chevron-right');
                desktopSidebarToggle.title = 'Expand Sidebar';
            }
            
            // Event listener untuk toggle button (Mobile)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openSidebar();
                });
            }
            
            // Event listener untuk close button (Mobile)
            if (sidebarClose) {
                sidebarClose.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeSidebar();
                });
            }
            
            // 🔥 Event listener untuk desktop toggle
            if (desktopSidebarToggle) {
                desktopSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSidebarCollapse();
                });
            }
            
            // Event listener untuk overlay (klik di luar sidebar untuk tutup - Mobile)
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }
            
            // Tutup sidebar saat link diklik (mobile only)
            const sidebarLinks = sidebar.querySelectorAll('.nav-link:not([data-bs-toggle])');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
            
            // Tutup sidebar saat window di-resize ke desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        });
        
        // ========================================
        // SMOOTH SCROLL
        // ========================================
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>