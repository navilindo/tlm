<?php
/**
 * Security Functions
 * CSRF protection, XSS prevention, input sanitization, and security utilities
 */

/**
 * Initialize CSRF protection
 */
function initialize_csrf() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        initialize_csrf();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token for forms
 */
function csrf_token_field() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generate_csrf_token() . '">';
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function validate_password($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Generate secure random string
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password securely
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in (wrapper function)
 */
function is_user_logged_in() {
    return is_logged_in();
}

/**
 * Check if user has specific role
 */
function has_role($role) {
    return is_user_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Check if user has any of the specified roles
 */
function has_any_role($roles) {
    if (!is_user_logged_in() || !isset($_SESSION['user_role'])) {
        return false;
    }
    return in_array($_SESSION['user_role'], (array) $roles);
}

/**
 * Require login
 */
function require_login() {
    if (!is_user_logged_in()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require specific role
 */
function require_role($role) {
    require_login();
    if (!has_role($role)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Require any of the specified roles
 */
function require_any_role($roles) {
    require_login();
    if (!has_any_role($roles)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

/**
 * Rate limiting check
 */
function check_rate_limit($identifier, $max_attempts = 5, $time_window = 900) { // 15 minutes
    $key = 'rate_limit_' . md5($identifier);
    $attempts = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;
    $last_attempt = isset($_SESSION[$key . '_time']) ? $_SESSION[$key . '_time'] : 0;
    
    $current_time = time();
    
    // Reset if time window has passed
    if ($current_time - $last_attempt > $time_window) {
        $_SESSION[$key] = 0;
        $_SESSION[$key . '_time'] = $current_time;
        return true;
    }
    
    // Check if limit exceeded
    if ($attempts >= $max_attempts) {
        return false;
    }
    
    // Increment attempts
    $_SESSION[$key] = $attempts + 1;
    $_SESSION[$key . '_time'] = $current_time;
    
    return true;
}

/**
 * Generate secure filename
 */
function generate_secure_filename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $basename = pathinfo($original_name, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    return $basename . '_' . uniqid() . '.' . $extension;
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = null, $max_size = null) {
    $errors = [];
    
    if ($allowed_types === null) {
        $allowed_types = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES, ALLOWED_VIDEO_TYPES);
    }
    
    if ($max_size === null) {
        $max_size = MAX_FILE_SIZE;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error: " . $file['error'];
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = "File size exceeds maximum allowed size of " . format_bytes($max_size);
    }
    
    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_types)) {
        $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowed_types);
    }
    
    // Additional security check for images
    if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $errors[] = "Invalid image file";
        }
    }
    
    return $errors;
}

/**
 * Format bytes to human readable format
 */
function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Escape HTML for output
 */
function escape_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate secure random password
 */
function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
}

/**
 * Check if current request is POST
 */
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if current request is GET
 */
function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Log security event
 */
function log_security_event($action, $details = '') {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $db = getDB();
    $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $db->execute($sql, [$user_id, $action, $details, $ip_address, $user_agent]);
}

/**
 * Validate required fields
 */
function validate_required($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "Field '{$field}' is required";
        }
    }
    return $errors;
}

/**
 * Validate numeric fields
 */
function validate_numeric($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (isset($data[$field]) && $data[$field] !== '' && !is_numeric($data[$field])) {
            $errors[] = "Field '{$field}' must be numeric";
        }
    }
    return $errors;
}

/**
 * Validate URL
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Clean filename for display
 */
function clean_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
}

/**
 * Generate unique slug
 */
function generate_unique_slug($title, $table, $column = 'slug', $exclude_id = null) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $db = getDB();
    $sql = "SELECT {$column} FROM {$table} WHERE {$column} LIKE ?";
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $existing = $db->fetchAll($sql, [$slug . '%', $exclude_id]);
    } else {
        $existing = $db->fetchAll($sql, [$slug . '%']);
    }
    
    $existing_slugs = array_column($existing, $column);
    
    if (!in_array($slug, $existing_slugs)) {
        return $slug;
    }
    
    $counter = 1;
    while (in_array($slug . '-' . $counter, $existing_slugs)) {
        $counter++;
    }
    
    return $slug . '-' . $counter;
}
?>
