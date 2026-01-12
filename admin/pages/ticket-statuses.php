<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'ticket-statuses';

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
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_tickets WHERE status_id = :id");
            $stmt->bindParam(':id', $_POST['delete_status']);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Cannot delete status because it is currently in use by one or more tickets.";
            } else {
                // Delete status
                $stmt = $db->prepare("DELETE FROM ticket_statuses WHERE id = :id");
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
        $color = trim($_POST['color']);
        $is_closed = isset($_POST['is_closed']) ? 1 : 0;
        $display_order = (int)$_POST['display_order'];
        
        // Validate required fields
        if (empty($name)) {
            $error_message = "Please enter a status name.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing status
                    $stmt = $db->prepare("
                        UPDATE ticket_statuses 
                        SET name = :name, 
                            color = :color, 
                            is_closed = :is_closed,
                            display_order = :display_order
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Insert new status
                    $stmt = $db->prepare("
                        INSERT INTO ticket_statuses 
                        (name, color, is_closed, display_order) 
                        VALUES 
                        (:name, :color, :is_closed, :display_order)
                    ");
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':color', $color);
                $stmt->bindParam(':is_closed', $is_closed);
                $stmt->bindParam(':display_order', $display_order);
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
        $stmt = $db->prepare("SELECT * FROM ticket_statuses WHERE id = :id");
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
        $stmt = $db->prepare("SELECT * FROM ticket_statuses ORDER BY display_order ASC, name ASC");
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
                    <?php echo $action === 'list' ? 'Ticket Statuses' : ($action === 'add' ? 'Add Status' : 'Edit Status'); ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/ticket-statuses.php">Ticket Statuses</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo $action === 'list' ? 'Ticket Statuses' : ($action === 'add' ? 'Add Status' : 'Edit Status'); ?>
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
                    <h3 class="card-title mb-0">Ticket Statuses</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/ticket-statuses.php?action=add" class="btn btn-primary btn-sm">
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
                                    <th>Closed</th>
                                    <th>Display Order</th>
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
                                            <td>
                                                <?php if ($status['is_closed']): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $status['display_order']; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/ticket-statuses.php?action=edit&id=<?php echo $status['id']; ?>" class="btn btn-sm btn-info me-1">
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
                                        <td colspan="6" class="text-center">No ticket statuses found.</td>
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($status['name']) ? $status['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo isset($status['color']) ? $status['color'] : '#3498db'; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($status['display_order']) ? $status['display_order'] : '0'; ?>" min="0">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_closed" name="is_closed" <?php echo (isset($status['is_closed']) && $status['is_closed']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_closed">Closed Status</label>
                            <small class="form-text text-muted d-block">If checked, tickets with this status will be considered closed.</small>
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_status" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Status
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-statuses.php" class="btn btn-secondary ms-2">
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