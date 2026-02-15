<?php
/**
 * Course Listing Page
 * Displays all available courses to students and visitors
 */

require_once '../config.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

// Build filters array
$filters = [
    'limit' => $per_page,
    'offset' => ($page - 1) * $per_page
];

if ($category_id) {
    $filters['category_id'] = $category_id;
}

if ($search) {
    $filters['search'] = $search;
}

// Get courses
$courses = get_courses($filters);

// Get categories for filter
$categories = get_categories();

// Get featured courses
$featured_courses = get_courses(['featured' => true, 'limit' => 6]);

// Pagination
$total_courses = count($courses);
$total_pages = ceil($total_courses / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .courses-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .courses-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .courses-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .search-box {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .search-box button {
            padding: 12px 30px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .search-box button:hover {
            background: #357abd;
        }
        
        .filters-section {
            margin-bottom: 30px;
        }
        
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        
        .category-filter {
            padding: 8px 20px;
            border: 2px solid #ddd;
            border-radius: 20px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }
        
        .category-filter:hover,
        .category-filter.active {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .course-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .course-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .course-content {
            padding: 20px;
        }
        
        .course-category {
            font-size: 12px;
            color: #4a90e2;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .course-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .course-title a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s;
        }
        
        .course-title a:hover {
            color: #4a90e2;
        }
        
        .course-instructor {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .course-instructor img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .course-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #27ae60;
        }
        
        .course-price.free {
            color: #4a90e2;
        }
        
        .course-stats {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #888;
        }
        
        .course-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .featured-section {
            margin-bottom: 50px;
        }
        
        .featured-section h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #333;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .pagination a,
        .pagination span {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        
        .pagination .active {
            background: #4a90e2;
            color: white;
            border-color: #4a90e2;
        }
        
        .no-courses {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .no-courses i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .no-courses h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="courses-container">
            <div class="courses-header">
                <h1>Explore Our Courses</h1>
                <p>Discover thousands of courses from expert instructors</p>
                
                <form class="search-box" method="GET" action="">
                    <input type="text" name="search" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
            
            <!-- Category Filters -->
            <div class="filters-section">
                <div class="category-filters">
                    <a href="index.php" class="category-filter <?php echo !$category_id ? 'active' : ''; ?>">All Courses</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="index.php?category=<?php echo $category['id']; ?>" 
                           class="category-filter <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Featured Courses -->
            <?php if ($page == 1 && !empty($featured_courses) && empty($search) && !$category_id): ?>
                <div class="featured-section">
                    <h2><i class="fas fa-star"></i> Featured Courses</h2>
                    <div class="courses-grid">
                        <?php foreach ($featured_courses as $course): ?>
                            <div class="course-card">
                                <?php if ($course['thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumbnail">
                                <?php else: ?>
                                    <div class="course-thumbnail"></div>
                                <?php endif; ?>
                                <div class="course-content">
                                    <?php if ($course['category_name']): ?>
                                        <div class="course-category"><?php echo htmlspecialchars($course['category_name']); ?></div>
                                    <?php endif; ?>
                                    <h3 class="course-title">
                                        <a href="view.php?id=<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                    </h3>
                                    <div class="course-instructor">
                                        <img src="<?php echo get_avatar_url($course); ?>" alt="<?php echo htmlspecialchars($course['first_name']); ?>">
                                        <span><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                                    </div>
                                    <div class="course-meta">
                                        <div class="course-price <?php echo $course['price'] == 0 ? 'free' : ''; ?>">
                                            <?php echo $course['price'] == 0 ? 'Free' : format_currency($course['price'], $course['currency']); ?>
                                        </div>
                                        <div class="course-stats">
                                            <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?></span>
                                            <?php if ($course['duration_hours']): ?>
                                                <span><i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?>h</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- All Courses -->
            <div class="all-courses">
                <h2>
                    <?php if ($category_id): ?>
                        <?php echo htmlspecialchars(get_category($category_id)['name']); ?> Courses
                    <?php elseif ($search): ?>
                        Search Results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        All Courses
                    <?php endif; ?>
                </h2>
                
                <?php if (!empty($courses)): ?>
                    <div class="courses-grid">
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <?php if ($course['thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumbnail">
                                <?php else: ?>
                                    <div class="course-thumbnail"></div>
                                <?php endif; ?>
                                <div class="course-content">
                                    <?php if ($course['category_name']): ?>
                                        <div class="course-category"><?php echo htmlspecialchars($course['category_name']); ?></div>
                                    <?php endif; ?>
                                    <h3 class="course-title">
                                        <a href="view.php?id=<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                    </h3>
                                    <div class="course-instructor">
                                        <img src="<?php echo get_avatar_url($course); ?>" alt="<?php echo htmlspecialchars($course['first_name']); ?>">
                                        <span><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></span>
                                    </div>
                                    <div class="course-meta">
                                        <div class="course-price <?php echo $course['price'] == 0 ? 'free' : ''; ?>">
                                            <?php echo $course['price'] == 0 ? 'Free' : format_currency($course['price'], $course['currency']); ?>
                                        </div>
                                        <div class="course-stats">
                                            <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?></span>
                                            <?php if ($course['duration_hours']): ?>
                                                <span><i class="fas fa-clock"></i> <?php echo $course['duration_hours']; ?>h</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-courses">
                        <i class="fas fa-book-open"></i>
                        <h3>No courses found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
