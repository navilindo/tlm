<?php
/**
 * Course Detail Page
 * Displays detailed course information and allows enrollment
 */

require_once '../config.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no course ID
if (!$course_id) {
    header('Location: index.php');
    exit;
}

// Get course details
$course = get_course($course_id);

// Redirect if course not found
if (!$course) {
    header('Location: index.php');
    exit;
}

// Get course content (modules and lessons)
$modules = get_course_content($course_id);

// Get categories for display
$categories = get_categories();

// Check if user is enrolled
$is_enrolled = false;
$enrollment = null;
if (is_logged_in()) {
    $db = getDB();
    $enrollment = $db->fetch("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", 
        [$_SESSION['user_id'], $course_id]);
    $is_enrolled = $enrollment !== false;
}

// Handle enrollment
$enrollment_error = '';
$enrollment_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    if (!is_logged_in()) {
        header('Location: ../auth/login.php?redirect=courses/view.php?id=' . $course_id);
        exit;
    }
    
    try {
        enroll_in_course($_SESSION['user_id'], $course_id);
        $enrollment_success = 'Successfully enrolled in the course!';
        $is_enrolled = true;
        $enrollment = $db->fetch("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", 
            [$_SESSION['user_id'], $course_id]);
    } catch (Exception $e) {
        $enrollment_error = $e->getMessage();
    }
}

// Get user progress if enrolled
$lesson_progress = [];
if ($is_enrolled) {
    $lesson_progress = get_user_lesson_progress($_SESSION['user_id'], $course_id);
    $completed_lessons = array_column($lesson_progress, 'lesson_id');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .course-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .course-header-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
        }
        
        .course-info h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .course-info .category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .course-info .description {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .course-info .meta {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .course-info .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .course-card-enroll {
            background: white;
            border-radius: 10px;
            padding: 30px;
            color: #333;
            text-align: center;
        }
        
        .course-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: #27ae60;
            margin-bottom: 5px;
        }
        
        .course-price.free {
            color: #4a90e2;
        }
        
        .course-price small {
            font-size: 1rem;
            color: #888;
        }
        
        .enroll-btn {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 15px;
        }
        
        .enroll-btn:hover {
            background: #219a52;
        }
        
        .enroll-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .enrolled-badge {
            background: #27ae60;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .progress-info {
            text-align: left;
            margin-top: 15px;
        }
        
        .progress-bar {
            height: 10px;
            background: #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        
        .progress-text {
            font-size: 14px;
            color: #666;
        }
        
        .continue-btn {
            width: 100%;
            padding: 15px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 15px;
            text-decoration: none;
            display: block;
        }
        
        .continue-btn:hover {
            background: #357abd;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .course-content-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .main-content h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .content-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .syllabus-module {
            margin-bottom: 20px;
        }
        
        .module-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }
        
        .module-header:hover {
            background: #e9ecef;
        }
        
        .module-title {
            font-weight: 600;
            color: #333;
        }
        
        .module-meta {
            font-size: 13px;
            color: #888;
        }
        
        .lesson-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .lesson-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .lesson-item:last-child {
            border-bottom: none;
        }
        
        .lesson-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
        }
        
        .lesson-icon.completed {
            color: #27ae60;
        }
        
        .lesson-title {
            flex: 1;
            color: #333;
        }
        
        .lesson-title a {
            text-decoration: none;
            color: inherit;
        }
        
        .lesson-title a:hover {
            color: #4a90e2;
        }
        
        .lesson-duration {
            font-size: 13px;
            color: #888;
        }
        
        .lesson-badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
            background: #e9ecef;
            color: #666;
        }
        
        .lesson-badge.free {
            background: #d4edda;
            color: #155724;
        }
        
        .sidebar {
            position: sticky;
            top: 20px;
        }
        
        .instructor-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .instructor-card h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .instructor-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .instructor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .instructor-name {
            font-weight: 600;
            color: #333;
        }
        
        .instructor-bio {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .course-details-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .course-details-card h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #666;
        }
        
        .detail-value {
            font-weight: 600;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .course-header-content {
                grid-template-columns: 1fr;
            }
            
            .course-content-section {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: static;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="course-detail-container">
            <!-- Course Header -->
            <div class="course-header">
                <div class="course-header-content">
                    <div class="course-info">
                        <?php if ($course['category_name']): ?>
                            <span class="category"><?php echo htmlspecialchars($course['category_name']); ?></span>
                        <?php endif; ?>
                        <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                        <p class="description"><?php echo htmlspecialchars($course['short_description'] ?? $course['description']); ?></p>
                        <div class="meta">
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $course['duration_hours'] ? $course['duration_hours'] . ' hours' : 'Self-paced'; ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-signal"></i>
                                <span><?php echo ucfirst($course['difficulty_level']); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-users"></i>
                                <span><?php echo get_course_enrollment_count($course_id); ?> enrolled</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-language"></i>
                                <span><?php echo strtoupper($course['language']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card-enroll">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" style="width:100%; border-radius:8px; margin-bottom:20px;">
                        <?php endif; ?>
                        
                        <div class="course-price <?php echo $course['price'] == 0 ? 'free' : ''; ?>">
                            <?php echo $course['price'] == 0 ? 'Free' : format_currency($course['price'], $course['currency']); ?>
                            <?php if ($course['price'] > 0): ?>
                                <small>/course</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($enrollment_success): ?>
                            <div class="alert alert-success"><?php echo $enrollment_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($enrollment_error): ?>
                            <div class="alert alert-error"><?php echo htmlspecialchars($enrollment_error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($is_enrolled): ?>
                            <div class="enrolled-badge">
                                <i class="fas fa-check-circle"></i> Enrolled
                            </div>
                            
                            <?php if ($enrollment): ?>
                                <div class="progress-info">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $enrollment['progress_percentage']; ?>%"></div>
                                    </div>
                                    <div class="progress-text">
                                        <?php echo $enrollment['progress_percentage']; ?>% complete
                                    </div>
                                </div>
                                <a href="../lessons/view.php?course=<?php echo $course_id; ?>" class="continue-btn">
                                    <i class="fas fa-play"></i> Continue Learning
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <form method="POST">
                                <button type="submit" name="enroll" class="enroll-btn">
                                    <i class="fas fa-user-plus"></i> Enroll Now
                                </button>
                            </form>
                            <p style="font-size: 12px; color: #888;">
                                <?php echo $course['max_students'] ? 'Limited to ' . $course['max_students'] . ' students' : 'Unlimited access'; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Course Content -->
            <div class="course-content-section">
                <div class="main-content">
                    <!-- Description -->
                    <div class="content-card">
                        <h2>About This Course</h2>
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                        
                        <?php if ($course['prerequisites']): ?>
                            <h3 style="margin-top: 20px;">Prerequisites</h3>
                            <p><?php echo nl2br(htmlspecialchars($course['prerequisites'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($course['learning_objectives']): ?>
                            <h3 style="margin-top: 20px;">What You'll Learn</h3>
                            <p><?php echo nl2br(htmlspecialchars($course['learning_objectives'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Course Curriculum -->
                    <div class="content-card">
                        <h2>Course Curriculum</h2>
                        <?php if (!empty($modules)): ?>
                            <?php foreach ($modules as $module_index => $module): ?>
                                <div class="syllabus-module">
                                    <div class="module-header">
                                        <span class="module-title">
                                            <i class="fas fa-chevron-down"></i>
                                            Module <?php echo $module_index + 1; ?>: <?php echo htmlspecialchars($module['title']); ?>
                                        </span>
                                        <span class="module-meta">
                                            <?php echo count($module['lessons']); ?> lessons
                                        </span>
                                    </div>
                                    <ul class="lesson-list">
                                        <?php foreach ($module['lessons'] as $lesson): ?>
                                            <li class="lesson-item">
                                                <div class="lesson-icon <?php echo $is_enrolled && in_array($lesson['id'], $completed_lessons ?? []) ? 'completed' : ''; ?>">
                                                    <?php if ($is_enrolled && in_array($lesson['id'], $completed_lessons ?? [])): ?>
                                                        <i class="fas fa-check-circle"></i>
                                                    <?php elseif ($is_enrolled): ?>
                                                        <i class="fas fa-play-circle"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-lock"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="lesson-title">
                                                    <?php if ($is_enrolled): ?>
                                                        <a href="../lessons/view.php?lesson=<?php echo $lesson['id']; ?>">
                                                            <?php echo htmlspecialchars($lesson['title']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($lesson['title']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="lesson-badge <?php echo $lesson['is_free'] ? 'free' : ''; ?>">
                                                    <?php echo ucfirst($lesson['content_type']); ?>
                                                </span>
                                                <?php if ($lesson['estimated_duration']): ?>
                                                    <span class="lesson-duration"><?php echo $lesson['estimated_duration']; ?> min</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Course content is not available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Instructor -->
                    <div class="instructor-card">
                        <h3>Instructor</h3>
                        <div class="instructor-info">
                            <img src="<?php echo get_avatar_url($course); ?>" alt="<?php echo htmlspecialchars($course['first_name']); ?>" class="instructor-avatar">
                            <div>
                                <div class="instructor-name"><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></div>
                            </div>
                        </div>
                        <?php if ($course['avatar']): ?>
                            <p class="instructor-bio"><?php echo htmlspecialchars($course['avatar']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Course Details -->
                    <div class="course-details-card">
                        <h3>Course Details</h3>
                        <div class="detail-item">
                            <span class="detail-label">Level</span>
                            <span class="detail-value"><?php echo ucfirst($course['difficulty_level']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value"><?php echo $course['duration_hours'] ? $course['duration_hours'] . ' hours' : 'Self-paced'; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Language</span>
                            <span class="detail-value"><?php echo strtoupper($course['language']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Enrollment</span>
                            <span class="detail-value"><?php echo ucfirst($course['enrollment_type']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Students</span>
                            <span class="detail-value"><?php echo get_course_enrollment_count($course_id); ?></span>
                        </div>
                        <?php if ($course['tags']): ?>
                            <div class="detail-item" style="display: block;">
                                <span class="detail-label">Tags</span>
                                <div style="margin-top: 8px;">
                                    <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                        <span class="lesson-badge"><?php echo trim($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
