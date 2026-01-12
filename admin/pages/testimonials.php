<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_testimonial']) || isset($_POST['edit_testimonial'])) {
        // Get form data
        $name = isset($_POST['name']) ? sanitize_admin_input($_POST['name']) : '';
        $position = isset($_POST['position']) ? sanitize_admin_input($_POST['position']) : '';
        $organization = isset($_POST['organization']) ? sanitize_admin_input($_POST['organization']) : '';
        $content = isset($_POST['content']) ? sanitize_admin_input($_POST['content']) : '';
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($name)) {
            set_admin_alert('Name is required.', 'danger');
        } elseif (empty($content)) {
            set_admin_alert('Testimonial content is required.', 'danger');
        } else {
            try {
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = SITE_ROOT . 'assets/images/testimonials/';
                    $file_name = time() . '_' . basename($_FILES['image']['name']);
                    $upload_file = $upload_dir . $file_name;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        $image_path = 'assets/images/testimonials/' . $file_name;
                    } else {
                        set_admin_alert('Failed to upload image.', 'danger');
                    }
                }
                
                if (isset($_POST['add_testimonial'])) {
                    // Add new testimonial
                    $stmt = $db->prepare("
                        INSERT INTO testimonials (name, position, organization, content, rating, image, display_order, active)
                        VALUES (:name, :position, :organization, :content, :rating, :image, :display_order, :active)
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'position' => $position,
                        'organization' => $organization,
                        'content' => $content,
                        'rating' => $rating,
                        'image' => $image_path,
                        'display_order' => $display_order,
                        'active' => $active
                    ]);
                    
                    set_admin_alert('Testimonial added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/testimonials.php');
                    exit;
                } elseif (isset($_POST['edit_testimonial'])) {
                    // Edit existing testimonial
                    $testimonial_id = isset($_POST['testimonial_id']) ? (int)$_POST['testimonial_id'] : 0;
                    
                    if ($testimonial_id > 0) {
                        // Get current testimonial data
                        $stmt = $db->prepare("SELECT image FROM testimonials WHERE id = :id");
                        $stmt->execute(['id' => $testimonial_id]);
                        $current_testimonial = $stmt->fetch();
                        
                        // Use current image if no new image uploaded
                        if (empty($image_path) && $current_testimonial) {
                            $image_path = $current_testimonial['image'];
                        }
                        
                        // Update testimonial
                        $stmt = $db->prepare("
                            UPDATE testimonials
                            SET name = :name, position = :position, organization = :organization,
                                content = :content, rating = :rating, image = :image,
                                display_order = :display_order, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'name' => $name,
                            'position' => $position,
                            'organization' => $organization,
                            'content' => $content,
                            'rating' => $rating,
                            'image' => $image_path,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $testimonial_id
                        ]);
                        
                        set_admin_alert('Testimonial updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/testimonials.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_testimonial'])) {
        // Delete testimonial
        $testimonial_id = isset($_POST['testimonial_id']) ? (int)$_POST['testimonial_id'] : 0;
        
        if ($testimonial_id > 0) {
            try {
                // Get testimonial image path
                $stmt = $db->prepare("SELECT image FROM testimonials WHERE id = :id");
                $stmt->execute(['id' => $testimonial_id]);
                $testimonial = $stmt->fetch();
                
                // Delete testimonial from database
                $stmt = $db->prepare("DELETE FROM testimonials WHERE id = :id");
                $stmt->execute(['id' => $testimonial_id]);
                
                // Delete image file
                if ($testimonial && !empty($testimonial['image'])) {
                    $image_file = SITE_ROOT . $testimonial['image'];
                    if (file_exists($image_file)) {
                        unlink($image_file);
                    }
                }
                
                set_admin_alert('Testimonial deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/testimonials.php');
        exit;
    }
}

// Get testimonial data for editing
$testimonial = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $testimonial = $stmt->fetch();
        
        if (!$testimonial) {
            set_admin_alert('Testimonial not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/testimonials.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/testimonials.php');
        exit;
    }
}

// Get all testimonials for listing
$testimonials = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM testimonials ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $testimonials = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Testimonial Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Testimonial' : 'Edit Testimonial'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $testimonial): ?>
                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $testimonial ? $testimonial['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" value="<?php echo $testimonial ? $testimonial['position'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="organization" class="form-label">Organization</label>
                            <input type="text" class="form-control" id="organization" name="organization" value="<?php echo $testimonial ? $testimonial['organization'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($testimonial && $testimonial['rating'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="content" class="form-label">Testimonial Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo $testimonial ? $testimonial['content'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Person Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 200x200 pixels (square)</small>
                            
                            <?php if ($testimonial && !empty($testimonial['image'])): ?>
                                <div class="mt-2">
                                    <p>Current Image:</p>
                                    <img src="<?php echo SITE_URL . '/' . $testimonial['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $testimonial ? $testimonial['display_order'] : '0'; ?>" min="0">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$testimonial || $testimonial['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_testimonial' : 'edit_testimonial'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Testimonial' : 'Update Testimonial'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/testimonials.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Testimonials List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Testimonials</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/testimonials.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Testimonial
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($testimonials)): ?>
                <div class="alert alert-info">
                    No testimonials found. Click the "Add New Testimonial" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Organization</th>
                                <th>Rating</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $testimonial): ?>
                                <tr>
                                    <td><?php echo $testimonial['id']; ?></td>
                                    <td>
                                        <?php if (!empty($testimonial['image'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $testimonial['image']; ?>" alt="Testimonial" class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $testimonial['name']; ?></td>
                                    <td><?php echo $testimonial['position']; ?></td>
                                    <td><?php echo $testimonial['organization']; ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo ($i <= $testimonial['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?php echo $testimonial['display_order']; ?></td>
                                    <td>
                                        <?php if ($testimonial['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/testimonials.php?action=edit&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" name="delete_testimonial" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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