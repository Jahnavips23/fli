<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'customer-tickets';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Generate a unique ticket number
function generate_ticket_number() {
    $prefix = 'TKT';
    $timestamp = substr(time(), -6);
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    return $prefix . $timestamp . $random;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_ticket'])) {
        // Delete ticket
        $id = (int)$_POST['delete_ticket'];
        try {
            $stmt = $db->prepare("DELETE FROM customer_tickets WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $success_message = "Ticket deleted successfully.";
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting ticket: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_ticket'])) {
        // Get form data
        $subject = trim($_POST['subject']);
        $description = trim($_POST['description']);
        $customer_name = trim($_POST['customer_name']);
        $customer_email = trim($_POST['customer_email']);
        $customer_phone = trim($_POST['customer_phone']);
        $status_id = (int)$_POST['status_id'];
        $priority_id = (int)$_POST['priority_id'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
        
        // Validate required fields
        if (empty($subject) || empty($description) || empty($customer_name) || empty($customer_email) || $status_id <= 0 || $priority_id <= 0) {
            $error_message = "Please fill in all required fields.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing ticket
                    $stmt = $db->prepare("
                        UPDATE customer_tickets 
                        SET subject = :subject, 
                            description = :description, 
                            customer_name = :customer_name, 
                            customer_email = :customer_email, 
                            customer_phone = :customer_phone, 
                            status_id = :status_id, 
                            priority_id = :priority_id, 
                            category_id = :category_id, 
                            assigned_to = :assigned_to, 
                            project_id = :project_id
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Generate ticket number for new ticket
                    $ticket_number = generate_ticket_number();
                    
                    // Insert new ticket
                    $stmt = $db->prepare("
                        INSERT INTO customer_tickets 
                        (ticket_number, subject, description, customer_name, customer_email, customer_phone, 
                         status_id, priority_id, category_id, assigned_to, project_id) 
                        VALUES 
                        (:ticket_number, :subject, :description, :customer_name, :customer_email, :customer_phone, 
                         :status_id, :priority_id, :category_id, :assigned_to, :project_id)
                    ");
                    $stmt->bindParam(':ticket_number', $ticket_number);
                }
                
                $stmt->bindParam(':subject', $subject);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':customer_name', $customer_name);
                $stmt->bindParam(':customer_email', $customer_email);
                $stmt->bindParam(':customer_phone', $customer_phone);
                $stmt->bindParam(':status_id', $status_id);
                $stmt->bindParam(':priority_id', $priority_id);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':assigned_to', $assigned_to);
                $stmt->bindParam(':project_id', $project_id);
                
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Ticket updated successfully.";
                } else {
                    $success_message = "Ticket created successfully with ticket number: " . $ticket_number;
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving ticket: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['add_reply'])) {
        // Add ticket reply
        $ticket_id = (int)$_POST['ticket_id'];
        $message = trim($_POST['message']);
        $status_id = (int)$_POST['status_id'];
        $user_id = $current_admin['id'];
        
        if (empty($message)) {
            $error_message = "Please enter a reply message.";
        } else {
            try {
                // Begin transaction
                $db->beginTransaction();
                
                // Add reply
                $stmt = $db->prepare("
                    INSERT INTO ticket_replies 
                    (ticket_id, message, is_customer, user_id) 
                    VALUES 
                    (:ticket_id, :message, 0, :user_id)
                ");
                $stmt->bindParam(':ticket_id', $ticket_id);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Update ticket status and last reply time
                $stmt = $db->prepare("
                    UPDATE customer_tickets 
                    SET status_id = :status_id, 
                        last_reply_at = CURRENT_TIMESTAMP 
                    WHERE id = :id
                ");
                $stmt->bindParam(':status_id', $status_id);
                $stmt->bindParam(':id', $ticket_id);
                $stmt->execute();
                
                // Commit transaction
                $db->commit();
                
                $success_message = "Reply added successfully.";
                $action = 'view';
                $id = $ticket_id;
            } catch (PDOException $e) {
                // Rollback transaction on error
                $db->rollBack();
                $error_message = "Error adding reply: " . $e->getMessage();
            }
        }
    }
}

// Get ticket data for edit/view
$ticket = [];
$replies = [];
if (($action === 'edit' || $action === 'view') && $id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT t.*, 
                   s.name as status_name, s.color as status_color, 
                   p.name as priority_name, p.color as priority_color,
                   c.name as category_name,
                   u.username as assigned_to_name,
                   pr.title as project_title, pr.order_id as project_order_id
            FROM customer_tickets t
            LEFT JOIN ticket_statuses s ON t.status_id = s.id
            LEFT JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN ticket_categories c ON t.category_id = c.id
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN projects pr ON t.project_id = pr.id
            WHERE t.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ticket = $stmt->fetch();
        
        if (!$ticket) {
            $error_message = "Ticket not found.";
            $action = 'list';
        } else {
            // Get ticket replies
            $stmt = $db->prepare("
                SELECT r.*, u.username
                FROM ticket_replies r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :ticket_id
                ORDER BY r.created_at ASC
            ");
            $stmt->bindParam(':ticket_id', $id);
            $stmt->execute();
            $replies = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving ticket: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all tickets for listing
$tickets = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("
            SELECT t.*, 
                   s.name as status_name, s.color as status_color, 
                   p.name as priority_name, p.color as priority_color,
                   c.name as category_name,
                   u.username as assigned_to_name
            FROM customer_tickets t
            LEFT JOIN ticket_statuses s ON t.status_id = s.id
            LEFT JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN ticket_categories c ON t.category_id = c.id
            LEFT JOIN users u ON t.assigned_to = u.id
            ORDER BY t.last_reply_at DESC
        ");
        $stmt->execute();
        $tickets = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving tickets: " . $e->getMessage();
    }
}

// Get all statuses for dropdown
$statuses = [];
try {
    $stmt = $db->prepare("SELECT * FROM ticket_statuses ORDER BY display_order ASC, name ASC");
    $stmt->execute();
    $statuses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving statuses: " . $e->getMessage();
}

// Get all priorities for dropdown
$priorities = [];
try {
    $stmt = $db->prepare("SELECT * FROM ticket_priorities ORDER BY display_order ASC, name ASC");
    $stmt->execute();
    $priorities = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving priorities: " . $e->getMessage();
}

// Get all categories for dropdown
$categories = [];
try {
    $stmt = $db->prepare("SELECT * FROM ticket_categories ORDER BY display_order ASC, name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving categories: " . $e->getMessage();
}

// Get all users for dropdown
$users = [];
try {
    $stmt = $db->prepare("SELECT id, username FROM users ORDER BY username ASC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving users: " . $e->getMessage();
}

// Get all projects for dropdown
$projects = [];
try {
    $stmt = $db->prepare("SELECT id, title, order_id FROM projects ORDER BY title ASC");
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving projects: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php 
                    if ($action === 'list') {
                        echo 'Customer Tickets';
                    } elseif ($action === 'add') {
                        echo 'Create New Ticket';
                    } elseif ($action === 'edit') {
                        echo 'Edit Ticket';
                    } else {
                        echo 'Ticket Details';
                    }
                    ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php">Customer Tickets</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php 
                        if ($action === 'list') {
                            echo 'Customer Tickets';
                        } elseif ($action === 'add') {
                            echo 'Create New Ticket';
                        } elseif ($action === 'edit') {
                            echo 'Edit Ticket';
                        } else {
                            echo 'Ticket Details';
                        }
                        ?>
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
                    <h3 class="card-title mb-0">Customer Tickets</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create New Ticket
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="ticketsTable">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Subject</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Category</th>
                                    <th>Assigned To</th>
                                    <th>Last Reply</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tickets)): ?>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><strong><?php echo $ticket['ticket_number']; ?></strong></td>
                                            <td><?php echo $ticket['subject']; ?></td>
                                            <td>
                                                <?php echo $ticket['customer_name']; ?><br>
                                                <small><?php echo $ticket['customer_email']; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $ticket['status_color']; ?>; color: #fff;">
                                                    <?php echo $ticket['status_name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $ticket['priority_color']; ?>; color: #fff;">
                                                    <?php echo $ticket['priority_name']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $ticket['category_name'] ?? 'N/A'; ?></td>
                                            <td><?php echo $ticket['assigned_to_name'] ?? 'Unassigned'; ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($ticket['last_reply_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php?action=view&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php?action=edit&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $ticket['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $ticket['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $ticket['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $ticket['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the ticket: <strong><?php echo $ticket['subject']; ?></strong> (Ticket #: <?php echo $ticket['ticket_number']; ?>)?
                                                                <div class="alert alert-warning mt-2">
                                                                    <i class="fas fa-exclamation-triangle"></i> This will permanently delete all ticket data and replies.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_ticket" value="<?php echo $ticket['id']; ?>">
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
                                        <td colspan="9" class="text-center">No tickets found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Ticket Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $action === 'add' ? 'Create New Ticket' : 'Edit Ticket'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($ticket['subject']) ? $ticket['subject'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($ticket['description']) ? $ticket['description'] : ''; ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status_id" name="status_id" required>
                                                <option value="">Select Status</option>
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?php echo $status['id']; ?>" <?php echo (isset($ticket['status_id']) && $ticket['status_id'] == $status['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $status['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="priority_id" class="form-label">Priority <span class="text-danger">*</span></label>
                                            <select class="form-select" id="priority_id" name="priority_id" required>
                                                <option value="">Select Priority</option>
                                                <?php foreach ($priorities as $priority): ?>
                                                    <option value="<?php echo $priority['id']; ?>" <?php echo (isset($ticket['priority_id']) && $ticket['priority_id'] == $priority['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $priority['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($ticket['category_id']) && $ticket['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $category['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="assigned_to" class="form-label">Assign To</label>
                                            <select class="form-select" id="assigned_to" name="assigned_to">
                                                <option value="">Unassigned</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($ticket['assigned_to']) && $ticket['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $user['username']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Related Project</label>
                                    <select class="form-select" id="project_id" name="project_id">
                                        <option value="">None</option>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?php echo $project['id']; ?>" <?php echo (isset($ticket['project_id']) && $ticket['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                                <?php echo $project['title']; ?> (<?php echo $project['order_id']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo isset($ticket['customer_name']) ? $ticket['customer_name'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Customer Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo isset($ticket['customer_email']) ? $ticket['customer_email'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Customer Phone</label>
                                    <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo isset($ticket['customer_phone']) ? $ticket['customer_phone'] : ''; ?>">
                                </div>
                                
                                <?php if ($action === 'edit'): ?>
                                <div class="mb-3">
                                    <label class="form-label">Ticket Number</label>
                                    <input type="text" class="form-control" value="<?php echo $ticket['ticket_number']; ?>" readonly>
                                    <small class="form-text text-muted">Ticket number cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Created</label>
                                    <input type="text" class="form-control" value="<?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Last Reply</label>
                                    <input type="text" class="form-control" value="<?php echo date('M d, Y H:i', strtotime($ticket['last_reply_at'])); ?>" readonly>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_ticket" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> <?php echo $action === 'add' ? 'Create Ticket' : 'Update Ticket'; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'view'): ?>
            <!-- Ticket Details View -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ticket Information</h3>
                            <div class="card-tools">
                                <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php?action=edit&id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit Ticket
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="mb-1"><?php echo $ticket['subject']; ?></h5>
                                <span class="badge" style="background-color: <?php echo $ticket['status_color']; ?>; color: #fff;">
                                    <?php echo $ticket['status_name']; ?>
                                </span>
                                <span class="badge" style="background-color: <?php echo $ticket['priority_color']; ?>; color: #fff;">
                                    <?php echo $ticket['priority_name']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Ticket #:</strong> <?php echo $ticket['ticket_number']; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Category:</strong> <?php echo $ticket['category_name'] ?? 'N/A'; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Assigned To:</strong> <?php echo $ticket['assigned_to_name'] ?? 'Unassigned'; ?>
                            </div>
                            
                            <?php if (!empty($ticket['project_id'])): ?>
                            <div class="mb-3">
                                <strong>Related Project:</strong><br>
                                <?php echo $ticket['project_title']; ?><br>
                                <small>Order ID: <?php echo $ticket['project_order_id']; ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Customer:</strong><br>
                                <?php echo $ticket['customer_name']; ?><br>
                                <a href="mailto:<?php echo $ticket['customer_email']; ?>"><?php echo $ticket['customer_email']; ?></a><br>
                                <?php if (!empty($ticket['customer_phone'])): ?>
                                    <?php echo $ticket['customer_phone']; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?><br>
                                <strong>Last Reply:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['last_reply_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Reply Form -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Add Reply</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Update Status</label>
                                    <select class="form-select" id="status_id" name="status_id">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status['id']; ?>" <?php echo ($ticket['status_id'] == $status['id']) ? 'selected' : ''; ?>>
                                                <?php echo $status['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                <button type="submit" name="add_reply" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Ticket Description -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ticket Description</h3>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br($ticket['description']); ?>
                        </div>
                    </div>
                    
                    <!-- Ticket Replies -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Conversation</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($replies)): ?>
                                <div class="ticket-replies">
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="ticket-reply <?php echo $reply['is_customer'] ? 'customer-reply' : 'staff-reply'; ?>">
                                            <div class="reply-header">
                                                <div class="reply-author">
                                                    <?php if ($reply['is_customer']): ?>
                                                        <strong><?php echo $ticket['customer_name']; ?></strong> <span class="badge bg-info">Customer</span>
                                                    <?php else: ?>
                                                        <strong><?php echo $reply['username']; ?></strong> <span class="badge bg-secondary">Staff</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="reply-date">
                                                    <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="reply-content">
                                                <?php echo nl2br($reply['message']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-center">No replies yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Ticket replies styles */
.ticket-replies {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ticket-reply {
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.staff-reply {
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.customer-reply {
    background-color: #e8f4f8;
    border-left: 4px solid #17a2b8;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.reply-content {
    white-space: pre-line;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    if (document.getElementById('ticketsTable')) {
        $('#ticketsTable').DataTable({
            "order": [[7, "desc"]], // Sort by last reply by default
            "pageLength": 25
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>