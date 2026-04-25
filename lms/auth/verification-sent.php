<?php
/**
 * Verification Email Sent Page
 * Shown after successful registration when email verification is required
 */

require_once '../config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

// Get email from session if available (set by register.php)
$email = $_SESSION['pending_verification_email'] ?? '';
$masked_email = '';

if ($email) {
    // Mask email for privacy: s***@example.com
    $parts = explode('@', $email);
    if (count($parts) === 2) {
        $local = $parts[0];
        $domain = $parts[1];
        $masked_local = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 2)) . substr($local, -1);
        $masked_email = $masked_local . '@' . $domain;
    }
    // Clear from session after display
    unset($_SESSION['pending_verification_email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .verification-sent-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .verification-sent-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            max-width: 550px;
            width: 90%;
            text-align: center;
        }
        .email-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1.5rem;
        }
        .email-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
            font-weight: 500;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="verification-sent-container">
        <div class="verification-sent-card">
            <div class="email-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h2 class="mb-3">Verify Your Email</h2>
            <p class="text-muted">
                Registration successful! To complete your account setup, please verify your email address.
            </p>
            
            <?php if ($masked_email): ?>
            <div class="email-box">
                <i class="fas fa-envelope me-2 text-primary"></i>
                We've sent a verification link to <strong><?= escape_html($masked_email) ?></strong>
            </div>
            <?php else: ?>
            <div class="email-box">
                <i class="fas fa-envelope me-2 text-primary"></i>
                We've sent a verification link to your email address
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info text-start" role="alert">
                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Next Steps:</h6>
                <ol class="mb-0 ps-3">
                    <li>Check your email inbox (and spam/junk folder)</li>
                    <li>Click the verification link in the email</li>
                    <li>Once verified, you can log in to your account</li>
                </ol>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <a href="resend-verification.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
                </a>
                <a href="login.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                </a>
            </div>
            
            <div class="mt-4 text-muted small">
                <p class="mb-0">Didn't receive the email? Make sure to check your spam or junk folder.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

