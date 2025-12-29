<?php
/**
 * Utility Functions
 * Common functions used throughout the LMS
 */

/**
 * Get all categories
 */
function get_categories() {
    $db = getDB();
    return $db->fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY sort_order, name");
}

/**
 * Get category by ID
 */
function get_category($id) {
    $db = getDB();
    return $db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
}

/**
 * Get courses with optional filters
 */
function get_courses($filters = []) {
    $db = getDB();
    
    $where = ["c.is_published = TRUE", "c.approval_status = ?"];
    $params = [COURSE_STATUS_APPROVED];
    
    if (!empty($filters['category_id'])) {
        $where[] = "c.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['instructor_id'])) {
        $where[] = "c.instructor_id = ?";
        $params[] = $filters['instructor_id'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(c.title LIKE ? OR c.description LIKE ? OR c.tags LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (isset($filters['featured'])) {
        $where[] = "c.featured = ?";
        $params[] = $filters['featured'];
    }
    
    $order_by = "ORDER BY c.featured DESC, c.created_at DESC";
    
    if (!empty($filters['limit'])) {
        $order_by .= " LIMIT ?";
        $params[] = (int) $filters['limit'];
    }
    
    $sql = "SELECT c.*, u.first_name, u.last_name, u.avatar, cat.name as category_name, 
                   COUNT(e.id) as enrolled_count
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'active'
            WHERE " . implode(' AND ', $where) . "
            GROUP BY c.id
            {$order_by}";
    
    return $db->fetchAll($sql, $params);
}

/**
 * Get single course by ID or slug
 */
function get_course($identifier, $by = 'id') {
    $db = getDB();
    
    $column = ($by === 'slug') ? 'slug' : 'id';
    $sql = "SELECT c.*, u.first_name, u.last_name, u.avatar, cat.name as category_name
            FROM courses c
            LEFT JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            WHERE c.{$column} = ?";
    
    return $db->fetch($sql, [$identifier]);
}

/**
 * Get course modules and lessons
 */
function get_course_content($course_id) {
    $db = getDB();
    
    $sql = "SELECT m.*, 
                   GROUP_CONCAT(l.id ORDER BY l.sort_order) as lesson_ids
            FROM modules m
            LEFT JOIN lessons l ON m.id = l.module_id AND l.is_published = TRUE
            WHERE m.course_id = ? AND m.is_published = TRUE
            GROUP BY m.id
            ORDER BY m.sort_order";
    
    $modules = $db->fetchAll($sql, [$course_id]);
    
    // Get lessons for each module
    foreach ($modules as &$module) {
        if ($module['lesson_ids']) {
            $lesson_ids = explode(',', $module['lesson_ids']);
            $placeholders = str_repeat('?,', count($lesson_ids) - 1) . '?';
            $sql = "SELECT * FROM lessons WHERE id IN ({$placeholders}) ORDER BY sort_order";
            $module['lessons'] = $db->fetchAll($sql, $lesson_ids);
        } else {
            $module['lessons'] = [];
        }
        unset($module['lesson_ids']);
    }
    
    return $modules;
}

/**
 * Get lesson by ID
 */
function get_lesson($lesson_id) {
    $db = getDB();
    
    $sql = "SELECT l.*, c.title as course_title, c.slug as course_slug,
                   m.title as module_title
            FROM lessons l
            JOIN courses c ON l.course_id = c.id
            JOIN modules m ON l.module_id = m.id
            WHERE l.id = ?";
    
    return $db->fetch($sql, [$lesson_id]);
}

/**
 * Enroll user in course
 */
function enroll_in_course($user_id, $course_id) {
    $db = getDB();
    
    // Check if already enrolled
    $existing = $db->fetch("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", 
        [$user_id, $course_id]);
    if ($existing) {
        throw new Exception("Already enrolled in this course");
    }
    
    // Check if course allows enrollment
    $course = get_course($course_id);
    if (!$course || $course['enrollment_type'] !== ENROLLMENT_PUBLIC) {
        throw new Exception("Course enrollment not allowed");
    }
    
    // Check max students limit
    if ($course['max_students']) {
        $enrolled_count = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ? AND status = 'active'", 
            [$course_id])['count'];
        if ($enrolled_count >= $course['max_students']) {
            throw new Exception("Course is full");
        }
    }
    
    // Create enrollment
    $sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
    $enrollment_id = $db->insert($sql, [$user_id, $course_id]);
    
    // Log activity
    log_security_event('course_enrolled', "User {$user_id} enrolled in course {$course_id}");
    
    return $enrollment_id;
}

/**
 * Get user enrollments
 */
function get_user_enrollments($user_id) {
    $db = getDB();
    
    $sql = "SELECT e.*, c.title, c.slug, c.short_description, c.thumbnail, c.duration_hours,
                   u.first_name, u.last_name, u.avatar,
                   cat.name as category_name,
                   COUNT(DISTINCT l.id) as total_lessons,
                   COUNT(DISTINCT lp.lesson_id) as completed_lessons
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            JOIN users u ON c.instructor_id = u.id
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN lessons l ON c.id = l.course_id AND l.is_published = TRUE
            LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = e.user_id
            WHERE e.user_id = ? AND e.status = 'active'
            GROUP BY e.id
            ORDER BY e.enrollment_date DESC";
    
    return $db->fetchAll($sql, [$user_id]);
}

/**
 * Get course enrollment count
 */
function get_course_enrollment_count($course_id) {
    $db = getDB();
    
    $result = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ? AND status = 'active'", 
        [$course_id]);
    return $result['count'];
}

/**
 * Mark lesson as completed
 */
function mark_lesson_completed($user_id, $lesson_id) {
    $db = getDB();
    
    $sql = "INSERT INTO lesson_progress (user_id, lesson_id) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE completed_at = CURRENT_TIMESTAMP";
    $db->execute($sql, [$user_id, $lesson_id]);
    
    // Update course progress
    update_course_progress($user_id, get_lesson($lesson_id)['course_id']);
}

/**
 * Update course progress
 */
function update_course_progress($user_id, $course_id) {
    $db = getDB();
    
    // Get total lessons and completed lessons
    $sql = "SELECT 
                (SELECT COUNT(*) FROM lessons WHERE course_id = ? AND is_published = TRUE) as total_lessons,
                (SELECT COUNT(*) FROM lesson_progress lp 
                 JOIN lessons l ON lp.lesson_id = l.id 
                 WHERE l.course_id = ? AND lp.user_id = ?) as completed_lessons";
    
    $result = $db->fetch($sql, [$course_id, $course_id, $user_id]);
    
    $total = $result['total_lessons'];
    $completed = $result['completed_lessons'];
    
    $percentage = ($total > 0) ? round(($completed / $total) * 100, 2) : 0;
    
    // Update enrollment progress
    $sql = "UPDATE enrollments SET progress_percentage = ? WHERE user_id = ? AND course_id = ?";
    $db->execute($sql, [$percentage, $user_id, $course_id]);
    
    // Mark as completed if 100%
    if ($percentage >= 100) {
        $sql = "UPDATE enrollments SET status = 'completed', completion_date = NOW() 
                WHERE user_id = ? AND course_id = ?";
        $db->execute($sql, [$user_id, $course_id]);
    }
}

/**
 * Get user lesson progress
 */
function get_user_lesson_progress($user_id, $course_id) {
    $db = getDB();
    
    $sql = "SELECT lp.lesson_id, lp.completed_at, l.title, l.sort_order
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = ? AND l.course_id = ?
            ORDER BY l.sort_order";
    
    return $db->fetchAll($sql, [$user_id, $course_id]);
}

/**
 * Create new course
 */
function create_course($instructor_id, $data) {
    $db = getDB();
    
    // Generate unique slug
    $slug = generate_unique_slug($data['title'], 'courses');
    
    // Insert course
    $sql = "INSERT INTO courses (title, slug, description, short_description, instructor_id, 
                                 category_id, enrollment_type, max_students, price, currency, 
                                 duration_hours, difficulty_level, language, tags, prerequisites, 
                                 learning_objectives) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $course_id = $db->insert($sql, [
        $data['title'],
        $slug,
        $data['description'],
        $data['short_description'] ?? null,
        $instructor_id,
        $data['category_id'] ?? null,
        $data['enrollment_type'] ?? ENROLLMENT_PUBLIC,
        $data['max_students'] ?? null,
        $data['price'] ?? 0,
        $data['currency'] ?? 'USD',
        $data['duration_hours'] ?? null,
        $data['difficulty_level'] ?? 'beginner',
        $data['language'] ?? 'en',
        $data['tags'] ?? null,
        $data['prerequisites'] ?? null,
        $data['learning_objectives'] ?? null
    ]);
    
    // Create default module
    create_module($course_id, 'Introduction', 'Course introduction and overview', 1);
    
    log_security_event('course_created', "Course created: {$data['title']} (ID: {$course_id})");
    
    return $course_id;
}

/**
 * Create course module
 */
function create_module($course_id, $title, $description = null, $sort_order = 1) {
    $db = getDB();
    
    $sql = "INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, ?, ?, ?)";
    return $db->insert($sql, [$course_id, $title, $description, $sort_order]);
}

/**
 * Create lesson
 */
function create_lesson($module_id, $course_id, $data) {
    $db = getDB();
    
    // Get next sort order
    $max_order = $db->fetch("SELECT MAX(sort_order) as max_order FROM lessons WHERE module_id = ?", [$module_id]);
    $sort_order = ($max_order['max_order'] ?? 0) + 1;
    
    $sql = "INSERT INTO lessons (module_id, course_id, title, content, content_type, 
                                 video_url, video_duration, pdf_file, external_link, 
                                 sort_order, estimated_duration) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $lesson_id = $db->insert($sql, [
        $module_id,
        $course_id,
        $data['title'],
        $data['content'] ?? null,
        $data['content_type'],
        $data['video_url'] ?? null,
        $data['video_duration'] ?? null,
        $data['pdf_file'] ?? null,
        $data['external_link'] ?? null,
        $sort_order,
        $data['estimated_duration'] ?? null
    ]);
    
    return $lesson_id;
}

/**
 * Upload file
 */
function upload_file($file, $directory = 'general') {
    // Validate file
    $errors = validate_file_upload($file);
    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }
    
    // Generate secure filename
    $filename = generate_secure_filename($file['name']);
    $upload_path = UPLOAD_PATH . $directory . '/' . $filename;
    
    // Create directory if not exists
    $dir = dirname($upload_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Failed to upload file");
    }
    
    return $directory . '/' . $filename;
}

/**
 * Format date for display
 */
function format_date($date, $format = null) {
    if (!$format) {
        $format = DISPLAY_DATE_FORMAT;
    }
    
    if (empty($date)) {
        return '';
    }
    
    return date($format, strtotime($date));
}

/**
 * Format currency
 */
function format_currency($amount, $currency = 'USD') {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥'
    ];
    
    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . number_format($amount, 2);
}

/**
 * Get user avatar URL
 */
function get_avatar_url($user) {
    if (!empty($user['avatar'])) {
        return SITE_URL . '/' . $user['avatar'];
    }
    
    // Generate Gravatar URL
    $email = md5(strtolower(trim($user['email'])));
    return "https://www.gravatar.com/avatar/{$email}?d=identicon&s=150";
}

/**
 * Truncate text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate pagination
 */
function generate_pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$base_url}?page={$prev_page}'>Previous</a></li>";
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= "<li class='page-item {$active}'><a class='page-link' href='{$base_url}?page={$i}'>{$i}</a></li>";
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $html .= "<li class='page-item'><a class='page-link' href='{$base_url}?page={$next_page}'>Next</a></li>";
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Send notification email (placeholder)
 */
function send_notification_email($to, $subject, $message, $type = 'general') {
    // TODO: Implement email sending
    // For now, just log the email
    error_log("Email notification - To: {$to}, Subject: {$subject}, Type: {$type}");
    
    // Add to email queue
    $db = getDB();
    $sql = "INSERT INTO email_queue (recipient_email, subject, message, type) VALUES (?, ?, ?, ?)";
    $db->execute($sql, [$to, $subject, $message, $type]);
    
    return true;
}

/**
 * Get system statistics
 */
function get_system_stats() {
    $db = getDB();
    
    $stats = [];
    
    // Total users
    $stats['total_users'] = $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'];
    
    // Total courses
    $stats['total_courses'] = $db->fetch("SELECT COUNT(*) as count FROM courses WHERE is_published = TRUE")['count'];
    
    // Total enrollments
    $stats['total_enrollments'] = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE status = 'active'")['count'];
    
    // Active courses this month
    $stats['active_courses_month'] = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE enrollment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")['count'];
    
    return $stats;
}

/**
 * Clean up old data
 */
function cleanup_old_data() {
    $db = getDB();
    
    // Clean up old activity logs (older than 1 year)
    $db->execute("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
    
    // Clean up old email queue entries
    $db->execute("DELETE FROM email_queue WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) AND status = 'sent'");
    
    // Clean up expired password reset tokens
    $db->execute("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE reset_expires < NOW()");
}
?>
