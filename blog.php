<?php
require_once 'includes/config.php';
$page_title = 'Blog';
$page_description = 'Stay updated with the latest news, tips, and insights about technology in education.';

// Get current page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$posts_per_page = 6;
$offset = ($current_page - 1) * $posts_per_page;

// Get category filter
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get blog posts
$posts = [];
$total_posts = 0;

try {
    // Build query
    $sql = "
        SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
        FROM blog_posts p
        LEFT JOIN users u ON p.author_id = u.id
        LEFT JOIN blog_categories c ON p.category_id = c.id
        WHERE p.status = 'published'
    ";
    
    $params = [];
    
    // Add category filter
    if ($category_id > 0) {
        $sql .= " AND p.category_id = :category_id";
        $params['category_id'] = $category_id;
    }
    
    // Add search filter
    if (!empty($search_query)) {
        $sql .= " AND (p.title LIKE :search OR p.content LIKE :search OR p.excerpt LIKE :search)";
        $params['search'] = '%' . $search_query . '%';
    }
    
    // Count total posts
    $count_sql = str_replace("SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug", "SELECT COUNT(*) as total", $sql);
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    $total_posts = $result['total'];
    
    // Get paginated posts
    $sql .= " ORDER BY p.created_at DESC LIMIT :offset, :limit";
    $stmt = $db->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
    
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching blog posts: " . $e->getMessage());
}

// Get categories for sidebar
$categories = [];
try {
    $stmt = $db->prepare("
        SELECT c.*, COUNT(p.id) as post_count
        FROM blog_categories c
        LEFT JOIN blog_posts p ON c.id = p.category_id AND p.status = 'published'
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching blog categories: " . $e->getMessage());
}

// Get recent posts for sidebar
$recent_posts = [];
try {
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.slug, p.created_at, p.featured_image
        FROM blog_posts p
        WHERE p.status = 'published'
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching recent posts: " . $e->getMessage());
}

// Calculate pagination
$total_pages = ceil($total_posts / $posts_per_page);
$prev_page = ($current_page > 1) ? $current_page - 1 : null;
$next_page = ($current_page < $total_pages) ? $current_page + 1 : null;

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <h1 class="fw-bold">Blog</h1>
                <p class="lead">Stay updated with the latest news, tips, and insights about technology in education</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <!-- Search and Filter Bar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="get" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search blog..." name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="category" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?> (<?php echo $category['post_count']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <?php if (!empty($search_query) || $category_id > 0): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-outline-secondary w-100">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($search_query) || $category_id > 0): ?>
                    <div class="alert alert-info mb-4">
                        <?php if (!empty($search_query)): ?>
                            <span class="me-2">Search results for: <strong><?php echo htmlspecialchars($search_query); ?></strong></span>
                        <?php endif; ?>
                        
                        <?php if ($category_id > 0): 
                            $category_name = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $category_id) {
                                    $category_name = $cat['name'];
                                    break;
                                }
                            }
                        ?>
                            <span>Category: <strong><?php echo $category_name; ?></strong></span>
                        <?php endif; ?>
                        
                        <span class="ms-2">(<?php echo $total_posts; ?> posts found)</span>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($posts)): ?>
                    <div class="alert alert-info">
                        <p class="mb-0">No blog posts found. Please check back later for new content.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 blog-card">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light text-center py-5">
                                            <i class="fas fa-newspaper fa-4x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <?php if (!empty($post['category_name'])): ?>
                                            <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo $post['category_id']; ?>" class="badge bg-primary text-decoration-none mb-2">
                                                <?php echo $post['category_name']; ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <h5 class="card-title">
                                            <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark">
                                                <?php echo $post['title']; ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="mb-3 text-muted small">
                                            <i class="fas fa-user me-1"></i> <?php echo $post['author_name']; ?> | 
                                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                        </div>
                                        
                                        <p class="card-text">
                                            <?php 
                                            if (!empty($post['excerpt'])) {
                                                echo $post['excerpt'];
                                            } else {
                                                echo substr(strip_tags($post['content']), 0, 150) . '...';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="card-footer bg-white border-top-0">
                                        <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-outline-primary btn-sm">
                                            Read More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Blog pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($prev_page): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog.php?page=<?php echo $prev_page; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $start_page + 4);
                                
                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog.php?page=1<?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog.php?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog.php?page=<?php echo $total_pages; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                            <?php echo $total_pages; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($next_page): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog.php?page=<?php echo $next_page; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Categories -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($categories as $category): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                                        <?php echo $category['name']; ?>
                                    </a>
                                    <span class="badge bg-primary rounded-pill"><?php echo $category['post_count']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Recent Posts -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Recent Posts</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_posts as $post): ?>
                                <li class="list-group-item">
                                    <div class="row g-0">
                                        <?php if (!empty($post['featured_image'])): ?>
                                            <div class="col-3">
                                                <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>" class="img-fluid rounded" alt="<?php echo $post['title']; ?>">
                                            </div>
                                            <div class="col-9 ps-3">
                                        <?php else: ?>
                                            <div class="col-12">
                                        <?php endif; ?>
                                                <h6 class="mb-1">
                                                    <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
                                                        <?php echo $post['title']; ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                                </small>
                                            </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Newsletter Signup -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Subscribe to Our Newsletter</h5>
                    </div>
                    <div class="card-body">
                        <p>Stay updated with our latest blog posts, news, and special offers.</p>
                        <form action="<?php echo SITE_URL; ?>/subscribe.php" method="post">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <select class="form-select" name="subscriber_type" required>
                                    <option value="">I am a...</option>
                                    <option value="parent">Parent</option>
                                    <option value="school_staff">School Staff</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-9 mb-4 mb-lg-0">
                <h3 class="fw-bold mb-2">Want to learn more about our services?</h3>
                <p class="mb-0">Contact us today to discuss how we can help with your technology needs.</p>
            </div>
            <div class="col-lg-3 text-lg-end">
                <a href="<?php echo SITE_URL; ?>/about.php#contact" class="btn btn-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<style>
.blog-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.blog-card .card-img-top {
    height: 200px;
    object-fit: cover;
    transition: all 0.5s ease;
}

.blog-card:hover .card-img-top {
    transform: scale(1.05);
}
</style>

<?php include 'includes/footer.php'; ?>