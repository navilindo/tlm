<?php
/**
 * Logout Script
 * Securely logs out users and redirects to login page
 */

require_once '../config.php';

// Log the logout activity
if (is_logged_in()) {
    $user_email = $_SESSION['user_email'] ?? 'unknown';
    log_security_event('logout', "User logged out: {$user_email}");
}

// Perform logout
logout_user();

// Clear remember cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page with success message
redirect_with_message('../auth/login.php', 'You have been successfully logged out.', 'success');
?>
