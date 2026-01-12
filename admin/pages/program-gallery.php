<?php
require_once '../includes/config.php';

// Get program ID
$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if program exists
$program = null;
if ($program_id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM kids_programs WHERE id = :id");
        $stmt->execute(['id' => $program_id]);
        $program = $stmt->fetch();
        
        if (!$program) {
            set_admin_alert('Program not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
        exit;
    }
} else {
    set_admin_alert('Invalid program ID.', 'danger');
    header('Location: ' . ADMIN_URL . '/pages/kids-programs.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_image']) || isset($_POST['edit_image'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_admin_input($_POST['description']) : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Image title is required.', 'danger');
        } else {
            // Handle image upload
            $image = '';
            if (isset($_POST['current_image'])) {
                $image = $_POST['current_image'];
            }
            
            if (!empty($_FILES['image']['name']) || isset($_POST['add_image'])) {
                $upload_dir = ROOT_PATH . 'assets/images/kids/gallery/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                $relative_path = 'assets/images/kids/gallery/' . $file_name;
                
                $upload_ok = true;
                
                // Skip validation if editing and no new image
                if (empty($_FILES['image']['name']) && isset($_POST['edit_image'])) {
                    $upload_ok = false;
                } else {
                    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    
                    // Check if image file is an actual image
                    $check = getimagesize($_FILES['image']['tmp_name']);
                    if ($check === false) {
                        set_admin_alert('File is not an image.', 'danger');
                        $upload_ok = false;
                    }
                    
                    // Check file size (limit to 5MB)
                    if ($_FILES['image']['size'] > 5000000) {
                        set_admin_alert('Image file is too large. Maximum size is 5MB.', 'danger');
                        $upload_ok = false;
                    }
                    
                    // Allow certain file formats
                    if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        set_admin_alert('Only JPG, JPEG, PNG & GIF files are allowed.', 'danger');
                        $upload_ok = false;
                    }
                }
                
                // Upload file
                if ($upload_ok && !empty($_FILES['image']['name'])) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image = $relative_path;
                    } else {
                        set_admin_alert('There was an error uploading your file.', 'danger');
                    }
                }
            }
            
            try {
                if (isset($_POST['add_image'])) {
                    // Add new gallery image
                    if (empty($image)) {
                        set_admin_alert('Image file is required.', 'danger');
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
                            VALUES (:program_id, :title, :description, :image, :display_order, :active)
                        ");
                        $stmt->execute([
                            'program_id' => $program_id,
                            'title' => $title,
                            'description' => $description,
                            'image' => $image,
                            'display_order' => $display_order,
                            'active' => $active
                        ]);
                        
                        set_admin_alert('Gallery image added successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/program-gallery.php?program_id=' . $program_id);
                        exit;
                    }
                } elseif (isset($_POST['edit_image'])) {
                    // Edit existing gallery image
                    $gallery_id = isset($_POST['gallery_id']) ? (int)$_POST['gallery_id'] : 0;
                    
                    if ($gallery_id > 0) {
                        // Update gallery image
                        $stmt = $db->prepare("
                            UPDATE program_gallery
                            SET title = :title, description = :description, 
                                display_order = :display_order, active = :active
                            " . (!empty($image) ? ", image = :image" : "") . "
                            WHERE id = :id AND program_id = :program_id
                        ");
                        
                        $params = [
                            'title' => $title,
                            'description' => $description,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $gallery_id,
                            'program_id' => $program_id
                        ];
                        
                        if (!empty($image)) {
                            $params['image'] = $image;
                        }
                        
                        $stmt->execute($params);
                        
                        set_admin_alert('Gallery image updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/program-gallery.php?program_id=' . $program_id);
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_image'])) {
        // Delete gallery image
        $gallery_id = isset($_POST['gallery_id']) ? (int)$_POST['gallery_id'] : 0;
        
        if ($gallery_id > 0) {
            try {
                // Get gallery image
                $stmt = $db->prepare("SELECT image FROM program_gallery WHERE id = :id AND program_id = :program_id");
                $stmt->execute([
                    'id' => $gallery_id,
                    'program_id' => $program_id
                ]);
                $gallery = $stmt->fetch();
                
                // Delete gallery image from database
                $stmt = $db->prepare("DELETE FROM program_gallery WHERE id = :id AND program_id = :program_id");
                $stmt->execute([
                    'id' => $gallery_id,
                    'program_id' => $program_id
                ]);
                
                // Delete gallery image file if exists
                if ($gallery && !empty($gallery['image'])) {
                    $image_path = ROOT_PATH . $gallery['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                set_admin_alert('Gallery image deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/program-gallery.php?program_id=' . $program_id);
        exit;
    }
}

// Get gallery image data for editing
$gallery_item = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM program_gallery WHERE id = :id AND program_id = :program_id");
        $stmt->execute([
            'id' => $id,
            'program_id' => $program_id
        ]);
        $gallery_item = $stmt->fetch();
        
        if (!$gallery_item) {
            set_admin_alert('Gallery image not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/program-gallery.php?program_id=' . $program_id);
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/program-gallery.php?program_id=' . $program_id);
        exit;
    }
}

// Get all gallery images for listing
$gallery_items = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM program_gallery WHERE program_id = :program_id ORDER BY display_order ASC, created_at DESC");
        $stmt->execute(['program_id' => $program_id]);
        $gallery_items = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Gallery for: <?php echo $program['title']; ?></h4>
    <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Programs
    </a>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Gallery Image Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Gallery Image' : 'Edit Gallery Image'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $gallery_item): ?>
                    <input type="hidden" name="gallery_id" value="<?php echo $gallery_item['id']; ?>">
                    <?php if (!empty($gallery_item['image'])): ?>
                        <input type="hidden" name="current_image" value="<?php echo $gallery_item['image']; ?>">
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $gallery_item ? $gallery_item['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $gallery_item ? $gallery_item['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $gallery_item ? $gallery_item['display_order'] : '0'; ?>" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$gallery_item || $gallery_item['active']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Image <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?></label>
                            <?php if ($gallery_item && !empty($gallery_item['image'])): ?>
                                <div class="mb-3">
                                    <img src="<?php echo SITE_URL . '/' . $gallery_item['image']; ?>" alt="<?php echo $gallery_item['title']; ?>" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            <small class="form-text text-muted">Recommended size: 1200x800 pixels, max 5MB</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_image' : 'edit_image'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Image' : 'Update Image'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/program-gallery.php?program_id=<?php echo $program_id; ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Gallery Images List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Program Gallery</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/program-gallery.php?program_id=<?php echo $program_id; ?>&action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Image
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($gallery_items)): ?>
                <div class="alert alert-info">
                    No gallery images found. Click the "Add New Image" button to add images to this program's gallery.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($gallery_items as $item): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo SITE_URL . '/' . $item['image']; ?>" class="card-img-top" alt="<?php echo $item['title']; ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $item['title']; ?></h5>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="card-text"><?php echo $item['description']; ?></p>
                                    <?php endif; ?>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Order: <?php echo $item['display_order']; ?> | 
                                            Status: <?php echo $item['active'] ? 'Active' : 'Inactive'; ?>
                                        </small>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <a href="<?php echo ADMIN_URL; ?>/pages/program-gallery.php?program_id=<?php echo $program_id; ?>&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                            <input type="hidden" name="gallery_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="delete_image" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>