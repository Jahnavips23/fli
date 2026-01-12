<?php
require_once 'includes/config.php';

// Get counts for dashboard
$users_count = get_count('users');
$posts_count = get_count('blog_posts');
$downloads_count = get_count('downloads');
$subscribers_count = get_count('newsletter_subscribers');

// Get recent blog posts
$recent_posts = get_recent_items('blog_posts', 5);

// Get recent subscribers
$recent_subscribers = get_recent_items('newsletter_subscribers', 5);

// Get visitor statistics for the chart
$visitor_stats = get_visitor_stats('year');

include 'includes/header.php';
?>

<!-- Dashboard Overview -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $users_count; ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="stats-icon bg-white text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-white py-2">
                <a href="<?php echo ADMIN_URL; ?>/pages/users.php" class="text-white d-flex justify-content-between align-items-center">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $posts_count; ?></h3>
                        <p class="mb-0">Blog Posts</p>
                    </div>
                    <div class="stats-icon bg-white text-success">
                        <i class="fas fa-blog"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-white py-2">
                <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php" class="text-white d-flex justify-content-between align-items-center">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $downloads_count; ?></h3>
                        <p class="mb-0">Downloads</p>
                    </div>
                    <div class="stats-icon bg-white text-warning">
                        <i class="fas fa-download"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-white py-2">
                <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php" class="text-white d-flex justify-content-between align-items-center">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?php echo $subscribers_count; ?></h3>
                        <p class="mb-0">Subscribers</p>
                    </div>
                    <div class="stats-icon bg-white text-info">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-white py-2">
                <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php" class="text-white d-flex justify-content-between align-items-center">
                    <span>View Details</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Recent Activity -->
<div class="row">
    <!-- Visitor Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Website Traffic</h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        This Year
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartDropdown">
                        <li><a class="dropdown-item chart-period" href="javascript:void(0);" data-period="week">This Week</a></li>
                        <li><a class="dropdown-item chart-period" href="javascript:void(0);" data-period="month">This Month</a></li>
                        <li><a class="dropdown-item chart-period active" href="javascript:void(0);" data-period="year">This Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body" style="height: 300px; max-height: 300px; overflow: hidden;">
                <div style="position: relative; height: 100%; width: 100%;">
                    <canvas id="visitorChart"></canvas>
                    <div id="chartLoading" class="chart-loading d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <script>
                    // Embed visitor data directly
                    var visitorChartLabels = <?php echo json_encode($visitor_stats['labels']); ?>;
                    var visitorChartData = <?php echo json_encode($visitor_stats['data']); ?>;
                </script>
            </div>
        </div>
    </div>
    
    <!-- Recent Subscribers -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Subscribers</h5>
                <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_subscribers)): ?>
                                <?php foreach ($recent_subscribers as $subscriber): ?>
                                    <tr>
                                        <td><?php echo $subscriber['email']; ?></td>
                                        <td>
                                            <?php 
                                            switch ($subscriber['subscriber_type']) {
                                                case 'parent':
                                                    echo '<span class="badge bg-primary">Parent</span>';
                                                    break;
                                                case 'school_staff':
                                                    echo '<span class="badge bg-success">School Staff</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary">Other</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo format_admin_date($subscriber['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No subscribers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Blog Posts -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Recent Blog Posts</h5>
                <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_posts)): ?>
                                <?php foreach ($recent_posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['title']; ?></td>
                                        <td>
                                            <?php if ($post['status'] === 'published'): ?>
                                                <span class="badge bg-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Get author name
                                            $author = get_admin_user($post['author_id']);
                                            echo $author ? $author['username'] : 'Unknown';
                                            ?>
                                        </td>
                                        <td><?php echo format_admin_date($post['created_at']); ?></td>
                                        <td>
                                            <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No blog posts found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php?action=add" class="btn btn-primary d-block py-3">
                            <i class="fas fa-plus-circle me-2"></i> Add Blog Post
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php?action=add" class="btn btn-success d-block py-3">
                            <i class="fas fa-plus-circle me-2"></i> Add Download
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-sm-0">
                        <a href="<?php echo ADMIN_URL; ?>/pages/users.php?action=add" class="btn btn-info d-block py-3">
                            <i class="fas fa-plus-circle me-2"></i> Add User
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="<?php echo ADMIN_URL; ?>/pages/carousel.php?action=add" class="btn btn-warning d-block py-3">
                            <i class="fas fa-plus-circle me-2"></i> Add Slide
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>