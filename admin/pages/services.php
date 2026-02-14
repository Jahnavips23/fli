<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service']) || isset($_POST['edit_service'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_admin_input($_POST['description']) : '';
        $icon = isset($_POST['icon']) ? sanitize_admin_input($_POST['icon']) : '';
        $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;

        // Validate form data
        if (empty($title)) {
            set_admin_alert('Title is required.', 'danger');
        } elseif (empty($description)) {
            set_admin_alert('Description is required.', 'danger');
        } elseif (empty($icon)) {
            set_admin_alert('Icon is required.', 'danger');
        } else {
            try {
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = ROOT_PATH . 'assets/images/services/';
                    $file_name = time() . '_' . basename($_FILES['image']['name']);
                    $upload_file = $upload_dir . $file_name;

                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    // Move uploaded file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                        $image_path = 'assets/images/services/' . $file_name;
                    } else {
                        set_admin_alert('Failed to upload image.', 'danger');
                    }
                }

                if (isset($_POST['add_service'])) {
                    // Add new service
                    $stmt = $db->prepare("
                        INSERT INTO services (title, description, icon, image, display_order, active)
                        VALUES (:title, :description, :icon, :image, :display_order, :active)
                    ");
                    $stmt->execute([
                        'title' => $title,
                        'description' => $description,
                        'icon' => $icon,
                        'image' => $image_path,
                        'display_order' => $display_order,
                        'active' => $active
                    ]);

                    set_admin_alert('Service added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/services.php');
                    exit;
                } elseif (isset($_POST['edit_service'])) {
                    // Edit existing service
                    $service_id = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;

                    if ($service_id > 0) {
                        // Get current service data
                        $stmt = $db->prepare("SELECT image FROM services WHERE id = :id");
                        $stmt->execute(['id' => $service_id]);
                        $current_service = $stmt->fetch();

                        // Use current image if no new image uploaded
                        if (empty($image_path) && $current_service) {
                            // Check if user wants to delete the image
                            if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
                                // Delete physical file
                                if (!empty($current_service['image']) && file_exists(ROOT_PATH . $current_service['image'])) {
                                    unlink(ROOT_PATH . $current_service['image']);
                                }
                                $image_path = ''; // Set to empty for DB
                            } else {
                                // Keep current image
                                $image_path = $current_service['image'];
                            }
                        }

                        // Update service
                        $stmt = $db->prepare("
                            UPDATE services
                            SET title = :title, description = :description, icon = :icon,
                                image = :image, display_order = :display_order, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'icon' => $icon,
                            'image' => $image_path,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $service_id
                        ]);

                        set_admin_alert('Service updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/services.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_service'])) {
        // Delete service
        $service_id = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;

        if ($service_id > 0) {
            try {
                // Get service image path
                $stmt = $db->prepare("SELECT image FROM services WHERE id = :id");
                $stmt->execute(['id' => $service_id]);
                $service = $stmt->fetch();

                // Delete service from database
                $stmt = $db->prepare("DELETE FROM services WHERE id = :id");
                $stmt->execute(['id' => $service_id]);

                // Delete image file
                if ($service && !empty($service['image'])) {
                    $image_file = ROOT_PATH . $service['image'];
                    if (file_exists($image_file)) {
                        unlink($image_file);
                    }
                }

                set_admin_alert('Service deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }

        header('Location: ' . ADMIN_URL . '/pages/services.php');
        exit;
    }
}

// Get service data for editing
$service = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch();

        if (!$service) {
            set_admin_alert('Service not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/services.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/services.php');
        exit;
    }
}

// Get all services for listing
$services = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM services ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $services = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Service Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Service' : 'Edit Service'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $service): ?>
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?php echo $service ? $service['title'] : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5"
                                required><?php echo $service ? $service['description'] : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon Class <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i id="icon-preview"
                                        class="<?php echo $service ? $service['icon'] : 'fas fa-cog'; ?>"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon"
                                    value="<?php echo $service ? $service['icon'] : ''; ?>"
                                    placeholder="e.g., fas fa-chalkboard-teacher" required>
                            </div>
                            <small class="form-text text-muted">Enter a Font Awesome icon class. <a
                                    href="https://fontawesome.com/icons" target="_blank">Browse icons</a></small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Service Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Recommended size: 600x400 pixels</small>

                            <?php if ($service && !empty($service['image'])): ?>
                                <div class="mt-2">
                                    <p>Current Image:</p>
                                    <img src="<?php echo SITE_URL . '/' . $service['image']; ?>" alt="Current Image"
                                        class="img-thumbnail" style="max-height: 150px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="delete_image" value="1"
                                            id="delete_image">
                                        <label class="form-check-label text-danger" for="delete_image">
                                            Delete this image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"
                                value="<?php echo $service ? $service['display_order'] : '0'; ?>" min="0">
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$service || $service['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_service' : 'edit_service'; ?>"
                        class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Service' : 'Update Service'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/services.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Icon Preview Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const iconInput = document.getElementById('icon');
            const iconPreview = document.getElementById('icon-preview');

            iconInput.addEventListener('input', function () {
                iconPreview.className = this.value || 'fas fa-cog';
            });
        });
    </script>
<?php else: ?>
    <!-- Services List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Services</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/services.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Service
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($services)): ?>
                <div class="alert alert-info">
                    No services found. Click the "Add New Service" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td><i class="<?php echo $service['icon']; ?> fa-2x"></i></td>
                                    <td><?php echo $service['title']; ?></td>
                                    <td><?php echo substr($service['description'], 0, 100) . (strlen($service['description']) > 100 ? '...' : ''); ?>
                                    </td>
                                    <td><?php echo $service['display_order']; ?></td>
                                    <td>
                                        <?php if ($service['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/services.php?action=edit&id=<?php echo $service['id']; ?>"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" name="delete_service" class="btn btn-sm btn-danger confirm-delete"
                                                data-bs-toggle="tooltip" title="Delete">
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