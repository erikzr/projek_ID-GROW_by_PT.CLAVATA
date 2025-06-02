<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Inventory App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-floating > label {
            color: #6c757d;
        }
        
        .btn-auth {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .auth-toggle {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .auth-toggle:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .form-floating > .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .loading {
            display: none;
        }
        
        .loading.show {
            display: inline-block;
        }
        
        .auth-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                <div class="auth-container p-5">
                    <!-- Login Form -->
                    <div id="login-section">
                        <div class="text-center mb-4">
                            <h2 class="auth-header">
                                <i class="fas fa-box-open me-2"></i>
                                Login to Inventory
                            </h2>
                            <p class="text-muted">Welcome back! Please login to your account.</p>
                        </div>

                        <form id="login-form">
                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="login-email" class="form-control" placeholder="name@example.com" required>
                                <label for="login-email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" name="password" id="login-password" class="form-control" placeholder="Password" required>
                                <label for="login-password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>

                            <button type="submit" class="btn btn-auth btn-primary w-100 mb-3">
                                <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>

                            <div id="login-error" class="alert alert-danger d-none" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span class="error-message"></span>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">Don't have an account? 
                                <a href="#" class="auth-toggle" onclick="showRegister()">
                                    <i class="fas fa-user-plus me-1"></i>Register here
                                </a>
                            </p>
                        </div>
                    </div>

                    <!-- Register Form -->
                    <div id="register-section" style="display: none;">
                        <div class="text-center mb-4">
                            <h2 class="auth-header">
                                <i class="fas fa-user-plus me-2"></i>
                                Create Account
                            </h2>
                            <p class="text-muted">Join us! Create your inventory account.</p>
                        </div>

                        <form id="register-form">
                            <div class="form-floating mb-3">
                                <input type="text" name="name" id="register-name" class="form-control" placeholder="Full Name" required>
                                <label for="register-name"><i class="fas fa-user me-2"></i>Full Name</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="register-email" class="form-control" placeholder="name@example.com" required>
                                <label for="register-email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="register-password" class="form-control" placeholder="Password" required minlength="8">
                                <label for="register-password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" name="password_confirmation" id="register-password-confirm" class="form-control" placeholder="Confirm Password" required>
                                <label for="register-password-confirm"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            </div>

                            <button type="submit" class="btn btn-auth btn-success w-100 mb-3">
                                <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>

                            <div id="register-error" class="alert alert-danger d-none" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span class="error-message"></span>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">Already have an account? 
                                <a href="#" class="auth-toggle" onclick="showLogin()">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login here
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toggle between login and register
        function showRegister() {
            document.getElementById('login-section').style.display = 'none';
            document.getElementById('register-section').style.display = 'block';
            document.title = 'Register | Inventory App';
            clearErrors();
        }

        function showLogin() {
            document.getElementById('register-section').style.display = 'none';
            document.getElementById('login-section').style.display = 'block';
            document.title = 'Login | Inventory App';
            clearErrors();
        }

        // Clear error messages
        function clearErrors() {
            document.getElementById('login-error').classList.add('d-none');
            document.getElementById('register-error').classList.add('d-none');
        }

        // Show error message
        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.querySelector('.error-message').textContent = message;
            errorElement.classList.remove('d-none');
        }

        // Show loading state
        function setLoading(formId, isLoading) {
            const form = document.getElementById(formId);
            const button = form.querySelector('button[type="submit"]');
            const loading = button.querySelector('.loading');
            
            if (isLoading) {
                button.disabled = true;
                loading.classList.add('show');
            } else {
                button.disabled = false;
                loading.classList.remove('show');
            }
        }

        // Login form handler
        document.getElementById('login-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();
            setLoading('login-form', true);

            const formData = new FormData(this);
            const data = {
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    // Handle validation errors
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join(', ');
                        throw new Error(errorMessages);
                    }
                    throw new Error(result.message || 'Login failed');
                }

                if (result.success) {
                    // Simpan token dan user data
                    sessionStorage.setItem('auth_token', result.data.token);
                    sessionStorage.setItem('user_data', JSON.stringify(result.data.user));
                    
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'alert alert-success';
                    successDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Login successful! Welcome back, ' + result.data.user.name + '!';
                    document.getElementById('login-form').prepend(successDiv);
                    
                    // Redirect after 1.5 seconds
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Login failed');
                }
            } catch (error) {
                console.error('Login error:', error);
                showError('login-error', error.message);
            } finally {
                setLoading('login-form', false);
            }
        });

        // Register form handler
        document.getElementById('register-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();
            setLoading('register-form', true);

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password'),
                password_confirmation: formData.get('password_confirmation')
            };

            // Client-side validation
            if (data.password !== data.password_confirmation) {
                showError('register-error', 'Passwords do not match');
                setLoading('register-form', false);
                return;
            }

            if (data.password.length < 8) {
                showError('register-error', 'Password must be at least 8 characters');
                setLoading('register-form', false);
                return;
            }

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    // Handle validation errors
                    if (response.status === 422 && result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join(', ');
                        throw new Error(errorMessages);
                    }
                    throw new Error(result.message || 'Registration failed');
                }

                if (result.success) {
                    // Simpan token dan user data
                    sessionStorage.setItem('auth_token', result.data.token);
                    sessionStorage.setItem('user_data', JSON.stringify(result.data.user));
                    
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'alert alert-success';
                    successDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Registration successful! Welcome, ' + result.data.user.name + '!';
                    document.getElementById('register-form').prepend(successDiv);
                    
                    // Redirect after 1.5 seconds
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Registration failed');
                }
            } catch (error) {
                console.error('Registration error:', error);
                showError('register-error', error.message);
            } finally {
                setLoading('register-form', false);
            }
        });

        // Check if user is already logged in
        window.addEventListener('load', function() {
            const token = sessionStorage.getItem('auth_token');
            if (token) {
                // Verify token with server
                fetch('/api/auth/user', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = '/dashboard';
                    } else {
                        // Token invalid, clear storage
                        sessionStorage.removeItem('auth_token');
                        sessionStorage.removeItem('user_data');
                    }
                })
                .catch(() => {
                    // Network error, clear storage
                    sessionStorage.removeItem('auth_token');
                    sessionStorage.removeItem('user_data');
                });
            }
        });
    </script>
</body>
</html>