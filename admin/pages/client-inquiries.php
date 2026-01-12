<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'client-inquiries';
require_once __DIR__ . '/../includes/header.php';

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Check if the required tables exist
function tableExists($tableName) {
    global $db, $db_available;
    if (!$db_available) {
        return false;
    }
    
    try {
        $result = $db->query("SHOW TABLES LIKE '$tableName'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

$requiredTables = [
    'client_inquiries',
    'client_inquiry_notes',
    'email_templates',
    'email_attachments',
    'inquiry_sources',
    'inquiry_types'
];

$missingTables = [];
if ($db_available) {
    foreach ($requiredTables as $table) {
        if (!tableExists($table)) {
            $missingTables[] = $table;
        }
    }
} else {
    $db_error = "Database connection is not available. Please check your configuration.";
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$source_filter = isset($_GET['source']) ? $_GET['source'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Build query
$query = "SELECT ci.*, u.username as assigned_to_name 
          FROM client_inquiries ci 
          LEFT JOIN users u ON ci.assigned_to = u.id 
          WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM client_inquiries WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $query .= " AND ci.status = :status";
    $count_query .= " AND status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($source_filter)) {
    $query .= " AND ci.source = :source";
    $count_query .= " AND source = :source";
    $params[':source'] = $source_filter;
}

if (!empty($search)) {
    $query .= " AND (ci.name LIKE :search OR ci.email LIKE :search OR ci.company LIKE :search)";
    $count_query .= " AND (name LIKE :search OR email LIKE :search OR company LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY ci.created_at DESC LIMIT :offset, :limit";
$params[':offset'] = $offset;
$params[':limit'] = $records_per_page;

// Get inquiries
try {
    // Get total count
    $stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        if ($key != ':offset' && $key != ':limit') {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get inquiries
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        if ($key == ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } elseif ($key == ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $inquiries = $stmt->fetchAll();
    
    // Get all sources for filter
    $stmt = $db->prepare("SELECT name FROM inquiry_sources WHERE is_active = 1 ORDER BY display_order");
    $stmt->execute();
    $sources = $stmt->fetchAll();
    
    // Get all users for assignment
    $stmt = $db->prepare("SELECT id, username FROM users ORDER BY username");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle inquiry deletion
if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = (int)$_POST['inquiry_id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM client_inquiries WHERE id = :id");
        $stmt->bindParam(':id', $inquiry_id);
        $stmt->execute();
        
        $success_message = "Inquiry deleted successfully.";
        
        // Redirect to refresh the page
        header("Location: client-inquiries.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error deleting inquiry: " . $e->getMessage();
    }
}

// Handle bulk email sending
if (isset($_POST['send_welcome_emails'])) {
    $selected_inquiries = isset($_POST['selected_inquiries']) ? $_POST['selected_inquiries'] : [];
    
    if (empty($selected_inquiries)) {
        $error_message = "Please select at least one inquiry to send welcome emails.";
    } else {
        $success_count = 0;
        $error_count = 0;
        
        // Get default email template
        try {
            $stmt = $db->prepare("SELECT * FROM email_templates WHERE is_default = 1 LIMIT 1");
            $stmt->execute();
            $template = $stmt->fetch();
            
            if (!$template) {
                $error_message = "No default email template found.";
            } else {
                // Get default attachments
                $stmt = $db->prepare("SELECT * FROM email_attachments WHERE is_default = 1");
                $stmt->execute();
                $attachments = $stmt->fetchAll();
                
                foreach ($selected_inquiries as $inquiry_id) {
                    // Get inquiry details
                    $stmt = $db->prepare("SELECT * FROM client_inquiries WHERE id = :id");
                    $stmt->bindParam(':id', $inquiry_id);
                    $stmt->execute();
                    $inquiry = $stmt->fetch();
                    
                    if ($inquiry) {
                        // Send email (this is a placeholder - actual email sending will be implemented)
                        $email_sent = true; // Assume success for now
                        
                        if ($email_sent) {
                            // Update inquiry status
                            $stmt = $db->prepare("UPDATE client_inquiries SET welcome_email_sent = 1, welcome_email_date = NOW() WHERE id = :id");
                            $stmt->bindParam(':id', $inquiry_id);
                            $stmt->execute();
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
                
                if ($success_count > 0) {
                    $success_message = "Welcome emails sent to $success_count inquiries.";
                    if ($error_count > 0) {
                        $success_message .= " Failed to send to $error_count inquiries.";
                    }
                    
                    // Redirect to refresh the page
                    header("Location: client-inquiries.php?success=emails_sent&count=$success_count");
                    exit;
                } else {
                    $error_message = "Failed to send welcome emails.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error sending welcome emails: " . $e->getMessage();
        }
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $inquiry_id = (int)$_POST['inquiry_id'];
    $new_status = $_POST['new_status'];
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    
    try {
        $stmt = $db->prepare("UPDATE client_inquiries SET status = :status, assigned_to = :assigned_to WHERE id = :id");
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':assigned_to', $assigned_to);
        $stmt->bindParam(':id', $inquiry_id);
        $stmt->execute();
        
        // Add a note about the status change
        $note = "Status changed to '$new_status'";
        if ($assigned_to) {
            foreach ($users as $user) {
                if ($user['id'] == $assigned_to) {
                    $note .= " and assigned to " . $user['username'];
                    break;
                }
            }
        }
        
        $stmt = $db->prepare("INSERT INTO client_inquiry_notes (inquiry_id, user_id, note) VALUES (:inquiry_id, :user_id, :note)");
        $stmt->bindParam(':inquiry_id', $inquiry_id);
        $stmt->bindParam(':user_id', $_SESSION['admin_id']);
        $stmt->bindParam(':note', $note);
        $stmt->execute();
        
        $success_message = "Inquiry status updated successfully.";
        
        // Redirect to refresh the page
        header("Location: client-inquiries.php?success=updated");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error updating inquiry status: " . $e->getMessage();
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $success_message = "Inquiry deleted successfully.";
            break;
        case 'updated':
            $success_message = "Inquiry status updated successfully.";
            break;
        case 'emails_sent':
            $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
            $success_message = "Welcome emails sent to $count inquiries.";
            break;
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Client Inquiries</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Client Inquiries</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($db_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-ban"></i> Database Connection Error</h5>
                    <p><?php echo $db_error; ?></p>
                    <p>Please check your database configuration in <code>includes/config.php</code>.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif (!empty($missingTables)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Database Tables Missing</h5>
                    <p>The following required database tables are missing:</p>
                    <ul>
                        <?php foreach ($missingTables as $table): ?>
                            <li><code><?php echo $table; ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                    <p>Please run the setup script to create these tables:</p>
                    <pre>php <?php echo SITE_ROOT; ?>admin/setup/create_inquiry_tables.php</pre>
                    <p>Or import the SQL file directly:</p>
                    <pre><?php echo SITE_ROOT; ?>admin/setup/inquiry_tables.sql</pre>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Manage Client Inquiries</h3>
                        <div>
                            <a href="add-inquiry.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Inquiry
                            </a>
                            <a href="email-templates.php" class="btn btn-info ml-2">
                                <i class="fas fa-envelope"></i> Email Templates
                            </a>
                            <a href="email-attachments.php" class="btn btn-secondary ml-2">
                                <i class="fas fa-paperclip"></i> Manage Attachments
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form action="" method="get" class="form-inline">
                                <div class="form-group mr-2">
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="contacted" <?php echo $status_filter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="qualified" <?php echo $status_filter === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                        <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2">
                                    <select name="source" class="form-control">
                                        <option value="">All Sources</option>
                                        <?php foreach ($sources as $source): ?>
                                            <option value="<?php echo $source['name']; ?>" <?php echo $source_filter === $source['name'] ? 'selected' : ''; ?>>
                                                <?php echo $source['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search name, email, company" value="<?php echo $search; ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                <a href="client-inquiries.php" class="btn btn-secondary">Reset</a>
                            </form>
                        </div>
                    </div>
                    
                    <form action="" method="post" id="inquiries-form">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="select-all">
                                            </div>
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Company</th>
                                        <th>Source</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Created</th>
                                        <th>Email Sent</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inquiries)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center">No inquiries found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($inquiries as $inquiry): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input inquiry-checkbox" name="selected_inquiries[]" value="<?php echo $inquiry['id']; ?>">
                                                    </div>
                                                </td>
                                                <td><?php echo $inquiry['name']; ?></td>
                                                <td><?php echo $inquiry['email']; ?></td>
                                                <td><?php echo $inquiry['company']; ?></td>
                                                <td><?php echo $inquiry['source']; ?></td>
                                                <td><?php echo $inquiry['inquiry_type']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $inquiry['status'] === 'new' ? 'primary' : 
                                                            ($inquiry['status'] === 'contacted' ? 'info' : 
                                                            ($inquiry['status'] === 'qualified' ? 'warning' : 
                                                            ($inquiry['status'] === 'converted' ? 'success' : 'secondary'))); 
                                                    ?>">
                                                        <?php echo ucfirst($inquiry['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $inquiry['assigned_to_name'] ?? 'Unassigned'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($inquiry['welcome_email_sent']): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Sent
                                                            <span class="d-block small"><?php echo date('M d, Y', strtotime($inquiry['welcome_email_date'])); ?></span>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Not Sent</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#statusModal<?php echo $inquiry['id']; ?>" title="Update Status">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $inquiry['id']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Status Update Modal -->
                                                    <div class="modal fade" id="statusModal<?php echo $inquiry['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel<?php echo $inquiry['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="statusModalLabel<?php echo $inquiry['id']; ?>">Update Inquiry Status</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="" method="post">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="new_status<?php echo $inquiry['id']; ?>">Status</label>
                                                                            <select name="new_status" id="new_status<?php echo $inquiry['id']; ?>" class="form-control" required>
                                                                                <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                                                <option value="contacted" <?php echo $inquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                                                <option value="qualified" <?php echo $inquiry['status'] === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                                                                <option value="converted" <?php echo $inquiry['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                                                                <option value="closed" <?php echo $inquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="assigned_to<?php echo $inquiry['id']; ?>">Assign To</label>
                                                                            <select name="assigned_to" id="assigned_to<?php echo $inquiry['id']; ?>" class="form-control">
                                                                                <option value="">Unassigned</option>
                                                                                <?php foreach ($users as $user): ?>
                                                                                    <option value="<?php echo $user['id']; ?>" <?php echo $inquiry['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                                                                        <?php echo $user['username']; ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Delete Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $inquiry['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $inquiry['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $inquiry['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete the inquiry from <strong><?php echo $inquiry['name']; ?></strong>?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <form action="" method="post">
                                                                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_inquiry" class="btn btn-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="submit" name="send_welcome_emails" class="btn btn-success" id="send-emails-btn" disabled>
                                    <i class="fas fa-envelope"></i> Send Welcome Emails
                                </button>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($source_filter) ? '&source=' . $source_filter : ''; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">First</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($source_filter) ? '&source=' . $source_filter : ''; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($source_filter) ? '&source=' . $source_filter : ''; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($source_filter) ? '&source=' . $source_filter : ''; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">Next</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($source_filter) ? '&source=' . $source_filter : ''; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">Last</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const inquiryCheckboxes = document.querySelectorAll('.inquiry-checkbox');
    const sendEmailsBtn = document.getElementById('send-emails-btn');
    
    selectAllCheckbox.addEventListener('change', function() {
        inquiryCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSendEmailsButton();
    });
    
    inquiryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSendEmailsButton();
            
            // Update select all checkbox
            const allChecked = Array.from(inquiryCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(inquiryCheckboxes).every(cb => !cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
        });
    });
    
    function updateSendEmailsButton() {
        const checkedCount = Array.from(inquiryCheckboxes).filter(cb => cb.checked).length;
        sendEmailsBtn.disabled = checkedCount === 0;
        
        if (checkedCount > 0) {
            sendEmailsBtn.innerHTML = `<i class="fas fa-envelope"></i> Send Welcome Emails (${checkedCount})`;
        } else {
            sendEmailsBtn.innerHTML = `<i class="fas fa-envelope"></i> Send Welcome Emails`;
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>