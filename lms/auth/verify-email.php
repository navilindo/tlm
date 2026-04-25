<?php
/**
 * Email Verification Page
 * Handles email verification when user clicks the link from their email
 */

require_once '../config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Invalid verification link. No token provided.';
} else {
    try {
        if (verify_user_email($token)) {
            $success = 'Your email has been verified successfully! You can now log in to your account.';
        } else {
            $error = 'Invalid or expired verification token. Please request a new verification email.';
        }
    } catch (Exception $e) {
        $error = 'An error occurred while verifying your email. Please try again later.';
        error_log('Email verification error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        .verification-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        .success-icon { color: #28a745; }
        .error-icon { color: #dc3545; }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <?php if ($success): ?>
                <div class="verification-icon success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="mb-3">Email Verified!</h2>
                <div class="alert alert-success" role="alert">
                    <?= escape_html($success) ?>
                </div>
                <div class="mt-4">
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                    </a>
                </div>
            <?php elseif ($error): ?>
                <div class="verification-icon error-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="mb-3">Verification Failed</h2>
                <div class="alert alert-danger" role="alert">
                    <?= escape_html($error) ?>
                </div>
                <div class="mt-4 d-grid gap-2">
                    <a href="resend-verification.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Resend Verification Email
                    </a>
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

