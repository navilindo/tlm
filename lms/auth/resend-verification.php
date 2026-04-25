<?php
/**
 * Resend Verification Email Page
 * Allows users to request a new verification email
 */

require_once '../config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if (is_post()) {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
            throw new Exception("Security token validation failed");
        }
        
        // Validate email
        $errors = validate_required($_POST, ['email']);
        if (!empty($errors)) {
            throw new Exception("Please enter your email address");
        }
        
        $email = sanitize_input($_POST['email']);
        
        if (!validate_email($email)) {
            throw new Exception("Please enter a valid email address");
        }
        
        // Check if user exists and is not already verified
        $db = getDB();
        $user = $db->fetch("SELECT id, first_name, email, is_verified, verification_token FROM users WHERE email = ? AND status = ?", 
            [$email, USER_STATUS_ACTIVE]);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            $success = 'If an account with that email exists and is not verified, a new verification email has been sent.';
        } elseif ($user['is_verified']) {
            $success = 'Your email address is already verified. You can log in to your account.';
        } else {
            // Generate new verification token
            $new_token = generate_secure_token(32);
            
            // Update user with new token
            $db->execute("UPDATE users SET verification_token = ? WHERE id = ?", [$new_token, $user['id']]);
            
            // Send verification email
            send_verification_email($user['email'], $user['first_name'], $new_token);
            
            log_security_event('verification_resent', "Verification email resent for: {$email}");
            
            $success = 'A new verification email has been sent to your email address. Please check your inbox and spam folder.';
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
    <title>Resend Verification Email - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="container-fluid h-90 card p-5">
        <div class="row h-100 m-5 p-5">
            <!-- Left side - Branding -->
            <div class="col-lg-8 d-flex align-items-center justify-content-center bg-info text-white">
                <div class="text-center">
                    <h1><i class="fas fa-envelope-open-text me-3 fa-2x pt-5"></i></h1>
                    <h1 class="display-4 fw-bold mb-4">
                        <?= SITE_NAME ?>
                    </h1>
                    <p class="lead mb-4">Didn't receive your verification email?</p>
                    <div class="row p-5">
                        <div class="col-md-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h5>Check Inbox</h5>
                            <p>Look for our email in your inbox</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-filter fa-3x mb-3"></i>
                            <h5>Spam Folder</h5>
                            <p>Sometimes emails end up in spam</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-paper-plane fa-3x mb-3"></i>
                            <h5>Resend</h5>
                            <p>Request a new verification email</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Resend Form -->
            <div class="col-lg-4 d-flex align-items-center justify-content-center">
                <div class="login-form-container h-100">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark">Resend Verification</h2>
                        <p class="text-muted">Enter your email to receive a new verification link</p>
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
                    
                    <?php if (!$success): ?>
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
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-info btn-lg text-white">
                                <i class="fas fa-paper-plane me-2"></i>
                                Resend Verification Email
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">
                            <a href="login.php" class="text-decoration-none fw-bold">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </p>
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-bold">
                                Sign Up
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>

