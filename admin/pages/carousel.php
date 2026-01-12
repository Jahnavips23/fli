<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_slide']) || isset($_POST['edit_slide'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_admin_input($_POST['description']) : '';
        $button_text = isset($_POST['button_text']) ? sanitize_admin_input($_POST['button_text']) : '';
        $button_link = isset($_POST['button_link']) ? sanitize_admin_input($_POST['button_link']) : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Title is required.', 'danger');
        } else {
            try {
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = SITE_ROOT . 'assets/images/carousel/';
                    $file_name = time() . '_' . basename($_FILES['image']['name']);
                    $upload_file = $upload_dir . $file_name;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        $image_path = 'assets/images/carousel/' . $file_name;
                    } else {
                        set_admin_alert('Failed to upload image.', 'danger');
                    }
                }
                
                if (isset($_POST['add_slide'])) {
                    // Add new slide
                    if (empty($image_path)) {
                        set_admin_alert('Image is required.', 'danger');
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO carousel_slides (title, description, image_path, button_text, button_link, active, display_order)
                            VALUES (:title, :description, :image_path, :button_text, :button_link, :active, :display_order)
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'image_path' => $image_path,
                            'button_text' => $button_text,
                            'button_link' => $button_link,
                            'active' => $active,
                            'display_order' => $display_order
                        ]);
                        
                        set_admin_alert('Slide added successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/carousel.php');
                        exit;
                    }
                } elseif (isset($_POST['edit_slide'])) {
                    // Edit existing slide
                    $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
                    
                    if ($slide_id > 0) {
                        // Get current slide data
                        $stmt = $db->prepare("SELECT image_path FROM carousel_slides WHERE id = :id");
                        $stmt->execute(['id' => $slide_id]);
                        $current_slide = $stmt->fetch();
                        
                        // Use current image if no new image uploaded
                        if (empty($image_path) && $current_slide) {
                            $image_path = $current_slide['image_path'];
                        }
                        
                        // Update slide
                        $stmt = $db->prepare("
                            UPDATE carousel_slides
                            SET title = :title, description = :description, image_path = :image_path,
                                button_text = :button_text, button_link = :button_link,
                                active = :active, display_order = :display_order
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'image_path' => $image_path,
                            'button_text' => $button_text,
                            'button_link' => $button_link,
                            'active' => $active,
                            'display_order' => $display_order,
                            'id' => $slide_id
                        ]);
                        
                        set_admin_alert('Slide updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/carousel.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_slide'])) {
        // Delete slide
        $slide_id = isset($_POST['slide_id']) ? (int)$_POST['slide_id'] : 0;
        
        if ($slide_id > 0) {
            try {
                // Get slide image path
                $stmt = $db->prepare("SELECT image_path FROM carousel_slides WHERE id = :id");
                $stmt->execute(['id' => $slide_id]);
                $slide = $stmt->fetch();
                
                // Delete slide from database
                $stmt = $db->prepare("DELETE FROM carousel_slides WHERE id = :id");
                $stmt->execute(['id' => $slide_id]);
                
                // Delete image file
                if ($slide && !empty($slide['image_path'])) {
                    $image_file = SITE_ROOT . $slide['image_path'];
                    if (file_exists($image_file)) {
                        unlink($image_file);
                    }
                }
                
                set_admin_alert('Slide deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/carousel.php');
        exit;
    }
}

// Get slide data for editing
$slide = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM carousel_slides WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $slide = $stmt->fetch();
        
        if (!$slide) {
            set_admin_alert('Slide not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/carousel.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/carousel.php');
        exit;
    }
}

// Get all slides for listing
$slides = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM carousel_slides ORDER BY display_order ASC");
        $stmt->execute();
        $slides = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Slide Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Slide' : 'Edit Slide'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $slide): ?>
                    <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $slide ? $slide['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo $slide ? $slide['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="button_text" class="form-label">Button Text</label>
                            <input type="text" class="form-control" id="button_text" name="button_text" value="<?php echo $slide ? $slide['button_text'] : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="button_link" class="form-label">Button Link</label>
                            <input type="text" class="form-control" id="button_link" name="button_link" value="<?php echo $slide ? $slide['button_link'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Slide Image <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?></label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            <small class="form-text text-muted">Recommended size: 1920x600 pixels</small>
                            
                            <?php if ($slide && !empty($slide['image_path'])): ?>
                                <div class="mt-2">
                                    <p>Current Image:</p>
                                    <img src="<?php echo SITE_URL . '/' . $slide['image_path']; ?>" alt="Current Slide" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $slide ? $slide['display_order'] : '0'; ?>" min="0">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$slide || $slide['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_slide' : 'edit_slide'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Slide' : 'Update Slide'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/carousel.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Slides List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Carousel Slides</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/carousel.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Slide
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($slides)): ?>
                <div class="alert alert-info">
                    No slides found. Click the "Add New Slide" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slides as $slide): ?>
                                <tr>
                                    <td><?php echo $slide['id']; ?></td>
                                    <td>
                                        <?php if (!empty($slide['image_path'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $slide['image_path']; ?>" alt="Slide" class="img-thumbnail" style="max-height: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $slide['title']; ?></td>
                                    <td><?php echo $slide['display_order']; ?></td>
                                    <td>
                                        <?php if ($slide['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($slide['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/carousel.php?action=edit&id=<?php echo $slide['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                                            <button type="submit" name="delete_slide" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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