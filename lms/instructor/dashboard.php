<?php
/**
 * Instructor Dashboard
 * Instructor interface for course management and student oversight
 */

require_once '../config.php';
require_role(ROLE_INSTRUCTOR);

$user = get_current_user();
$instructor_stats = get_instructor_stats($user['id']);
$my_courses = get_instructor_courses($user['id']);
$recent_students = get_recent_students($user['id']);
$pending_assignments = get_pending_assignments($user['id']);

// Handle flash messages
$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - <?= SITE_NAME ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body data-page="instructor-dashboard">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-info fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Instructor Panel
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-courses.php">
                            <i class="fas fa-book me-1"></i>My Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create-course.php">
                            <i class="fas fa-plus me-1"></i>Create Course
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-1"></i>Students
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= get_avatar_url($user) ?>" class="avatar me-2" alt="Profile">
                            <?= escape_html($user['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3">
            <div class="d-flex align-items-center">
                <img src="<?= get_avatar_url($user) ?>" class="avatar-lg me-3" alt="Profile">
                <div>
                    <h6 class="mb-0"><?= escape_html($user['first_name'] . ' ' . $user['last_name']) ?></h6>
                    <small class="text-muted">Instructor</small>
                </div>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a class="nav-link" href="manage-courses.php">
                <i class="fas fa-book"></i>My Courses
            </a>
            <a class="nav-link" href="create-course.php">
                <i class="fas fa-plus"></i>Create Course
            </a>
            <a class="nav-link" href="students.php">
                <i class="fas fa-users"></i>My Students
            </a>
            <a class="nav-link" href="gradebook.php">
                <i class="fas fa-chart-line"></i>Gradebook
            </a>
            <a class="nav-link" href="analytics.php">
                <i class="fas fa-chart-bar"></i>Analytics
            </a>
            <a class="nav-link" href="profile.php">
                <i class="fas fa-user"></i>Profile
            </a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-cog"></i>Settings
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Welcome, <?= escape_html($user['first_name']) ?>!</h1>
                <p class="text-muted mb-0">Manage your courses and track student progress</p>
            </div>
            <div>
                <a href="create-course.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Create New Course
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'info' ?> alert-dismissible fade show" role="alert">
                <?= escape_html($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $instructor_stats['total_courses'] ?></div>
                                <div class="stats-label">Total Courses</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            <?= $instructor_stats['published_courses'] ?> published
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $instructor_stats['total_students'] ?></div>
                                <div class="stats-label">Total Students</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            +<?= $instructor_stats['new_students_this_month'] ?> this month
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $instructor_stats['total_revenue'] ?></div>
                                <div class="stats-label">Total Revenue</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            $<?= number_format($instructor_stats['revenue_this_month'], 2) ?> this month
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $instructor_stats['completion_rate'] ?>%</div>
                                <div class="stats-label">Avg Completion</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            Course completion rate
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- My Courses -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i>My Courses
                        </h5>
                        <a href="manage-courses.php" class="btn btn-sm btn-outline-primary">Manage All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($my_courses)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <h5>No courses created yet</h5>
                                <p class="text-muted mb-3">Start sharing your knowledge by creating your first course.</p>
                                <a href="create-course.php" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Create Your First Course
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Students</th>
                                            <th>Progress</th>
                                            <th>Status</th>
                                            <th>Revenue</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($my_courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($course['thumbnail']): ?>
                                                            <img src="<?= escape_html($course['thumbnail']) ?>" class="avatar me-3" alt="Course">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded p-2 me-3">
                                                                <i class="fas fa-book text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?= escape_html($course['title']) ?></h6>
                                                            <small class="text-muted">
                                                                <?= $course['total_lessons'] ?> lessons
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $course['enrolled_students'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;" data-progress="<?= (int)$course['average_progress'] ?>">
                                                        <div class="progress-bar" role="progressbar" style="width: <?= $course['average_progress'] ?>%">
                                                            <?= number_format($course['average_progress'], 0) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($course['is_published']): ?>
                                                        <span class="badge bg-success">Published</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Draft</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($course['approval_status'] === 'pending'): ?>
                                                        <span class="badge bg-info">Pending Approval</span>
                                                    <?php elseif ($course['approval_status'] === 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($course['price'] > 0): ?>
                                                        $<?= number_format($course['enrolled_students'] * $course['price'], 2) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Free</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../courses/view.php?slug=<?= escape_html($course['slug']) ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View Course">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-course.php?id=<?= $course['id'] ?>" 
                                                           class="btn btn-outline-secondary" 
                                                           title="Edit Course">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="students.php?course_id=<?= $course['id'] ?>" 
                                                           class="btn btn-outline-info" 
                                                           title="View Students">
                                                            <i class="fas fa-users"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="col-lg-4">
                <!-- Recent Students -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user-graduate me-2"></i>Recent Enrollments
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_students)): ?>
                            <p class="text-muted small mb-0">No recent enrollments</p>
                        <?php else: ?>
                            <?php foreach (array_slice($recent_students, 0, 5) as $student): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?= get_avatar_url($student) ?>" class="avatar me-3" alt="Student">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= escape_html($student['first_name'] . ' ' . $student['last_name']) ?></h6>
                                        <small class="text-muted">
                                            <?= escape_html($student['course_title']) ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?= format_date($student['enrollment_date'], 'M j') ?>
                                    </small>
                                </div>
                                <?php if (!$loop->last): ?><hr class="my-2"><?php endif; ?>
                            <?php endforeach; ?>
                            
                            <div class="text-center mt-3">
                                <a href="students.php" class="btn btn-sm btn-outline-primary">View All Students</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Assignments -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i>Pending Reviews
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_assignments)): ?>
                            <p class="text-success small mb-0">
                                <i class="fas fa-check-circle me-2"></i>All assignments reviewed
                            </p>
                        <?php else: ?>
                            <?php foreach (array_slice($pending_assignments, 0, 3) as $assignment): ?>
                                <div class="mb-3">
                                    <h6 class="mb-1"><?= escape_html($assignment['assignment_title']) ?></h6>
                                    <small class="text-muted">
                                        <?= escape_html($assignment['student_name']) ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <?= format_date($assignment['submitted_at'], 'M j, Y') ?>
                                    </small>
                                    <div class="mt-1">
                                        <a href="grade-assignment.php?id=<?= $assignment['submission_id'] ?>" 
                                           class="btn btn-sm btn-warning">
                                            Review
                                        </a>
                                    </div>
                                </div>
                                <?php if (!$loop->last): ?><hr class="my-2"><?php endif; ?>
                            <?php endforeach; ?>
                            
                            <?php if (count($pending_assignments) > 3): ?>
                                <div class="text-center mt-3">
                                    <a href="gradebook.php" class="btn btn-sm btn-outline-warning">
                                        View All Pending
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="create-course.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i>Create Course
                            </a>
                            <a href="manage-courses.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-2"></i>Edit Courses
                            </a>
                            <a href="students.php" class="btn btn-info btn-sm">
                                <i class="fas fa-users me-2"></i>View Students
                            </a>
                            <a href="gradebook.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-chart-line me-2"></i>Gradebook
                            </a>
                            <a href="analytics.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/auth.js"></script>
</body>
</html>
