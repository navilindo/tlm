<?php
/**
 * Registration Page
 * User registration form with email verification
 */

require_once '../config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle registration form submission
if (is_post()) {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
            throw new Exception("Security token validation failed");
        }
        
        // Validate required fields
        $errors = validate_required($_POST, ['email', 'password', 'first_name', 'last_name', 'role']);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
        
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $role = sanitize_input($_POST['role']);
        
        // Validate email
        if (!validate_email($email)) {
            throw new Exception("Please enter a valid email address");
        }
        
        // Validate password
        if (!validate_password($password)) {
            throw new Exception("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long");
        }
        
        // Check password confirmation
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }
        
        // Validate role
        $allowed_roles = [ROLE_STUDENT, ROLE_INSTRUCTOR];
        if (!in_array($role, $allowed_roles)) {
            throw new Exception("Invalid role selected");
        }
        
        // Register user
        $user_id = register_user($email, $password, $first_name, $last_name, $role);
        
        // Check if email verification is required
        $require_verification = get_system_setting('email_verification_required');
        if ($require_verification === 'true') {
            redirect_with_message('login.php', 'Registration successful! Please check your email to verify your account before logging in.', 'info');
        } else {
            // Auto-login and redirect
            $user = login_user($email, $password);
            $redirect_url = '../index.php';
            if (has_role(ROLE_INSTRUCTOR)) {
                $redirect_url = '../instructor/dashboard.php';
            } elseif (has_role(ROLE_STUDENT)) {
                $redirect_url = '../student/dashboard.php';
            }
            redirect_with_message($redirect_url, 'Registration successful! Welcome to ' . SITE_NAME, 'success');
        }
        
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
    <title>Register - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left side - Branding -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-success text-white">
                <div class="text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-user-plus me-3"></i>
                        Join <?= SITE_NAME ?>
                    </h1>
                    <p class="lead mb-4">Start your learning journey today</p>
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                            <h5>Learn</h5>
                            <p>Access thousands of courses from expert instructors</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-certificate fa-3x mb-3"></i>
                            <h5>Earn</h5>
                            <p>Get certificates upon completion</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-trophy fa-3x mb-3"></i>
                            <h5>Grow</h5>
                            <p>Advance your career with new skills</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Registration Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="register-form-container">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark">Create Account</h2>
                        <p class="text-muted">Join our learning community</p>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-2"></i>First Name
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?= escape_html($_POST['first_name'] ?? '') ?>"
                                       required 
                                       autocomplete="given-name">
                                <div class="invalid-feedback">
                                    Please enter your first name.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-2"></i>Last Name
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?= escape_html($_POST['last_name'] ?? '') ?>"
                                       required 
                                       autocomplete="family-name">
                                <div class="invalid-feedback">
                                    Please enter your last name.
                                </div>
                            </div>
                        </div>
                        
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
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag me-2"></i>I want to join as
                            </label>
                            <select class="form-select form-select-lg" id="role" name="role" required>
                                <option value="">Select your role</option>
                                <option value="<?= ROLE_STUDENT ?>" <?= ($_POST['role'] ?? '') === ROLE_STUDENT ? 'selected' : '' ?>>
                                    Student - Learn from courses
                                </option>
                                <option value="<?= ROLE_INSTRUCTOR ?>" <?= ($_POST['role'] ?? '') === ROLE_INSTRUCTOR ? 'selected' : '' ?>>
                                    Instructor - Create and teach courses
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Please select your role.
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
                                       autocomplete="new-password"
                                       minlength="<?= PASSWORD_MIN_LENGTH ?>">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least <?= PASSWORD_MIN_LENGTH ?> characters long
                            </div>
                            <div class="invalid-feedback">
                                Password must be at least <?= PASSWORD_MIN_LENGTH ?> characters long.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
                                   autocomplete="new-password">
                            <div class="invalid-feedback">
                                Please confirm your password.
                            </div>
                            <div class="form-text text-danger" id="passwordMatch" style="display: none;">
                                Passwords do not match
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="terms" 
                                   name="terms" 
                                   required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                and <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                            <div class="invalid-feedback">
                                You must agree to the terms and conditions.
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                Create Account
                            </button>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="login.php" class="text-decoration-none fw-bold">
                                    Sign In
                                </a>
                            </p>
                        </div>
                    </form>
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
        
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        
        function validatePasswordMatch() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                passwordMatch.style.display = 'block';
            } else {
                confirmPassword.setCustomValidity('');
                passwordMatch.style.display = 'none';
            }
        }
        
        password.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
    </script>
</body>
</html>
