<?php
/**
 * Course Management Page
 * Allows instructors to manage their courses
 */

require_once '../config.php';

// Check if user is logged in and is an instructor
if (!is_logged_in() || !has_role(ROLE_INSTRUCTOR)) {
    header('Location: ../auth/login.php?redirect=courses/manage.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get instructor's courses
$courses = get_instructor_courses($user_id);

// Get instructor stats
$stats = get_instructor_stats($user_id);

// Handle course actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['course_id'])) {
        $course_id = (int)$_POST['course_id'];
        $action = $_POST['action'];
        
        // Verify course belongs to user
        $course = get_course($course_id);
        if ($course && $course['instructor_id'] == $user_id) {
            $db = getDB();
            
            try {
                switch ($action) {
                    case 'publish':
                        $db->execute("UPDATE courses SET is_published = TRUE WHERE id = ?", [$course_id]);
                        $message = 'Course published successfully!';
                        break;
                        
                    case 'unpublish':
                        $db->execute("UPDATE courses SET is_published = FALSE WHERE id = ?", [$course_id]);
                        $message = 'Course unpublished successfully!';
                        break;
                        
                    case 'delete':
                        // Check for enrollments
                        $enrollments = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?", [$course_id]);
                        if ($enrollments['count'] > 0) {
                            $error = 'Cannot delete course with active enrollments. Please unpublish instead.';
                        } else {
                            $db->execute("DELETE FROM courses WHERE id = ?", [$course_id]);
                            $message = 'Course deleted successfully!';
                        }
                        break;
                }
                
                // Refresh courses
                $courses = get_instructor_courses($user_id);
                $stats = get_instructor_stats($user_id);
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .manage-container {
            max-width: 1400px;
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        
        .stat-card .icon.blue { background: #e3f2fd; color: #4a90e2; }
        .stat-card .icon.green { background: #e8f5e9; color: #27ae60; }
        .stat-card .icon.orange { background: #fff3e0; color: #f39c12; }
        .stat-card .icon.purple { background: #f3e5f5; color: #9b59b6; }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        /* Courses Table */
        .courses-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header h2 {
            font-size: 1.3rem;
            color: #333;
            margin: 0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .course-title {
            font-weight: 600;
            color: #333;
        }
        
        .course-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .course-title a:hover {
            color: #4a90e2;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-pending {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .approval-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .approval-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .approval-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .approval-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            th, td {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="manage-container">
            <div class="page-header">
                <h1><i class="fas fa-chalkboard-teacher"></i> My Courses</h1>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Course
                </a>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon blue">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="value"><?php echo $stats['total_courses']; ?></div>
                    <div class="label">Total Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="value"><?php echo $stats['published_courses']; ?></div>
                    <div class="label">Published</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="value"><?php echo $stats['total_students']; ?></div>
                    <div class="label">Total Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon orange">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="value"><?php echo $stats['new_students_this_month']; ?></div>
                    <div class="label">New This Month</div>
                </div>
            </div>
            
            <!-- Courses Table -->
            <div class="courses-section">
                <div class="section-header">
                    <h2>All Courses</h2>
                    <span style="color: #666; font-size: 14px;">
                        <?php echo count($courses); ?> course(s)
                    </span>
                </div>
                
                <?php if (!empty($courses)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Students</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td>
                                            <div class="course-title">
                                                <a href="edit.php?id=<?php echo $course['id']; ?>">
                                                    <?php echo htmlspecialchars($course['title']); ?>
                                                </a>
                                            </div>
                                            <div style="font-size: 13px; color: #888; margin-top: 5px;">
                                                <?php echo $course['total_lessons']; ?> lessons
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $course['is_published'] ? 'status-published' : 'status-draft'; ?>">
                                                <?php echo $course['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="approval-badge approval-<?php echo $course['approval_status']; ?>">
                                                <?php echo ucfirst($course['approval_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $course['enrolled_students']; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $avg_progress = $course['average_progress'] ?? 0;
                                                echo round($avg_progress, 1) . '%';
                                            ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="view.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                
                                                <?php if (!$course['is_published']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                        <input type="hidden" name="action" value="publish">
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                onclick="return confirm('Are you sure you want to publish this course?');">
                                                            <i class="fas fa-upload"></i> Publish
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                        <input type="hidden" name="action" value="unpublish">
                                                        <button type="submit" class="btn btn-sm btn-secondary" 
                                                                onclick="return confirm('Are you sure you want to unpublish this course?');">
                                                            <i class="fas fa-download"></i> Unpublish
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($course['enrolled_students'] == 0): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No courses yet</h3>
                        <p>Start by creating your first course!</p>
                        <a href="create.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Create Course
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
