<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'project-statuses';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_status'])) {
        // Check if status is in use
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM projects WHERE status_id = :id");
            $stmt->bindParam(':id', $_POST['delete_status']);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Cannot delete status because it is currently in use by one or more projects.";
            } else {
                // Delete status
                $stmt = $db->prepare("DELETE FROM project_statuses WHERE id = :id");
                $stmt->bindParam(':id', $_POST['delete_status']);
                $stmt->execute();
                $success_message = "Status deleted successfully.";
            }
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting status: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_status'])) {
        // Get form data
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $color = trim($_POST['color']);
        $display_order = (int)$_POST['display_order'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // Validate required fields
        if (empty($name)) {
            $error_message = "Please enter a status name.";
        } else {
            try {
                // If this is set as default, unset any existing default
                if ($is_default) {
                    $stmt = $db->prepare("UPDATE project_statuses SET is_default = 0");
                    $stmt->execute();
                }
                
                if ($id > 0) {
                    // Update existing status
                    $stmt = $db->prepare("
                        UPDATE project_statuses 
                        SET name = :name, 
                            description = :description, 
                            color = :color, 
                            display_order = :display_order, 
                            is_default = :is_default 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Insert new status
                    $stmt = $db->prepare("
                        INSERT INTO project_statuses 
                        (name, description, color, display_order, is_default) 
                        VALUES 
                        (:name, :description, :color, :display_order, :is_default)
                    ");
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':color', $color);
                $stmt->bindParam(':display_order', $display_order);
                $stmt->bindParam(':is_default', $is_default);
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Status updated successfully.";
                } else {
                    $success_message = "Status added successfully.";
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving status: " . $e->getMessage();
            }
        }
    }
}

// Get status data for edit
$status = [];
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM project_statuses WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $status = $stmt->fetch();
        
        if (!$status) {
            $error_message = "Status not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving status: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all statuses for listing
$statuses = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM project_statuses ORDER BY display_order ASC, name ASC");
        $stmt->execute();
        $statuses = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving statuses: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php echo $action === 'list' ? 'Project Statuses' : ($action === 'add' ? 'Add Status' : 'Edit Status'); ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/project-statuses.php">Project Statuses</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo $action === 'list' ? 'Project Statuses' : ($action === 'add' ? 'Add Status' : 'Edit Status'); ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Project Statuses</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/project-statuses.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Status
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Color</th>
                                    <th>Description</th>
                                    <th>Order</th>
                                    <th>Default</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($statuses)): ?>
                                    <?php foreach ($statuses as $status): ?>
                                        <tr>
                                            <td><?php echo $status['id']; ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $status['color']; ?>; color: #fff;">
                                                    <?php echo $status['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="width: 30px; height: 30px; background-color: <?php echo $status['color']; ?>; border-radius: 4px;"></div>
                                                <small><?php echo $status['color']; ?></small>
                                            </td>
                                            <td><?php echo $status['description']; ?></td>
                                            <td><?php echo $status['display_order']; ?></td>
                                            <td>
                                                <?php if ($status['is_default']): ?>
                                                    <span class="badge bg-success">Default</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/project-statuses.php?action=edit&id=<?php echo $status['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $status['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $status['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $status['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $status['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the status: <strong><?php echo $status['name']; ?></strong>?
                                                                <?php if ($status['is_default']): ?>
                                                                <div class="alert alert-warning mt-2">
                                                                    <i class="fas fa-exclamation-triangle"></i> Warning: This is the default status. Deleting it may cause issues.
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_status" value="<?php echo $status['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No project statuses found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add/Edit Status Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $action === 'add' ? 'Add New Status' : 'Edit Status'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($status['name']) ? $status['name'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($status['description']) ? $status['description'] : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo isset($status['color']) ? $status['color'] : '#3498db'; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($status['display_order']) ? $status['display_order'] : '0'; ?>" min="0">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default" <?php echo (isset($status['is_default']) && $status['is_default']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_default">Set as Default Status</label>
                                    <small class="form-text text-muted d-block">This status will be automatically assigned to new projects.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_status" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Status
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/project-statuses.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>