<?php
/**
 * Course Creation Page
 * Allows instructors to create new courses
 */

require_once '../config.php';

// Check if user is logged in and is an instructor
if (!is_logged_in() || !has_role(ROLE_INSTRUCTOR)) {
    header('Location: ../auth/login.php?redirect=courses/create.php');
    exit;
}

$error = '';
$success = '';

// Get categories for dropdown
$categories = get_categories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Prepare course data
            $course_data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'category_id' => (int)$_POST['category_id'],
                'enrollment_type' => $_POST['enrollment_type'],
                'difficulty_level' => $_POST['difficulty_level'],
                'max_students' => !empty($_POST['max_students']) ? (int)$_POST['max_students'] : null,
                'price' => !empty($_POST['price']) ? (float)$_POST['price'] : 0,
                'currency' => $_POST['currency'] ?? 'USD',
                'duration_hours' => !empty($_POST['duration_hours']) ? (int)$_POST['duration_hours'] : null,
                'language' => $_POST['language'] ?? 'en',
                'tags' => trim($_POST['tags'] ?? ''),
                'prerequisites' => trim($_POST['prerequisites'] ?? ''),
                'learning_objectives' => trim($_POST['learning_objectives'] ?? '')
            ];
            
            try {
                $course_id = create_course($_SESSION['user_id'], $course_data);
                $success = 'Course created successfully! You can now add modules and lessons.';
                
                // Redirect to edit page
                header('Location: edit.php?id=' . $course_id);
                exit;
            } catch (Exception $e) {
                $error = 'Error creating course: ' . $e->getMessage();
            }
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
    <title>Create Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .create-course-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group label .required {
            color: #e74c3c;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group .help-text {
            font-size: 13px;
            color: #888;
            margin-top: 5px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #4a90e2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357abd;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="create-course-container">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Create New Course</h1>
                <p>Fill in the details to create a new course. You can add modules and lessons after creating the course.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <!-- Basic Information -->
                <div class="form-card">
                    <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                    
                    <div class="form-group">
                        <label>Course Title <span class="required">*</span></label>
                        <input type="text" name="title" required placeholder="e.g., Introduction to Web Development">
                        <p class="help-text">Choose a clear, descriptive title for your course.</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Short Description</label>
                        <input type="text" name="short_description" placeholder="A brief summary of your course (max 500 characters)">
                        <p class="help-text">This will appear in course cards and search results.</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Description <span class="required">*</span></label>
                        <textarea name="description" required placeholder="Describe what students will learn in this course..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Difficulty Level <span class="required">*</span></label>
                            <select name="difficulty_level" required>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tags</label>
                        <input type="text" name="tags" placeholder="e.g., html, css, javascript, web development">
                        <p class="help-text">Separate tags with commas.</p>
                    </div>
                </div>
                
                <!-- Course Settings -->
                <div class="form-card">
                    <h2><i class="fas fa-cog"></i> Course Settings</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Enrollment Type <span class="required">*</span></label>
                            <select name="enrollment_type" required>
                                <option value="public">Public - Anyone can enroll</option>
                                <option value="private">Private - Invite only</option>
                                <option value="invite_only">Invite Only</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Maximum Students</label>
                            <input type="number" name="max_students" min="1" placeholder="Leave empty for unlimited">
                        </div>
                    </div>
                    
                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Price (<?php echo $_POST['currency'] ?? 'USD'; ?>)</label>
                            <input type="number" name="price" min="0" step="0.01" value="0" placeholder="0 for free">
                        </div>
                        
                        <div class="form-group">
                            <label>Currency</label>
                            <select name="currency">
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="JPY">JPY (¥)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Duration (Hours)</label>
                            <input type="number" name="duration_hours" min="1" placeholder="e.g., 10">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Language</label>
                        <select name="language">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                        </select>
                    </div>
                </div>
                
                <!-- Additional Details -->
                <div class="form-card">
                    <h2><i class="fas fa-book-open"></i> Additional Details</h2>
                    
                    <div class="form-group">
                        <label>Prerequisites</label>
                        <textarea name="prerequisites" placeholder="What knowledge or experience is required before taking this course?"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Learning Objectives</label>
                        <textarea name="learning_objectives" placeholder="What will students be able to do after completing this course?"></textarea>
                        <p class="help-text">List the key skills and knowledge students will gain.</p>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="manage.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Course
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
