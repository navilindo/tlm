<?php
/**
 * LMS Configuration File
 * Main configuration settings for the E-Learning Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lms_db');
define('DB_USER', 'root');
define('DB_PASS', 'toor');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost:3000/lms');
define('SITE_NAME', 'LMS Platform');
define('SITE_DESCRIPTION', 'Online Learning Management System');

// Security Configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 7200); // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('REMEMBER_ME_DURATION', 2592000); // 30 days

// Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'wmv']);

// Email Configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@lms.com');
define('FROM_NAME', 'LMS Platform');

// System Settings
define('DEFAULT_TIMEZONE', 'UTC');
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M j, Y g:i A');

// Role Constants
define('ROLE_ADMIN', 'admin');
define('ROLE_INSTRUCTOR', 'instructor');
define('ROLE_STUDENT', 'student');

// Course Enrollment Types
define('ENROLLMENT_PUBLIC', 'public');
define('ENROLLMENT_PRIVATE', 'private');
define('ENROLLMENT_INVITE_ONLY', 'invite_only');

// Course Status
define('COURSE_STATUS_PENDING', 'pending');
define('COURSE_STATUS_APPROVED', 'approved');
define('COURSE_STATUS_REJECTED', 'rejected');

// User Status
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');

// Content Types
define('CONTENT_TYPE_TEXT', 'text');
define('CONTENT_TYPE_VIDEO', 'video');
define('CONTENT_TYPE_PDF', 'pdf');
define('CONTENT_TYPE_LINK', 'link');
define('CONTENT_TYPE_QUIZ', 'quiz');
define('CONTENT_TYPE_ASSIGNMENT', 'assignment');

// Question Types
define('QUESTION_MULTIPLE_CHOICE', 'multiple_choice');
define('QUESTION_TRUE_FALSE', 'true_false');
define('QUESTION_SHORT_ANSWER', 'short_answer');
define('QUESTION_ESSAY', 'essay');

// File Paths
define('INCLUDES_PATH', __DIR__ . '/includes/');
define('TEMPLATES_PATH', __DIR__ . '/templates/');
define('ASSETS_PATH', __DIR__ . '/../assets/');

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Include essential functions (order matters!)
require_once INCLUDES_PATH . 'database.php';
require_once INCLUDES_PATH . 'security.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'functions.php';

// Initialize CSRF protection
initialize_csrf();

// Auto-login from remember token
if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
    auto_login_from_token($_COOKIE['remember_token']);
}
?>
