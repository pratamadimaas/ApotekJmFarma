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
        }
        
        /* Navbar Modern */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
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
        
        /* Sidebar Ultra Modern */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #ffffff 0%, #fafbff 100%);
            color: var(--text-primary);
            position: fixed;
            top: 76px;
            left: 0;
            width: 280px;
            padding: 2rem 0;
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            z-index: 999;
            overflow-y: auto;
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
        }
        
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            transform: scale(1.1) rotate(5deg);
        }
        
        /* Main Content Modern */
        .main-content {
            margin-left: 280px;
            margin-top: 76px;
            padding: 2.5rem;
            min-height: calc(100vh - 76px);
        }
        
        /* Card Ultra Modern */
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
        
        /* Button Modern dengan Gradient */
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
        
        /* Alert Modern */
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
        
        /* Footer Modern */
        .footer {
            margin-left: 280px;
            padding: 1.5rem 2.5rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Table Modern */
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
        
        /* Form Modern */
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
        
        /* Badge Modern */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }
        
        /* Scrollbar Custom */
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
        
        /* Loading Animation */
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
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
            }
            
            .main-content,
            .footer {
                margin-left: 240px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content,
            .footer {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    @include('layouts.partials.navbar')
    
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
        <p class="mb-0">© 2024 Apotek Sehat | Version 1.0 | Powered by Laravel</p>
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
        
        // Smooth scroll behavior
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
        
        // Mobile sidebar toggle
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>