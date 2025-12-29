<?php
/**
 * Admin Dashboard
 * Administrative interface for system management
 */

require_once '../config.php';
require_role(ROLE_ADMIN);

$stats = get_system_stats();
$recent_users = get_recent_users();
$recent_courses = get_recent_courses();
$pending_courses = get_pending_courses();
$system_activity = get_recent_activity();

// Handle flash messages
$flash = get_flash_message();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body data-page="admin-dashboard">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>
                Admin Panel
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
                        <a class="nav-link" href="manage-users.php">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-courses.php">
                            <i class="fas fa-book me-1"></i>Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="system-config.php">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-2"></i>
                            Administrator
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-home me-2"></i>View Site</a></li>
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
                <i class="fas fa-user-shield fa-2x text-warning me-3"></i>
                <div>
                    <h6 class="mb-0">Administrator</h6>
                    <small class="text-muted">System Control</small>
                </div>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a class="nav-link" href="manage-users.php">
                <i class="fas fa-users"></i>User Management
            </a>
            <a class="nav-link" href="manage-courses.php">
                <i class="fas fa-book"></i>Course Management
            </a>
            <a class="nav-link" href="system-config.php">
                <i class="fas fa-cog"></i>System Settings
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i>Reports & Analytics
            </a>
            <a class="nav-link" href="backup.php">
                <i class="fas fa-database"></i>Database Backup
            </a>
            <a class="nav-link" href="activity-log.php">
                <i class="fas fa-history"></i>Activity Log
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Admin Dashboard</h1>
                <p class="text-muted mb-0">System overview and management tools</p>
            </div>
            <div>
                <span class="badge bg-success">
                    <i class="fas fa-circle me-1"></i>System Online
                </span>
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
                                <div class="stats-number"><?= number_format($stats['total_users']) ?></div>
                                <div class="stats-label">Total Users</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            +<?= $stats['new_users_today'] ?> today
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= number_format($stats['total_courses']) ?></div>
                                <div class="stats-label">Total Courses</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            <?= $stats['pending_courses'] ?> pending approval
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= number_format($stats['total_enrollments']) ?></div>
                                <div class="stats-label">Total Enrollments</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            <?= $stats['enrollments_this_month'] ?> this month
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="stats-number"><?= number_format($stats['active_students']) ?></div>
                                <div class="stats-label">Active Students</div>
                            </div>
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <small class="opacity-75">
                            Last 30 days
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Users -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Recent Users
                        </h5>
                        <a href="manage-users.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_users)): ?>
                            <p class="text-muted mb-0">No recent users</p>
                        <?php else: ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?= get_avatar_url($user) ?>" class="avatar me-3" alt="User">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= escape_html($user['first_name'] . ' ' . $user['last_name']) ?></h6>
                                        <small class="text-muted"><?= escape_html($user['email']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'instructor' ? 'info' : 'success') ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= format_date($user['created_at'], 'M j') ?></small>
                                    </div>
                                </div>
                                <?php if (!$loop->last): ?><hr class="my-2"><?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pending Course Approvals -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Pending Approvals
                        </h5>
                        <a href="manage-courses.php" class="btn btn-sm btn-outline-warning">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_courses)): ?>
                            <p class="text-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>All courses approved
                            </p>
                        <?php else: ?>
                            <?php foreach ($pending_courses as $course): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= escape_html($course['title']) ?></h6>
                                        <small class="text-muted">
                                            by <?= escape_html($course['first_name'] . ' ' . $course['last_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-sm btn-success me-1" onclick="approveCourse(<?= $course['id'] ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectCourse(<?= $course['id'] ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <br>
                                        <small class="text-muted"><?= format_date($course['created_at'], 'M j') ?></small>
                                    </div>
                                </div>
                                <?php if (!$loop->last): ?><hr class="my-2"><?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Activity -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($system_activity)): ?>
                            <p class="text-muted mb-0">No recent activity</p>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($system_activity as $activity): ?>
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="bg-light rounded-circle p-2">
                                                <i class="fas fa-<?= get_activity_icon($activity['action']) ?> text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?= escape_html($activity['action']) ?></h6>
                                            <p class="text-muted small mb-1">
                                                <?= escape_html($activity['details']) ?>
                                            </p>
                                            <small class="text-muted">
                                                <?= format_date($activity['created_at'], 'M j, Y g:i A') ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php if (!$loop->last): ?><hr class="my-2"><?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="manage-users.php?action=add" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-2"></i>Add User
                            </a>
                            <a href="manage-courses.php?action=add" class="btn btn-success btn-sm">
                                <i class="fas fa-book-plus me-2"></i>Create Course
                            </a>
                            <a href="system-config.php" class="btn btn-info btn-sm">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </a>
                            <a href="backup.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-download me-2"></i>Backup Database
                            </a>
                            <a href="../courses/index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye me-2"></i>View Site
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-server me-2"></i>System Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-hdd text-primary fa-lg"></i>
                                    <div class="small mt-1">Storage</div>
                                    <div class="fw-bold"><?= get_disk_usage() ?>%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <i class="fas fa-memory text-success fa-lg"></i>
                                    <div class="small mt-1">Memory</div>
                                    <div class="fw-bold"><?= get_memory_usage() ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        function approveCourse(courseId) {
            if (confirm('Are you sure you want to approve this course?')) {
                // AJAX call to approve course
                fetch('manage-courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=approve&id=${courseId}&csrf_token=${document.querySelector('[name="csrf_token"]').value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        LMS.showAlert(data.message, 'danger');
                    }
                });
            }
        }

        function rejectCourse(courseId) {
            const reason = prompt('Enter reason for rejection (optional):');
            if (confirm('Are you sure you want to reject this course?')) {
                fetch('manage-courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reject&id=${courseId}&reason=${encodeURIComponent(reason)}&csrf_token=${document.querySelector('[name="csrf_token"]').value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        LMS.showAlert(data.message, 'danger');
                    }
                });
            }
        }

        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
