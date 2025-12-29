<?php
/**
 * Authentication Functions
 * User registration, login, logout, password reset, and session management
 */

/**
 * Register new user
 */
function register_user($email, $password, $first_name, $last_name, $role = ROLE_STUDENT) {
    $db = getDB();
    
    // Check if email already exists
    $existing_user = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing_user) {
        throw new Exception("Email address already registered");
    }
    
    // Validate password
    if (!validate_password($password)) {
        throw new Exception("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long");
    }
    
    // Generate verification token
    $verification_token = generate_secure_token(32);
    
    // Hash password
    $password_hash = hash_password($password);
    
    // Insert user
    $sql = "INSERT INTO users (email, password_hash, first_name, last_name, role, verification_token) VALUES (?, ?, ?, ?, ?, ?)";
    $user_id = $db->insert($sql, [$email, $password_hash, $first_name, $last_name, $role, $verification_token]);
    
    // Log activity
    log_security_event('user_registered', "User registered: {$email}");
    
    return $user_id;
}

/**
 * Verify user email
 */
function verify_user_email($token) {
    $db = getDB();
    
    $sql = "UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE verification_token = ?";
    $affected = $db->modify($sql, [$token]);
    
    if ($affected > 0) {
        log_security_event('email_verified', "Email verified with token: {$token}");
        return true;
    }
    
    return false;
}

/**
 * Login user
 */
function login_user($email, $password, $remember_me = false) {
    $db = getDB();
    
    // Rate limiting check
    $identifier = $email . '_login';
    if (!check_rate_limit($identifier, 5, 900)) { // 5 attempts per 15 minutes
        throw new Exception("Too many login attempts. Please try again later.");
    }
    
    // Get user
    $sql = "SELECT * FROM users WHERE email = ? AND status = ?";
    $user = $db->fetch($sql, [$email, USER_STATUS_ACTIVE]);
    
    if (!$user || !verify_password($password, $user['password_hash'])) {
        log_security_event('failed_login', "Failed login attempt for: {$email}");
        throw new Exception("Invalid email or password");
    }
    
    // Check if user is verified (if verification required)
    $setting = get_system_setting('email_verification_required');
    if ($setting === 'true' && !$user['is_verified']) {
        throw new Exception("Please verify your email address before logging in");
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['login_time'] = time();
    
    // Update last login
    $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
    
    // Handle remember me
    if ($remember_me) {
        $remember_token = generate_secure_token(32);
        $expires = time() + REMEMBER_ME_DURATION;
        
        // Store remember token
        $db->execute("UPDATE users SET remember_token = ? WHERE id = ?", [$remember_token, $user['id']]);
        
        // Set cookie
        setcookie('remember_token', $remember_token, $expires, '/', '', false, true);
    }
    
    // Log successful login
    log_security_event('successful_login', "User logged in: {$email}");
    
    return $user;
}

/**
 * Auto-login from remember token
 */
function auto_login_from_token($token) {
    $db = getDB();
    
    $sql = "SELECT * FROM users WHERE remember_token = ? AND status = ?";
    $user = $db->fetch($sql, [$token, USER_STATUS_ACTIVE]);
    
    if ($user && $user['is_verified']) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_first_name'] = $user['first_name'];
        $_SESSION['user_last_name'] = $user['last_name'];
        $_SESSION['login_time'] = time();
        
        // Update last login
        $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        
        log_security_event('auto_login', "User auto-logged in: {$user['email']}");
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout_user() {
    if (is_logged_in()) {
        $user_email = $_SESSION['user_email'] ?? 'unknown';
        
        // Clear remember token
        if (isset($_COOKIE['remember_token'])) {
            $db = getDB();
            $db->execute("UPDATE users SET remember_token = NULL WHERE id = ?", [$_SESSION['user_id']]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        log_security_event('logout', "User logged out: {$user_email}");
    }
}

/**
 * Check if user is logged in
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Get current user data
 */
if (!function_exists('get_current_user')) {
    function get_current_user() {
        if (!is_logged_in()) {
            return null;
        }
        
        $db = getDB();
        return $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
}

/**
 * Update user profile
 */
function update_user_profile($user_id, $data) {
    $db = getDB();
    
    $allowed_fields = ['first_name', 'last_name', 'bio', 'phone', 'avatar'];
    $updates = [];
    $params = [];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        throw new Exception("No valid fields to update");
    }
    
    $params[] = $user_id;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $affected = $db->modify($sql, $params);
    
    if ($affected > 0) {
        log_security_event('profile_updated', "Profile updated for user ID: {$user_id}");
        return true;
    }
    
    return false;
}

/**
 * Upload user avatar
 */
function upload_avatar($user_id, $file) {
    $db = getDB();
    
    // Validate file
    $errors = validate_file_upload($file, ALLOWED_IMAGE_TYPES, 2097152); // 2MB
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }
    
    // Generate secure filename
    $filename = generate_secure_filename($file['name']);
    $upload_path = UPLOAD_PATH . 'avatars/' . $filename;
    
    // Create directory if not exists
    $dir = dirname($upload_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Failed to upload file");
    }
    
    // Update user avatar
    $avatar_url = 'assets/uploads/avatars/' . $filename;
    $sql = "UPDATE users SET avatar = ? WHERE id = ?";
    $db->execute($sql, [$avatar_url, $user_id]);
    
    log_security_event('avatar_uploaded', "Avatar uploaded for user ID: {$user_id}");
    
    return $avatar_url;
}

/**
 * Request password reset
 */
function request_password_reset($email) {
    $db = getDB();
    
    $user = $db->fetch("SELECT id, email FROM users WHERE email = ?", [$email]);
    if (!$user) {
        // Don't reveal if email exists or not
        return true;
    }
    
    $reset_token = generate_secure_token(32);
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
    
    $sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?";
    $db->execute($sql, [$reset_token, $expires, $user['id']]);
    
    // TODO: Send email with reset link
    log_security_event('password_reset_requested', "Password reset requested for: {$email}");
    
    return $reset_token;
}

/**
 * Reset password with token
 */
function reset_password($token, $new_password) {
    $db = getDB();
    
    // Validate password
    if (!validate_password($new_password)) {
        throw new Exception("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long");
    }
    
    $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()";
    $user = $db->fetch($sql, [$token]);
    
    if (!$user) {
        throw new Exception("Invalid or expired reset token");
    }
    
    // Update password and clear reset token
    $password_hash = hash_password($new_password);
    $sql = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL, remember_token = NULL WHERE id = ?";
    $db->execute($sql, [$password_hash, $user['id']]);
    
    // Log activity
    log_security_event('password_reset', "Password reset completed for user ID: {$user['id']}");
    
    return true;
}

/**
 * Change password
 */
function change_password($user_id, $current_password, $new_password) {
    $db = getDB();
    
    // Get current password hash
    $user = $db->fetch("SELECT password_hash FROM users WHERE id = ?", [$user_id]);
    if (!$user || !verify_password($current_password, $user['password_hash'])) {
        throw new Exception("Current password is incorrect");
    }
    
    // Validate new password
    if (!validate_password($new_password)) {
        throw new Exception("Password must be at least " . PASSWORD_MIN_LENGTH . " characters long");
    }
    
    // Update password
    $password_hash = hash_password($new_password);
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    $db->execute($sql, [$password_hash, $user_id]);
    
    // Clear remember tokens
    $sql = "UPDATE users SET remember_token = NULL WHERE id = ?";
    $db->execute($sql, [$user_id]);
    
    // Clear remember cookie
    setcookie('remember_token', '', time() - 3600, '/');
    
    log_security_event('password_changed', "Password changed for user ID: {$user_id}");
    
    return true;
}

/**
 * Check session timeout
 */
function check_session_timeout() {
    if (!is_logged_in() || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    $current_time = time();
    $login_time = $_SESSION['login_time'];
    $timeout = SESSION_TIMEOUT;
    
    if ($current_time - $login_time > $timeout) {
        logout_user();
        return true; // Session expired
    }
    
    // Update login time if still active
    $_SESSION['login_time'] = $current_time;
    return false;
}

/**
 * Create user session data
 */
function create_user_session($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['login_time'] = time();
}

/**
 * Get user role display name
 */
function get_user_role_name($role) {
    $roles = [
        ROLE_ADMIN => 'Administrator',
        ROLE_INSTRUCTOR => 'Instructor',
        ROLE_STUDENT => 'Student'
    ];
    return $roles[$role] ?? 'Unknown';
}

/**
 * Check if user can access course
 */
function user_can_access_course($user_id, $course_id) {
    $db = getDB();
    
    // Check if user is instructor of the course
    $course = $db->fetch("SELECT instructor_id FROM courses WHERE id = ?", [$course_id]);
    if ($course && $course['instructor_id'] == $user_id) {
        return true;
    }
    
    // Check if user is enrolled
    $enrollment = $db->fetch("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = ?", 
        [$user_id, $course_id, 'active']);
    if ($enrollment) {
        return true;
    }
    
    // Check if course is public
    $course = $db->fetch("SELECT enrollment_type FROM courses WHERE id = ?", [$course_id]);
    if ($course && $course['enrollment_type'] === ENROLLMENT_PUBLIC) {
        return true;
    }
    
    return false;
}

/**
 * Get system setting
 */
function get_system_setting($key, $default = null) {
    $db = getDB();
    $setting = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
    return $setting ? $setting['setting_value'] : $default;
}

/**
 * Update system setting
 */
function update_system_setting($key, $value) {
    $db = getDB();
    $sql = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
    return $db->modify($sql, [$value, $key]);
}

// Initialize session timeout check
if (is_logged_in()) {
    check_session_timeout();
}
?>
