<?php
/**
 * Course Editing Page
 * Allows instructors to edit their courses
 */

require_once '../config.php';

// Check if user is logged in and is an instructor
if (!is_logged_in() || !has_role(ROLE_INSTRUCTOR)) {
    header('Location: ../auth/login.php?redirect=courses/manage.php');
    exit;
}

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no course ID
if (!$course_id) {
    header('Location: manage.php');
    exit;
}

// Get course details
$course = get_course($course_id);

// Redirect if course not found or not owned by user
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    header('Location: manage.php');
    exit;
}

// Get categories for dropdown
$categories = get_categories();

// Get course modules
$modules = get_course_content($course_id);

$error = '';
$success = '';

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Validate required fields
        $required_fields = ['title', 'description', 'category_id', 'enrollment_type', 'difficulty_level'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $error = 'Please fill in all required fields: ' . implode(', ', $missing_fields);
        } else {
            $db = getDB();
            
            // Update course
            $sql = "UPDATE courses SET 
                        title = ?,
                        description = ?,
                        short_description = ?,
                        category_id = ?,
                        enrollment_type = ?,
                        difficulty_level = ?,
                        max_students = ?,
                        price = ?,
                        currency = ?,
                        duration_hours = ?,
                        language = ?,
                        tags = ?,
                        prerequisites = ?,
                        learning_objectives = ?
                    WHERE id = ? AND instructor_id = ?";
            
            try {
                $db->execute($sql, [
                    trim($_POST['title']),
                    trim($_POST['description']),
                    trim($_POST['short_description'] ?? ''),
                    (int)$_POST['category_id'],
                    $_POST['enrollment_type'],
                    $_POST['difficulty_level'],
                    !empty($_POST['max_students']) ? (int)$_POST['max_students'] : null,
                    !empty($_POST['price']) ? (float)$_POST['price'] : 0,
                    $_POST['currency'] ?? 'USD',
                    !empty($_POST['duration_hours']) ? (int)$_POST['duration_hours'] : null,
                    $_POST['language'] ?? 'en',
                    trim($_POST['tags'] ?? ''),
                    trim($_POST['prerequisites'] ?? ''),
                    trim($_POST['learning_objectives'] ?? ''),
                    $course_id,
                    $_SESSION['user_id']
                ]);
                
                $success = 'Course updated successfully!';
                
                // Refresh course data
                $course = get_course($course_id);
            } catch (Exception $e) {
                $error = 'Error updating course: ' . $e->getMessage();
            }
        }
    }
}

// Handle module creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else if (!empty($_POST['module_title'])) {
        try {
            $db = getDB();
            // Get next sort order
            $max_order = $db->fetch("SELECT MAX(sort_order) as max_order FROM modules WHERE course_id = ?", [$course_id]);
            $sort_order = ($max_order['max_order'] ?? 0) + 1;
            
            $sql = "INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, ?, ?, ?)";
            $db->insert($sql, [$course_id, trim($_POST['module_title']), trim($_POST['module_description'] ?? ''), $sort_order]);
            
            $success = 'Module added successfully!';
            
            // Refresh modules
            $modules = get_course_content($course_id);
        } catch (Exception $e) {
            $error = 'Error adding module: ' . $e->getMessage();
        }
    }
}

// Handle lesson creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lesson'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else if (!empty($_POST['lesson_title']) && !empty($_POST['module_id'])) {
        try {
            $db = getDB();
            
            // Get next sort order
            $max_order = $db->fetch("SELECT MAX(sort_order) as max_order FROM lessons WHERE module_id = ?", [(int)$_POST['module_id']]);
            $sort_order = ($max_order['max_order'] ?? 0) + 1;
            
            $sql = "INSERT INTO lessons (module_id, course_id, title, content, content_type, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
            $db->insert($sql, [
                (int)$_POST['module_id'],
                $course_id,
                trim($_POST['lesson_title']),
                trim($_POST['lesson_content'] ?? ''),
                $_POST['lesson_type'] ?? 'text',
                $sort_order
            ]);
            
            $success = 'Lesson added successfully!';
            
            // Refresh modules
            $modules = get_course_content($course_id);
        } catch (Exception $e) {
            $error = 'Error adding lesson: ' . $e->getMessage();
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - <?php echo htmlspecialchars($course['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-course-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: #333;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #4a90e2; color: white; }
        .btn-primary:hover { background: #357abd; }
        
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #219a52; }
        
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-card h2 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group label .required { color: #e74c3c; }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .form-group .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        
        .modules-section { margin-top: 30px; }
        
        .module-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .module-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        
        .lesson-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .lesson-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .lesson-item:last-child { border-bottom: none; }
        
        .lesson-info { display: flex; align-items: center; gap: 10px; }
        
        .lesson-type-badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
            background: #e9ecef;
            color: #666;
        }
        
        .add-forms {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .add-forms h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: #e9ecef;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
        }
        
        .tab-btn.active {
            background: #4a90e2;
            color: white;
        }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="edit-course-container">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Course</h1>
                <div class="header-actions">
                    <a href="view.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> View Course
                    </a>
                    <a href="manage.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Manage
                    </a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="content-grid">
                <div class="main-content">
                    <!-- Course Details Form -->
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-card">
                            <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                            
                            <div class="form-group">
                                <label>Course Title <span class="required">*</span></label>
                                <input type="text" name="title" required value="<?php echo htmlspecialchars($course['title']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Short Description</label>
                                <input type="text" name="short_description" value="<?php echo htmlspecialchars($course['short_description'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Full Description <span class="required">*</span></label>
                                <textarea name="description" required rows="5"><?php echo htmlspecialchars($course['description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Category <span class="required">*</span></label>
                                    <select name="category_id" required>
                                        <option value="">Select category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $course['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Difficulty Level <span class="required">*</span></label>
                                    <select name="difficulty_level" required>
                                        <option value="beginner" <?php echo $course['difficulty_level'] == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="intermediate" <?php echo $course['difficulty_level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="advanced" <?php echo $course['difficulty_level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Tags</label>
                                <input type="text" name="tags" value="<?php echo htmlspecialchars($course['tags'] ?? ''); ?>" placeholder="Separate with commas">
                            </div>
                        </div>
                        
                        <div class="form-card">
                            <h2><i class="fas fa-cog"></i> Course Settings</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Enrollment Type <span class="required">*</span></label>
                                    <select name="enrollment_type" required>
                                        <option value="public" <?php echo $course['enrollment_type'] == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="private" <?php echo $course['enrollment_type'] == 'private' ? 'selected' : ''; ?>>Private</option>
                                        <option value="invite_only" <?php echo $course['enrollment_type'] == 'invite_only' ? 'selected' : ''; ?>>Invite Only</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Max Students</label>
                                    <input type="number" name="max_students" min="1" value="<?php echo $course['max_students'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" name="price" min="0" step="0.01" value="<?php echo $course['price']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select name="currency">
                                        <option value="USD" <?php echo $course['currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
                                        <option value="EUR" <?php echo $course['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                        <option value="GBP" <?php echo $course['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Duration (Hours)</label>
                                    <input type="number" name="duration_hours" min="1" value="<?php echo $course['duration_hours'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Language</label>
                                <select name="language">
                                    <option value="en" <?php echo $course['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="es" <?php echo $course['language'] == 'es' ? 'selected' : ''; ?>>Spanish</option>
                                    <option value="fr" <?php echo $course['language'] == 'fr' ? 'selected' : ''; ?>>French</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-card">
                            <h2><i class="fas fa-book-open"></i> Additional Details</h2>
                            
                            <div class="form-group">
                                <label>Prerequisites</label>
                                <textarea name="prerequisites" rows="3"><?php echo htmlspecialchars($course['prerequisites'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Learning Objectives</label>
                                <textarea name="learning_objectives" rows="3"><?php echo htmlspecialchars($course['learning_objectives'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_course" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    
                    <!-- Course Content (Modules & Lessons) -->
                    <div class="modules-section">
                        <div class="form-card">
                            <h2><i class="fas fa-list"></i> Course Content</h2>
                            
                            <?php if (!empty($modules)): ?>
                                <?php foreach ($modules as $module_index => $module): ?>
                                    <div class="module-card">
                                        <div class="module-header">
                                            <span class="module-title">
                                                Module <?php echo $module_index + 1; ?>: <?php echo htmlspecialchars($module['title']); ?>
                                            </span>
                                            <div>
                                                <button class="btn btn-sm btn-primary" onclick="showAddLesson(<?php echo $module['id']; ?>)">
                                                    <i class="fas fa-plus"></i> Add Lesson
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($module['lessons'])): ?>
                                            <ul class="lesson-list">
                                                <?php foreach ($module['lessons'] as $lesson): ?>
                                                    <li class="lesson-item">
                                                        <div class="lesson-info">
                                                            <i class="fas fa-file-alt"></i>
                                                            <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                                            <span class="lesson-type-badge"><?php echo ucfirst($lesson['content_type']); ?></span>
                                                        </div>
                                                        <div>
                                                            <a href="../lessons/edit.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p style="color: #888; font-size: 14px;">No lessons yet. Click "Add Lesson" to add content.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #888; margin-bottom: 20px;">No modules yet.</p>
                            <?php endif; ?>
                            
                            <!-- Add Module Form -->
                            <div class="add-forms">
                                <h3>Add New Module</h3>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <div class="form-row">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <input type="text" name="module_title" placeholder="Module title" required>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <input type="text" name="module_description" placeholder="Description (optional)">
                                        </div>
                                        <button type="submit" name="add_module" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Add Module
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="sidebar">
                    <div class="form-card">
                        <h2>Course Status</h2>
                        
                        <div style="margin-bottom: 15px;">
                            <strong>Status:</strong> 
                            <?php if ($course['is_published']): ?>
                                <span style="color: #27ae60;"><i class="fas fa-check-circle"></i> Published</span>
                            <?php else: ?>
                                <span style="color: #f39c12;"><i class="fas fa-clock"></i> Draft</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <strong>Approval:</strong> 
                            <?php if ($course['approval_status'] == 'approved'): ?>
                                <span style="color: #27ae60;">Approved</span>
                            <?php elseif ($course['approval_status'] == 'pending'): ?>
                                <span style="color: #f39c12;">Pending</span>
                            <?php else: ?>
                                <span style="color: #e74c3c;">Rejected</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <strong>Enrolled:</strong> <?php echo get_course_enrollment_count($course_id); ?> students
                        </div>
                        
                        <hr style="margin: 20px 0;">
                        
                        <a href="view.php?id=<?php echo $course_id; ?>" class="btn btn-secondary" style="width: 100%; text-align: center; margin-bottom: 10px;">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        function showAddLesson(moduleId) {
            alert('Lesson editor will open for module: ' + moduleId);
        }
    </script>
</body>
</html>
