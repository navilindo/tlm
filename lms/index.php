<?php
/**
 * Main Homepage
 * Landing page and course catalog
 */

require_once 'config.php';

// Redirect logged-in users to their dashboard
if (is_logged_in()) {
    if (has_role(ROLE_ADMIN)) {
        header('Location: admin/dashboard.php');
    } elseif (has_role(ROLE_INSTRUCTOR)) {
        header('Location: instructor/dashboard.php');
    } elseif (has_role(ROLE_STUDENT)) {
        header('Location: student/dashboard.php');
    }
    exit;
}

$featured_courses = get_courses(['featured' => true, 'limit' => 6]);
$recent_courses = get_courses(['limit' => 8]);
$categories = get_categories();
$stats = get_system_stats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= SITE_NAME ?> - Online Learning Platform</title>
    <meta name="description" content="<?= SITE_DESCRIPTION ?> - Learn new skills with expert-led courses">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                <?= SITE_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#courses">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Sign In
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary ms-2 px-3" href="auth/register.php">
                            <i class="fas fa-user-plus me-1"></i>Sign Up
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Learn Without Limits
                    </h1>
                    <p class="lead mb-4">
                        Discover thousands of courses from expert instructors. 
                        Build skills, advance your career, and achieve your goals.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="auth/register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Get Started Free
                        </a>
                        <a href="#courses" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play me-2"></i>Browse Courses
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-laptop-code fa-10x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stats-card bg-primary text-white rounded p-4">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <div class="stats-number"><?= number_format($stats['total_users']) ?></div>
                        <div class="stats-label">Active Learners</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card bg-success text-white rounded p-4">
                        <i class="fas fa-book fa-3x mb-3"></i>
                        <div class="stats-number"><?= number_format($stats['total_courses']) ?></div>
                        <div class="stats-label">Courses Available</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card bg-info text-white rounded p-4">
                        <i class="fas fa-certificate fa-3x mb-3"></i>
                        <div class="stats-number"><?= number_format($stats['total_enrollments']) ?></div>
                        <div class="stats-label">Certificates Earned</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card bg-warning text-dark rounded p-4">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <div class="stats-number"><?= number_format($stats['active_courses_month']) ?></div>
                        <div class="stats-label">New Enrollments This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses -->
    <section id="courses" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold">Featured Courses</h2>
                    <p class="lead text-muted">Discover our most popular courses taught by industry experts</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($featured_courses as $course): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card course-card h-100">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?= escape_html($course['thumbnail']) ?>" class="course-thumbnail" alt="<?= escape_html($course['title']) ?>">
                            <?php else: ?>
                                <div class="course-thumbnail bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-play-circle fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?= get_avatar_url($course) ?>" class="avatar me-2" alt="<?= escape_html($course['first_name'] . ' ' . $course['last_name']) ?>">
                                    <small class="text-muted">
                                        <?= escape_html($course['first_name'] . ' ' . $course['last_name']) ?>
                                    </small>
                                </div>
                                
                                <h5 class="card-title"><?= escape_html($course['title']) ?></h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?= truncate_text($course['short_description'] ?: $course['description'], 100) ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="course-price">
                                        <?php if ($course['price'] > 0): ?>
                                            <?= format_currency($course['price'], $course['currency']) ?>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">Free</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <?= $course['enrolled_count'] ?> enrolled
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <a href="courses/view.php?slug=<?= escape_html($course['slug']) ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>View Course
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($featured_courses)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h4>No courses available yet</h4>
                    <p class="text-muted">Courses will appear here once instructors start creating content.</p>
                    <a href="auth/register.php" class="btn btn-primary">Become an Instructor</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold">Course Categories</h2>
                    <p class="lead text-muted">Explore courses by subject area</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="fas fa-folder fa-3x text-primary mb-3"></i>
                                <h5 class="card-title"><?= escape_html($category['name']) ?></h5>
                                <p class="card-text text-muted">
                                    <?= truncate_text($category['description'], 80) ?>
                                </p>
                                <a href="courses/index.php?category=<?= $category['id'] ?>" class="btn btn-outline-primary">
                                    Browse Courses
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h4>No categories available</h4>
                    <p class="text-muted">Categories will be created as courses are added.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recent Courses -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold">Latest Courses</h2>
                    <p class="lead text-muted">Recently added courses from our instructors</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($recent_courses as $course): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card course-card h-100">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?= escape_html($course['thumbnail']) ?>" class="course-thumbnail" alt="<?= escape_html($course['title']) ?>">
                            <?php else: ?>
                                <div class="course-thumbnail bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-play-circle fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h6 class="card-title"><?= escape_html($course['title']) ?></h6>
                                <p class="card-text text-muted small">
                                    by <?= escape_html($course['first_name'] . ' ' . $course['last_name']) ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="course-price small">
                                        <?php if ($course['price'] > 0): ?>
                                            <?= format_currency($course['price'], $course['currency']) ?>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">Free</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= $course['enrolled_count'] ?> students
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="courses/index.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-th me-2"></i>View All Courses
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">Why Choose Our Platform?</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-chalkboard-teacher fa-2x text-primary me-3 mt-1"></i>
                                <div>
                                    <h5>Expert Instructors</h5>
                                    <p class="text-muted">Learn from industry professionals with real-world experience.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-certificate fa-2x text-success me-3 mt-1"></i>
                                <div>
                                    <h5>Certificates</h5>
                                    <p class="text-muted">Earn certificates upon course completion to showcase your skills.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-clock fa-2x text-info me-3 mt-1"></i>
                                <div>
                                    <h5>Learn at Your Pace</h5>
                                    <p class="text-muted">Access courses anytime, anywhere, and learn at your own speed.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-users fa-2x text-warning me-3 mt-1"></i>
                                <div>
                                    <h5>Community</h5>
                                    <p class="text-muted">Connect with fellow learners and instructors in discussions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-graduation-cap fa-10x text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-4">Ready to Start Learning?</h2>
                    <p class="lead mb-4">
                        Join thousands of learners who are already advancing their careers with our courses.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="auth/register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Sign Up Free
                        </a>
                        <a href="auth/login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><i class="fas fa-graduation-cap me-2"></i><?= SITE_NAME ?></h5>
                    <p class="text-muted"><?= SITE_DESCRIPTION ?></p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Platform</h6>
                    <ul class="list-unstyled">
                        <li><a href="courses/index.php" class="text-muted">Browse Courses</a></li>
                        <li><a href="#" class="text-muted">Become Instructor</a></li>
                        <li><a href="#" class="text-muted">Pricing</a></li>
                        <li><a href="#" class="text-muted">Help Center</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">About Us</a></li>
                        <li><a href="#" class="text-muted">Contact</a></li>
                        <li><a href="#" class="text-muted">Blog</a></li>
                        <li><a href="#" class="text-muted">Careers</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted">Terms of Service</a></li>
                        <li><a href="#" class="text-muted">Cookie Policy</a></li>
                        <li><a href="#" class="text-muted">DMCA</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6>Connect</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">Newsletter</a></li>
                        <li><a href="#" class="text-muted">Support</a></li>
                        <li><a href="#" class="text-muted">Community</a></li>
                        <li><a href="#" class="text-muted">Mobile App</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        Made with <i class="fas fa-heart text-danger"></i> for learners worldwide
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
    
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add animation to stats on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.stats-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>
