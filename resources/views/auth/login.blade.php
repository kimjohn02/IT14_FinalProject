<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SAR EQUIP</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa url('{{ asset('images/medical_background.png') }}') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        :root {
            --congress-blue: #06448a;
            --sar-red: #d32f2f;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .login-header {
            background: white;
            color: black;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--sar-red) 0%, #ff5252 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #ff5252 0%, var(--sar-red) 100%);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--sar-red);
            box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card card">
        <div class="login-header">
            <div class="d-flex align-items-center justify-content-center">
                <div>
                    <img src="{{ asset('images/sar_equip_logo.png') }}" alt="Company Logo" class="img-fluid me-3" style="max-height: 100px;">
                    <h4 class="mb-0 fw-bold">SAR EQUIP</h4>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('message'))
                <div class="alert alert-warning">
                    <i class="bi bi-clock-history me-2"></i>
                    {{ session('message') }}
                </div>
            @endif
            <form method="POST" action="/login">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" 
                               id="username" name="username" 
                               value="{{ old('username') }}"
                               placeholder="Enter your username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" 
                               id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button> 
            </form>
            
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script>
        const alerts = document.querySelectorAll('.alert-success, .alert-danger, .alert-warning');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
      </script>
</body>
</html>