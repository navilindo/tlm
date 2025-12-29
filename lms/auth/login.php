<?php
/**
 * Login Page
 * User authentication and login form
 */

require_once '../config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if (is_post()) {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
            throw new Exception("Security token validation failed");
        }
        
        // Validate required fields
        $errors = validate_required($_POST, ['email', 'password']);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $remember_me = isset($_POST['remember_me']);
        
        // Attempt login
        $user = login_user($email, $password, $remember_me);
        
        // Redirect based on role
        $redirect_url = '../index.php';
        if (has_role(ROLE_ADMIN)) {
            $redirect_url = '../admin/dashboard.php';
        } elseif (has_role(ROLE_INSTRUCTOR)) {
            $redirect_url = '../instructor/dashboard.php';
        } elseif (has_role(ROLE_STUDENT)) {
            $redirect_url = '../student/dashboard.php';
        }
        
        redirect_with_message($redirect_url, 'Login successful! Welcome back.', 'success');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle flash messages
$flash = get_flash_message();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left side - Branding -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-primary text-white">
                <div class="text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-graduation-cap me-3"></i>
                        <?= SITE_NAME ?>
                    </h1>
                    <p class="lead mb-4">Welcome to your learning platform</p>
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fas fa-book fa-3x mb-3"></i>
                            <h5>Learn</h5>
                            <p>Access quality courses from expert instructors</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5>Connect</h5>
                            <p>Join a community of learners and educators</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h5>Grow</h5>
                            <p>Track your progress and achieve your goals</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-form-container">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark">Sign In</h2>
                        <p class="text-muted">Access your learning account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= escape_html($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= escape_html($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?= csrf_token_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   value="<?= escape_html($_POST['email'] ?? '') ?>"
                                   required 
                                   autocomplete="email">
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember_me" 
                                   name="remember_me">
                            <label class="form-check-label" for="remember_me">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="forgot-password.php" class="text-decoration-none">
                                <i class="fas fa-key me-2"></i>Forgot Password?
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-bold">
                                    Sign Up
                                </a>
                            </p>
                        </div>
                    </form>
                    
                    <!-- Demo Accounts -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2">Demo Accounts:</h6>
                        <small class="text-muted">
                            <strong>Admin:</strong> admin@lms.com / admin123<br>
                            <strong>Instructor:</strong> instructor@lms.com / instructor123<br>
                            <strong>Student:</strong> student@lms.com / student123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
