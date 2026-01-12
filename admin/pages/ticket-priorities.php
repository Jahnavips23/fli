<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'ticket-priorities';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_priority'])) {
        // Check if priority is in use
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_tickets WHERE priority_id = :id");
            $stmt->bindParam(':id', $_POST['delete_priority']);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Cannot delete priority because it is currently in use by one or more tickets.";
            } else {
                // Delete priority
                $stmt = $db->prepare("DELETE FROM ticket_priorities WHERE id = :id");
                $stmt->bindParam(':id', $_POST['delete_priority']);
                $stmt->execute();
                $success_message = "Priority deleted successfully.";
            }
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting priority: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_priority'])) {
        // Get form data
        $name = trim($_POST['name']);
        $color = trim($_POST['color']);
        $display_order = (int)$_POST['display_order'];
        
        // Validate required fields
        if (empty($name)) {
            $error_message = "Please enter a priority name.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing priority
                    $stmt = $db->prepare("
                        UPDATE ticket_priorities 
                        SET name = :name, 
                            color = :color, 
                            display_order = :display_order
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Insert new priority
                    $stmt = $db->prepare("
                        INSERT INTO ticket_priorities 
                        (name, color, display_order) 
                        VALUES 
                        (:name, :color, :display_order)
                    ");
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':color', $color);
                $stmt->bindParam(':display_order', $display_order);
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Priority updated successfully.";
                } else {
                    $success_message = "Priority added successfully.";
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving priority: " . $e->getMessage();
            }
        }
    }
}

// Get priority data for edit
$priority = [];
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM ticket_priorities WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $priority = $stmt->fetch();
        
        if (!$priority) {
            $error_message = "Priority not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving priority: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all priorities for listing
$priorities = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM ticket_priorities ORDER BY display_order ASC, name ASC");
        $stmt->execute();
        $priorities = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving priorities: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php echo $action === 'list' ? 'Ticket Priorities' : ($action === 'add' ? 'Add Priority' : 'Edit Priority'); ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/ticket-priorities.php">Ticket Priorities</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo $action === 'list' ? 'Ticket Priorities' : ($action === 'add' ? 'Add Priority' : 'Edit Priority'); ?>
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
                    <h3 class="card-title mb-0">Ticket Priorities</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/ticket-priorities.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Priority
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
                                    <th>Display Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($priorities)): ?>
                                    <?php foreach ($priorities as $priority): ?>
                                        <tr>
                                            <td><?php echo $priority['id']; ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $priority['color']; ?>; color: #fff;">
                                                    <?php echo $priority['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="width: 30px; height: 30px; background-color: <?php echo $priority['color']; ?>; border-radius: 4px;"></div>
                                                <small><?php echo $priority['color']; ?></small>
                                            </td>
                                            <td><?php echo $priority['display_order']; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/ticket-priorities.php?action=edit&id=<?php echo $priority['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $priority['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $priority['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $priority['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $priority['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the priority: <strong><?php echo $priority['name']; ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_priority" value="<?php echo $priority['id']; ?>">
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
                                        <td colspan="5" class="text-center">No ticket priorities found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add/Edit Priority Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $action === 'add' ? 'Add New Priority' : 'Edit Priority'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Priority Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($priority['name']) ? $priority['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="<?php echo isset($priority['color']) ? $priority['color'] : '#3498db'; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($priority['display_order']) ? $priority['display_order'] : '0'; ?>" min="0">
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_priority" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Priority
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-priorities.php" class="btn btn-secondary ms-2">
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