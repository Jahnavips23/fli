<?php
require_once 'includes/config.php';

// Get post slug
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

// Get post data
$post = null;
try {
    $stmt = $db->prepare("
        SELECT p.*, u.username as author_name, c.name as category_name, c.slug as category_slug
        FROM blog_posts p
        LEFT JOIN users u ON p.author_id = u.id
        LEFT JOIN blog_categories c ON p.category_id = c.id
        WHERE p.slug = :slug AND p.status = 'published'
    ");
    $stmt->execute(['slug' => $slug]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header('Location: ' . SITE_URL . '/blog.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching blog post: " . $e->getMessage());
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

// Set page title and description
$page_title = $post['title'];
$page_description = !empty($post['excerpt']) ? $post['excerpt'] : substr(strip_tags($post['content']), 0, 160);

// Get related posts
$related_posts = [];
try {
    $stmt = $db->prepare("
        SELECT p.id, p.title, p.slug, p.created_at, p.featured_image
        FROM blog_posts p
        WHERE p.status = 'published'
        AND p.id != :post_id
        AND (p.category_id = :category_id OR p.category_id IS NULL)
        ORDER BY p.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([
        'post_id' => $post['id'],
        'category_id' => $post['category_id'] ?? 0
    ]);
    $related_posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching related posts: " . $e->getMessage());
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
        AND p.id != :post_id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute(['post_id' => $post['id']]);
    $recent_posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching recent posts: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <h1 class="fw-bold"><?php echo $post['title']; ?></h1>
                <div class="mt-3">
                    <?php if (!empty($post['category_name'])): ?>
                        <span class="badge bg-light text-primary me-2">
                            <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo $post['category_id']; ?>" class="text-decoration-none">
                                <?php echo $post['category_name']; ?>
                            </a>
                        </span>
                    <?php endif; ?>
                    <span class="text-white-50">
                        <i class="fas fa-user me-1"></i> <?php echo $post['author_name']; ?> | 
                        <i class="fas fa-calendar-alt me-1"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Post Content -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (!empty($post['featured_image'])): ?>
                            <div class="mb-4">
                                <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>" class="img-fluid rounded" alt="<?php echo $post['title']; ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <?php echo $post['content']; ?>
                        </div>
                        
                        <!-- Social Share -->
                        <div class="mt-5 pt-4 border-top">
                            <h5>Share This Post</h5>
                            <div class="d-flex gap-2 mt-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/blog-post.php?slug=' . $post['slug']); ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-facebook-f me-1"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/blog-post.php?slug=' . $post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="btn btn-outline-info">
                                    <i class="fab fa-twitter me-1"></i> Twitter
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(SITE_URL . '/blog-post.php?slug=' . $post['slug']); ?>&title=<?php echo urlencode($post['title']); ?>" target="_blank" class="btn btn-outline-secondary">
                                    <i class="fab fa-linkedin-in me-1"></i> LinkedIn
                                </a>
                                <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode('Check out this article: ' . SITE_URL . '/blog-post.php?slug=' . $post['slug']); ?>" class="btn btn-outline-dark">
                                    <i class="fas fa-envelope me-1"></i> Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Related Posts -->
                <?php if (!empty($related_posts)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Related Posts</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($related_posts as $related): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 blog-card-sm">
                                            <?php if (!empty($related['featured_image'])): ?>
                                                <img src="<?php echo SITE_URL . '/' . $related['featured_image']; ?>" class="card-img-top" alt="<?php echo $related['title']; ?>">
                                            <?php else: ?>
                                                <div class="card-img-top bg-light text-center py-4">
                                                    <i class="fas fa-newspaper fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $related['slug']; ?>" class="text-decoration-none text-dark">
                                                        <?php echo $related['title']; ?>
                                                    </a>
                                                </h6>
                                                <div class="small text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M j, Y', strtotime($related['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Back to Blog -->
                <div class="text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Blog
                    </a>
                </div>
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
                            <?php foreach ($recent_posts as $recent): ?>
                                <li class="list-group-item">
                                    <div class="row g-0">
                                        <?php if (!empty($recent['featured_image'])): ?>
                                            <div class="col-3">
                                                <img src="<?php echo SITE_URL . '/' . $recent['featured_image']; ?>" class="img-fluid rounded" alt="<?php echo $recent['title']; ?>">
                                            </div>
                                            <div class="col-9 ps-3">
                                        <?php else: ?>
                                            <div class="col-12">
                                        <?php endif; ?>
                                                <h6 class="mb-1">
                                                    <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $recent['slug']; ?>" class="text-decoration-none">
                                                        <?php echo $recent['title']; ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M j, Y', strtotime($recent['created_at'])); ?>
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
.blog-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.blog-content img {
    max-width: 100%;
    height: auto;
    margin: 1.5rem 0;
}

.blog-content h2, .blog-content h3, .blog-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.blog-content p {
    margin-bottom: 1.5rem;
}

.blog-content ul, .blog-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.blog-content blockquote {
    border-left: 4px solid #0d6efd;
    padding-left: 1rem;
    font-style: italic;
    margin: 1.5rem 0;
}

.blog-card-sm {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.blog-card-sm:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.blog-card-sm .card-img-top {
    height: 120px;
    object-fit: cover;
}
</style>

<?php include 'includes/footer.php'; ?>