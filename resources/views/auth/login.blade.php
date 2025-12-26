<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Solusi Digital Untuk Apotek Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background-color: #f5f5f5;
        }
        
        .left-section {
            flex: 1;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .right-section {
            flex: 1;
            background-color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-title {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .social-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            background-color: #f9fafb;
            border-color: #2563eb;
            color: #2563eb;
        }
        
        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            height: 50px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }
        
        .form-control:focus {
            background-color: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .form-check {
            margin-bottom: 25px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: #ef4444;
            border-color: #ef4444;
        }
        
        .form-check-label {
            font-size: 14px;
            color: #6b7280;
            margin-left: 8px;
            cursor: pointer;
        }
        
        .forgot-password {
            font-size: 14px;
            color: #6b7280;
            text-decoration: none;
            float: right;
            margin-top: -20px;
            margin-bottom: 25px;
            display: inline-block;
        }
        
        .forgot-password:hover {
            color: #2563eb;
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            background-color: #ef4444;
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .welcome-content {
            text-align: center;
        }
        
        .welcome-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .welcome-text {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .logo-section {
            margin-bottom: 40px;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background-color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        @media (max-width: 991px) {
            body {
                flex-direction: column;
            }
            
            .right-section {
                display: none;
            }
            
            .left-section {
                min-height: 100vh;
            }
        }
    </style>
</head>
<body>
    <div class="left-section">
        <div class="login-container">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-capsule"></i>
                </div>
                <div class="logo-text">Sistem Manajemen Apotek</div>
            </div>
            
            <h2 class="login-title">Sign In</h2>
            

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">USERNAME</label>
                    <input type="text" 
                           name="username" 
                           class="form-control @error('username') is-invalid @enderror" 
                           placeholder="Username"
                           value="{{ old('username') }}"
                           required 
                           autofocus>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">PASSWORD</label>
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Password"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
                
                <a href="#" class="forgot-password">Forgot Password</a>

                <button type="submit" class="btn-login">
                    Sign In
                </button>
            </form>
        </div>
    </div>
    
    <div class="right-section">
        <div class="welcome-content">
            <h1 class="welcome-title">Welcome to login</h1>
            <p class="welcome-text">
                Sistem Informasi Manajemen Apotek<br>
                Solusi Digital Untuk Apotek Modern
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>