<?php
/**
 * Student Dashboard
 * Student homepage with enrolled courses and progress
 */

require_once '../config.php';
require_role(ROLE_STUDENT);

$user = get_current_user();
$enrollments = get_user_enrollments($user['id']);
$recent_announcements = get_recent_announcements($user['id']);
$stats = get_student_stats($user['id']);

// Handle flash messages
$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?= SITE_NAME ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body data-page="dashboard">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                <?= SITE_NAME ?>
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
                        <a class="nav-link" href="my-courses.php">
                            <i class="fas fa-book me-1"></i>My Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../courses/index.php">
                            <i class="fas fa-search me-1"></i>Browse Courses
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
                    <small class="text-muted">Student</small>
                </div>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a class="nav-link" href="my-courses.php">
                <i class="fas fa-book"></i>My Courses
            </a>
            <a class="nav-link" href="../courses/index.php">
                <i class="fas fa-search"></i>Browse Courses
            </a>
            <a class="nav-link" href="assignments.php">
                <i class="fas fa-tasks"></i>Assignments
            </a>
            <a class="nav-link" href="grades.php">
                <i class="fas fa-chart-line"></i>Grades & Progress
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
                <h1 class="h3 mb-0">Welcome back, <?= escape_html($user['first_name']) ?>!</h1>
                <p class="text-muted mb-0">Here's what's happening with your learning journey.</p>
            </div>
            <div>
                <a href="../courses/index.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Find New Courses
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
                                <div class="stats-number"><?= $stats['enrolled_courses'] ?></div>
                                <div class="stats-label">Enrolled Courses</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $stats['completed_lessons'] ?></div>
                                <div class="stats-label">Lessons Completed</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $stats['average_progress'] ?>%</div>
                                <div class="stats-label">Average Progress</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= $stats['pending_assignments'] ?></div>
                                <div class="stats-label">Pending Assignments</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- My Courses -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i>Continue Learning
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($enrollments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <h5>No courses enrolled yet</h5>
                                <p class="text-muted mb-3">Start your learning journey by enrolling in a course.</p>
                                <a href="../courses/index.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Browse Courses
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach (array_slice($enrollments, 0, 6) as $enrollment): ?>
                                    <div class="col-lg-6">
                                        <div class="card course-card h-100">
                                            <?php if ($enrollment['thumbnail']): ?>
                                                <img src="<?= escape_html($enrollment['thumbnail']) ?>" class="course-thumbnail" alt="<?= escape_html($enrollment['title']) ?>">
                                            <?php else: ?>
                                                <div class="course-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-play-circle fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h6 class="card-title"><?= escape_html($enrollment['title']) ?></h6>
                                                <p class="card-text text-muted small">
                                                    by <?= escape_html($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?>
                                                </p>
                                                
                                                <div class="progress mb-2" data-progress="<?= (int)$enrollment['progress_percentage'] ?>">
                                                    <div class="progress-bar" role="progressbar" style="width: <?= $enrollment['progress_percentage'] ?>%"></div>
                                                </div>
                                                <small class="text-muted"><?= number_format($enrollment['progress_percentage'], 1) ?>% complete</small>
                                                
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-play-circle me-1"></i>
                                                        <?= $enrollment['completed_lessons'] ?> / <?= $enrollment['total_lessons'] ?> lessons
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="card-footer bg-transparent">
                                                <a href="../courses/view.php?slug=<?= escape_html($enrollment['slug']) ?>" class="btn btn-primary btn-sm w-100">
                                                    <i class="fas fa-play me-2"></i>Continue Learning
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (count($enrollments) > 6): ?>
                                <div class="text-center mt-4">
                                    <a href="my-courses.php" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>View All Courses
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="col-lg-4">
                <!-- Recent Announcements -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bullhorn me-2"></i>Recent Announcements
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_announcements)): ?>
                            <p class="text-muted small mb-0">No recent announcements</p>
                        <?php else: ?>
                            <?php foreach (array_slice($recent_announcements, 0, 5) as $announcement): ?>
                                <div class="mb-3">
                                    <h6 class="mb-1">
                                        <a href="../courses/announcements.php?id=<?= $announcement['id'] ?>" class="text-decoration-none">
                                            <?= escape_html($announcement['title']) ?>
                                        </a>
                                    </h6>
                                    <p class="text-muted small mb-1">
                                        <?= truncate_text($announcement['content'], 80) ?>
                                    </p>
                                    <small class="text-muted">
                                        <?= format_date($announcement['created_at'], 'M j, Y') ?>
                                    </small>
                                </div>
                                <?php if (!$loop->last): ?><hr><?php endif; ?>
                            <?php endforeach; ?>
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
                            <a href="../courses/index.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-search me-2"></i>Browse Courses
                            </a>
                            <a href="my-courses.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-book me-2"></i>My Courses
                            </a>
                            <a href="assignments.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-tasks me-2"></i>Assignments
                            </a>
                            <a href="grades.php" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-chart-line me-2"></i>View Grades
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
