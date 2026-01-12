<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
        // Get form data
        $name = isset($_POST['name']) ? sanitize_admin_input($_POST['name']) : '';
        $slug = isset($_POST['slug']) ? sanitize_admin_input($_POST['slug']) : '';
        $description = isset($_POST['description']) ? sanitize_admin_input($_POST['description']) : '';
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = generate_slug($name);
        }
        
        // Validate form data
        if (empty($name)) {
            set_admin_alert('Name is required.', 'danger');
        } else {
            try {
                if (isset($_POST['add_category'])) {
                    // Check if slug already exists
                    $stmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = :slug");
                    $stmt->execute(['slug' => $slug]);
                    if ($stmt->rowCount() > 0) {
                        $slug = $slug . '-' . time();
                    }
                    
                    // Add new category
                    $stmt = $db->prepare("
                        INSERT INTO blog_categories (name, slug, description)
                        VALUES (:name, :slug, :description)
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description
                    ]);
                    
                    set_admin_alert('Category added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/blog-categories.php');
                    exit;
                } elseif (isset($_POST['edit_category'])) {
                    // Edit existing category
                    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
                    
                    if ($category_id > 0) {
                        // Check if slug already exists for other categories
                        $stmt = $db->prepare("SELECT id FROM blog_categories WHERE slug = :slug AND id != :id");
                        $stmt->execute(['slug' => $slug, 'id' => $category_id]);
                        if ($stmt->rowCount() > 0) {
                            $slug = $slug . '-' . time();
                        }
                        
                        // Update category
                        $stmt = $db->prepare("
                            UPDATE blog_categories
                            SET name = :name, slug = :slug, description = :description
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'name' => $name,
                            'slug' => $slug,
                            'description' => $description,
                            'id' => $category_id
                        ]);
                        
                        set_admin_alert('Category updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/blog-categories.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        // Delete category
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        
        if ($category_id > 0) {
            try {
                // Check if category is used in any posts
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM blog_posts WHERE category_id = :category_id");
                $stmt->execute(['category_id' => $category_id]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    // Update posts to remove category
                    $stmt = $db->prepare("UPDATE blog_posts SET category_id = NULL WHERE category_id = :category_id");
                    $stmt->execute(['category_id' => $category_id]);
                }
                
                // Delete category
                $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = :id");
                $stmt->execute(['id' => $category_id]);
                
                set_admin_alert('Category deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/blog-categories.php');
        exit;
    }
}

// Get category data for editing
$category = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM blog_categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        
        if (!$category) {
            set_admin_alert('Category not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/blog-categories.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/blog-categories.php');
        exit;
    }
}

// Get all categories for listing
$categories = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("
            SELECT c.*, COUNT(p.id) as post_count
            FROM blog_categories c
            LEFT JOIN blog_posts p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Category Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <?php if ($action === 'edit' && $category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $category ? $category['name'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo $category ? $category['slug'] : ''; ?>">
                    <small class="form-text text-muted">Leave empty to generate automatically from name.</small>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $category ? $category['description'] : ''; ?></textarea>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_category' : 'edit_category'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Category' : 'Update Category'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/blog-categories.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Categories List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Blog Categories</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/blog-categories.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Category
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    No categories found. Click the "Add New Category" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Posts</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo $category['name']; ?></td>
                                    <td><?php echo $category['slug']; ?></td>
                                    <td><?php echo $category['description'] ? substr($category['description'], 0, 50) . (strlen($category['description']) > 50 ? '...' : '') : ''; ?></td>
                                    <td><?php echo $category['post_count']; ?></td>
                                    <td><?php echo format_admin_date($category['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/blog-categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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