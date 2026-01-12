<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'projects';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Generate a unique order ID
function generate_order_id() {
    $prefix = 'FLI';
    $timestamp = substr(time(), -6);
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    return $prefix . $timestamp . $random;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_project'])) {
        // Delete project
        $id = (int)$_POST['delete_project'];
        try {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $success_message = "Project deleted successfully.";
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting project: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_project'])) {
        // Get form data
        $title = trim($_POST['title']);
        $customer_name = trim($_POST['customer_name']);
        $customer_email = trim($_POST['customer_email']);
        $customer_phone = trim($_POST['customer_phone']);
        $description = trim($_POST['description']);
        $status_id = (int)$_POST['status_id'];
        
        // Validate required fields
        if (empty($title) || empty($customer_name) || empty($customer_email) || $status_id <= 0) {
            $error_message = "Please fill in all required fields.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing project
                    $stmt = $db->prepare("
                        UPDATE projects 
                        SET title = :title, 
                            customer_name = :customer_name, 
                            customer_email = :customer_email, 
                            customer_phone = :customer_phone, 
                            description = :description, 
                            status_id = :status_id
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Generate order ID for new project
                    $order_id = generate_order_id();
                    
                    // Insert new project
                    $stmt = $db->prepare("
                        INSERT INTO projects 
                        (order_id, title, customer_name, customer_email, customer_phone, description, status_id) 
                        VALUES 
                        (:order_id, :title, :customer_name, :customer_email, :customer_phone, :description, :status_id)
                    ");
                    $stmt->bindParam(':order_id', $order_id);
                }
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':customer_name', $customer_name);
                $stmt->bindParam(':customer_email', $customer_email);
                $stmt->bindParam(':customer_phone', $customer_phone);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':status_id', $status_id);
                
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Project updated successfully.";
                } else {
                    // Get the new project ID
                    $new_project_id = $db->lastInsertId();
                    
                    // Add initial project update
                    $current_admin_id = $current_admin['id'];
                    $comments = "Project created";
                    
                    $stmt = $db->prepare("
                        INSERT INTO project_updates 
                        (project_id, status_id, comments, created_by) 
                        VALUES 
                        (:project_id, :status_id, :comments, :created_by)
                    ");
                    $stmt->bindParam(':project_id', $new_project_id);
                    $stmt->bindParam(':status_id', $status_id);
                    $stmt->bindParam(':comments', $comments);
                    $stmt->bindParam(':created_by', $current_admin_id);
                    $stmt->execute();
                    
                    $success_message = "Project added successfully with Order ID: " . $order_id;
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving project: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['add_update'])) {
        // Add project update
        $project_id = (int)$_POST['project_id'];
        $status_id = (int)$_POST['status_id'];
        $comments = trim($_POST['comments']);
        $notify_customer = isset($_POST['notify_customer']) ? 1 : 0;
        $current_admin_id = $current_admin['id'];
        
        try {
            // Add update
            $stmt = $db->prepare("
                INSERT INTO project_updates 
                (project_id, status_id, comments, notify_customer, created_by) 
                VALUES 
                (:project_id, :status_id, :comments, :notify_customer, :created_by)
            ");
            $stmt->bindParam(':project_id', $project_id);
            $stmt->bindParam(':status_id', $status_id);
            $stmt->bindParam(':comments', $comments);
            $stmt->bindParam(':notify_customer', $notify_customer);
            $stmt->bindParam(':created_by', $current_admin_id);
            $stmt->execute();
            
            // Update project status
            $stmt = $db->prepare("
                UPDATE projects 
                SET status_id = :status_id, 
                    last_update = CURRENT_TIMESTAMP 
                WHERE id = :id
            ");
            $stmt->bindParam(':status_id', $status_id);
            $stmt->bindParam(':id', $project_id);
            $stmt->execute();
            
            $success_message = "Project update added successfully.";
            
            // If notification is requested, set it up for processing
            if ($notify_customer) {
                // This will be handled by a cron job or background process
                $success_message .= " Customer will be notified of this update.";
            }
            
            $action = 'view';
            $id = $project_id;
        } catch (PDOException $e) {
            $error_message = "Error adding update: " . $e->getMessage();
        }
    }
}

// Get project data for edit/view
$project = [];
$updates = [];
if (($action === 'edit' || $action === 'view') && $id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, s.name as status_name, s.color as status_color 
            FROM projects p
            JOIN project_statuses s ON p.status_id = s.id
            WHERE p.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $project = $stmt->fetch();
        
        if (!$project) {
            $error_message = "Project not found.";
            $action = 'list';
        } else {
            // Get project updates
            $stmt = $db->prepare("
                SELECT pu.*, s.name as status_name, s.color as status_color, u.username as created_by_name
                FROM project_updates pu
                JOIN project_statuses s ON pu.status_id = s.id
                JOIN users u ON pu.created_by = u.id
                WHERE pu.project_id = :project_id
                ORDER BY pu.created_at DESC
            ");
            $stmt->bindParam(':project_id', $id);
            $stmt->execute();
            $updates = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving project: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all projects for listing
$projects = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("
            SELECT p.*, s.name as status_name, s.color as status_color 
            FROM projects p
            JOIN project_statuses s ON p.status_id = s.id
            ORDER BY p.last_update DESC
        ");
        $stmt->execute();
        $projects = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving projects: " . $e->getMessage();
    }
}

// Get all statuses for dropdown
$statuses = [];
try {
    $stmt = $db->prepare("SELECT * FROM project_statuses ORDER BY display_order ASC, name ASC");
    $stmt->execute();
    $statuses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error retrieving statuses: " . $e->getMessage();
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
                        echo 'Projects';
                    } elseif ($action === 'add') {
                        echo 'Add Project';
                    } elseif ($action === 'edit') {
                        echo 'Edit Project';
                    } else {
                        echo 'Project Details';
                    }
                    ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/projects.php">Projects</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php 
                        if ($action === 'list') {
                            echo 'Projects';
                        } elseif ($action === 'add') {
                            echo 'Add Project';
                        } elseif ($action === 'edit') {
                            echo 'Edit Project';
                        } else {
                            echo 'Project Details';
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
                    <h3 class="card-title mb-0">Projects</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/projects.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Project
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="projectsTable">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Title</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Last Update</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><strong><?php echo $project['order_id']; ?></strong></td>
                                            <td><?php echo $project['title']; ?></td>
                                            <td>
                                                <?php echo $project['customer_name']; ?><br>
                                                <small><?php echo $project['customer_email']; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $project['status_color']; ?>; color: #fff;">
                                                    <?php echo $project['status_name']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($project['last_update'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/projects.php?action=view&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/projects.php?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-warning me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $project['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $project['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $project['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $project['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the project: <strong><?php echo $project['title']; ?></strong> (Order ID: <?php echo $project['order_id']; ?>)?
                                                                <div class="alert alert-warning mt-2">
                                                                    <i class="fas fa-exclamation-triangle"></i> This will permanently delete all project data and updates.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_project" value="<?php echo $project['id']; ?>">
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
                                        <td colspan="6" class="text-center">No projects found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Project Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $action === 'add' ? 'Add New Project' : 'Edit Project'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Project Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($project['title']) ? $project['title'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Project Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($project['description']) ? $project['description'] : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status_id" name="status_id" required>
                                        <option value="">Select Status</option>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status['id']; ?>" <?php echo (isset($project['status_id']) && $project['status_id'] == $status['id']) ? 'selected' : ($status['is_default'] && $action === 'add' ? 'selected' : ''); ?>>
                                                <?php echo $status['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo isset($project['customer_name']) ? $project['customer_name'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Customer Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo isset($project['customer_email']) ? $project['customer_email'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Customer Phone</label>
                                    <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="<?php echo isset($project['customer_phone']) ? $project['customer_phone'] : ''; ?>">
                                </div>
                                
                                <?php if ($action === 'edit'): ?>
                                <div class="mb-3">
                                    <label class="form-label">Order ID</label>
                                    <input type="text" class="form-control" value="<?php echo $project['order_id']; ?>" readonly>
                                    <small class="form-text text-muted">Order ID cannot be changed</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_project" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Project
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/projects.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'view'): ?>
            <!-- Project Details View -->
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Project Details</h3>
                            <div class="card-tools">
                                <a href="<?php echo ADMIN_URL; ?>/pages/projects.php?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit Project
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="mb-1"><?php echo $project['title']; ?></h5>
                                <span class="badge" style="background-color: <?php echo $project['status_color']; ?>; color: #fff;">
                                    <?php echo $project['status_name']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Order ID:</strong> <?php echo $project['order_id']; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Customer:</strong><br>
                                <?php echo $project['customer_name']; ?><br>
                                <a href="mailto:<?php echo $project['customer_email']; ?>"><?php echo $project['customer_email']; ?></a><br>
                                <?php if (!empty($project['customer_phone'])): ?>
                                    <?php echo $project['customer_phone']; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Description:</strong><br>
                                <?php echo nl2br($project['description']); ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($project['created_at'])); ?><br>
                                <strong>Last Update:</strong> <?php echo date('M d, Y H:i', strtotime($project['last_update'])); ?>
                            </div>
                            
                            <div class="mt-4">
                                <a href="<?php echo SITE_URL; ?>/track-project.php?id=<?php echo $project['order_id']; ?>" target="_blank" class="btn btn-info">
                                    <i class="fas fa-external-link-alt me-1"></i> View Public Tracking Page
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Update Form -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Add Project Update</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status_id" name="status_id" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status['id']; ?>" <?php echo ($project['status_id'] == $status['id']) ? 'selected' : ''; ?>>
                                                <?php echo $status['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comments" class="form-label">Comments <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notify_customer" name="notify_customer">
                                    <label class="form-check-label" for="notify_customer">Notify Customer</label>
                                    <small class="form-text text-muted d-block">Send an email notification to the customer about this update.</small>
                                </div>
                                
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                <button type="submit" name="add_update" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Update
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <!-- Project Updates -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Project Updates</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($updates)): ?>
                                <div class="timeline">
                                    <?php foreach ($updates as $update): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker" style="background-color: <?php echo $update['status_color']; ?>;"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <span class="badge" style="background-color: <?php echo $update['status_color']; ?>; color: #fff;">
                                                        <?php echo $update['status_name']; ?>
                                                    </span>
                                                    <span class="timeline-date">
                                                        <?php echo date('M d, Y H:i', strtotime($update['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <div class="timeline-body">
                                                    <?php echo nl2br($update['comments']); ?>
                                                </div>
                                                <div class="timeline-footer">
                                                    <small>
                                                        By: <?php echo $update['created_by_name']; ?>
                                                        <?php if ($update['notify_customer']): ?>
                                                            | <i class="fas fa-envelope"></i> Customer notified
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-center">No updates found for this project.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    display: flex;
}

.timeline-marker {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 15px;
    margin-top: 5px;
    flex-shrink: 0;
}

.timeline-content {
    background-color: #f8f9fa;
    border-radius: 4px;
    padding: 15px;
    flex-grow: 1;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.timeline-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.timeline-body {
    margin-bottom: 10px;
}

.timeline-footer {
    color: #6c757d;
    border-top: 1px solid #e9ecef;
    padding-top: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    if (document.getElementById('projectsTable')) {
        $('#projectsTable').DataTable({
            "order": [[4, "desc"]], // Sort by last update by default
            "pageLength": 25
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>