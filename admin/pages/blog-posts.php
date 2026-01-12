<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post']) || isset($_POST['edit_post'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $slug = isset($_POST['slug']) ? sanitize_admin_input($_POST['slug']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : ''; // Don't sanitize rich text content
        $excerpt = isset($_POST['excerpt']) ? sanitize_admin_input($_POST['excerpt']) : '';
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $status = isset($_POST['status']) ? sanitize_admin_input($_POST['status']) : 'draft';
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = generate_slug($title);
        }
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Title is required.', 'danger');
        } elseif (empty($content)) {
            set_admin_alert('Content is required.', 'danger');
        } else {
            try {
                // Handle featured image upload
                $featured_image = '';
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = SITE_ROOT . 'assets/images/blog/';
                    $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                    $upload_file = $upload_dir . $file_name;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_file)) {
                        $featured_image = 'assets/images/blog/' . $file_name;
                    } else {
                        set_admin_alert('Failed to upload featured image.', 'danger');
                    }
                }
                
                if (isset($_POST['add_post'])) {
                    // Check if slug already exists
                    $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = :slug");
                    $stmt->execute(['slug' => $slug]);
                    if ($stmt->rowCount() > 0) {
                        $slug = $slug . '-' . time();
                    }
                    
                    // Add new post
                    $stmt = $db->prepare("
                        INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, author_id, status, category_id)
                        VALUES (:title, :slug, :content, :excerpt, :featured_image, :author_id, :status, :category_id)
                    ");
                    $stmt->execute([
                        'title' => $title,
                        'slug' => $slug,
                        'content' => $content,
                        'excerpt' => $excerpt,
                        'featured_image' => $featured_image,
                        'author_id' => $_SESSION['admin_id'],
                        'status' => $status,
                        'category_id' => $category_id
                    ]);
                    
                    set_admin_alert('Post added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/blog-posts.php');
                    exit;
                } elseif (isset($_POST['edit_post'])) {
                    // Edit existing post
                    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
                    
                    if ($post_id > 0) {
                        // Check if slug already exists for other posts
                        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = :slug AND id != :id");
                        $stmt->execute(['slug' => $slug, 'id' => $post_id]);
                        if ($stmt->rowCount() > 0) {
                            $slug = $slug . '-' . time();
                        }
                        
                        // Get current post data
                        $stmt = $db->prepare("SELECT featured_image FROM blog_posts WHERE id = :id");
                        $stmt->execute(['id' => $post_id]);
                        $current_post = $stmt->fetch();
                        
                        // Use current image if no new image uploaded
                        if (empty($featured_image) && $current_post) {
                            $featured_image = $current_post['featured_image'];
                        }
                        
                        // Update post
                        $stmt = $db->prepare("
                            UPDATE blog_posts
                            SET title = :title, slug = :slug, content = :content, excerpt = :excerpt,
                                featured_image = :featured_image, status = :status, category_id = :category_id
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'slug' => $slug,
                            'content' => $content,
                            'excerpt' => $excerpt,
                            'featured_image' => $featured_image,
                            'status' => $status,
                            'category_id' => $category_id,
                            'id' => $post_id
                        ]);
                        
                        set_admin_alert('Post updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/blog-posts.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_post'])) {
        // Delete post
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        
        if ($post_id > 0) {
            try {
                // Get post featured image
                $stmt = $db->prepare("SELECT featured_image FROM blog_posts WHERE id = :id");
                $stmt->execute(['id' => $post_id]);
                $post = $stmt->fetch();
                
                // Delete post from database
                $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = :id");
                $stmt->execute(['id' => $post_id]);
                
                // Delete featured image file
                if ($post && !empty($post['featured_image'])) {
                    $image_file = SITE_ROOT . $post['featured_image'];
                    if (file_exists($image_file)) {
                        unlink($image_file);
                    }
                }
                
                set_admin_alert('Post deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/blog-posts.php');
        exit;
    }
}

// Get post data for editing
$post = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            set_admin_alert('Post not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/blog-posts.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/blog-posts.php');
        exit;
    }
}

// Get all posts for listing
$posts = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("
            SELECT p.*, u.username as author_name, c.name as category_name
            FROM blog_posts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN blog_categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

// Get categories for dropdown
$categories = [];
try {
    $stmt = $db->prepare("SELECT id, name FROM blog_categories ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Post Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Post' : 'Edit Post'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $post): ?>
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $post ? $post['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $post ? $post['slug'] : ''; ?>">
                            <small class="form-text text-muted">Leave empty to generate automatically from title.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control summernote" id="content" name="content" rows="10" required><?php echo $post ? $post['content'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo $post ? $post['excerpt'] : ''; ?></textarea>
                            <small class="form-text text-muted">A short summary of the post. Leave empty to generate automatically.</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Publish</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo ($post && $post['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo ($post && $post['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="<?php echo $action === 'add' ? 'add_post' : 'edit_post'; ?>" class="btn btn-primary">
                                        <?php echo $action === 'add' ? 'Add Post' : 'Update Post'; ?>
                                    </button>
                                    <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Category</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo ($post && $post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-grid">
                                    <a href="<?php echo ADMIN_URL; ?>/pages/blog-categories.php?action=add" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Add New Category
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Featured Image</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <small class="form-text text-muted">Recommended size: 1200x630 pixels</small>
                                </div>
                                
                                <?php if ($post && !empty($post['featured_image'])): ?>
                                    <div class="mt-2">
                                        <p>Current Image:</p>
                                        <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>" alt="Featured Image" class="img-thumbnail">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Posts List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Blog Posts</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Post
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    No posts found. Click the "Add New Post" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo $post['title']; ?></td>
                                    <td><?php echo $post['author_name']; ?></td>
                                    <td><?php echo $post['category_name'] ? $post['category_name'] : 'Uncategorized'; ?></td>
                                    <td>
                                        <?php if ($post['status'] === 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($post['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <button type="submit" name="delete_post" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>