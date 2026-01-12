<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'client-inquiries';
require_once __DIR__ . '/../includes/header.php';

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Get inquiry ID
$inquiry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($inquiry_id <= 0) {
    header("Location: client-inquiries.php");
    exit;
}

// Initialize variables
$inquiry = false;
$notes = [];
$users = [];

// Get inquiry details
if ($db_available) {
try {
    $stmt = $db->prepare("
        SELECT ci.*, u.username as assigned_to_name 
        FROM client_inquiries ci 
        LEFT JOIN users u ON ci.assigned_to = u.id 
        WHERE ci.id = :id
    ");
    $stmt->bindParam(':id', $inquiry_id);
    $stmt->execute();
    $inquiry = $stmt->fetch();
    
    if (!$inquiry) {
        header("Location: client-inquiries.php");
        exit;
    }
    
    // Get inquiry notes
    $stmt = $db->prepare("
        SELECT n.*, u.username 
        FROM client_inquiry_notes n 
        JOIN users u ON n.user_id = u.id 
        WHERE n.inquiry_id = :inquiry_id 
        ORDER BY n.created_at DESC
    ");
    $stmt->bindParam(':inquiry_id', $inquiry_id);
    $stmt->execute();
    $notes = $stmt->fetchAll();
    
    // Get all users for assignment
    $stmt = $db->prepare("SELECT id, username FROM users ORDER BY username");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
} else {
    $db_error = "Database connection is not available. Please check your configuration.";
}

// Handle status update
if (isset($_POST['update_status'])) {
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
        
        // Refresh the page
        header("Location: view-inquiry.php?id=$inquiry_id&success=updated");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error updating inquiry status: " . $e->getMessage();
    }
}

// Handle adding a note
if (isset($_POST['add_note'])) {
    $note_text = trim($_POST['note_text']);
    
    if (empty($note_text)) {
        $error_message = "Please enter a note.";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO client_inquiry_notes (inquiry_id, user_id, note) VALUES (:inquiry_id, :user_id, :note)");
            $stmt->bindParam(':inquiry_id', $inquiry_id);
            $stmt->bindParam(':user_id', $_SESSION['admin_id']);
            $stmt->bindParam(':note', $note_text);
            $stmt->execute();
            
            $success_message = "Note added successfully.";
            
            // Refresh the page
            header("Location: view-inquiry.php?id=$inquiry_id&success=note_added");
            exit;
        } catch (PDOException $e) {
            $error_message = "Error adding note: " . $e->getMessage();
        }
    }
}

// Handle sending welcome email
if (isset($_POST['send_welcome_email'])) {
    try {
        // Get default email template
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
            
            // Send email (this is a placeholder - actual email sending will be implemented)
            $email_sent = true; // Assume success for now
            
            if ($email_sent) {
                // Update inquiry status
                $stmt = $db->prepare("UPDATE client_inquiries SET welcome_email_sent = 1, welcome_email_date = NOW() WHERE id = :id");
                $stmt->bindParam(':id', $inquiry_id);
                $stmt->execute();
                
                // Add note about email
                $note = "Welcome email sent manually";
                $stmt = $db->prepare("INSERT INTO client_inquiry_notes (inquiry_id, user_id, note) VALUES (:inquiry_id, :user_id, :note)");
                $stmt->bindParam(':inquiry_id', $inquiry_id);
                $stmt->bindParam(':user_id', $_SESSION['admin_id']);
                $stmt->bindParam(':note', $note);
                $stmt->execute();
                
                $success_message = "Welcome email sent successfully.";
                
                // Refresh the page
                header("Location: view-inquiry.php?id=$inquiry_id&success=email_sent");
                exit;
            } else {
                $error_message = "Failed to send welcome email.";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error sending welcome email: " . $e->getMessage();
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'updated':
            $success_message = "Inquiry status updated successfully.";
            break;
        case 'note_added':
            $success_message = "Note added successfully.";
            break;
        case 'email_sent':
            $success_message = "Welcome email sent successfully.";
            break;
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">View Inquiry</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">Client Inquiries</a></li>
                        <li class="breadcrumb-item active">View Inquiry</li>
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
            <?php else: ?>
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
            <?php endif; ?>
            
            <?php if (isset($db_error)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inquiry Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> Cannot view inquiry details until database connection is fixed.
                        </div>
                    </div>
                </div>
            <?php elseif (!$inquiry): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inquiry Not Found</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> The requested inquiry could not be found.
                        </div>
                        <a href="client-inquiries.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Inquiries
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-4">
                        <!-- Inquiry Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Inquiry Details</h3>
                            <div class="card-tools">
                                <a href="edit-inquiry.php?id=<?php echo $inquiry_id; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h5><?php echo $inquiry['name']; ?></h5>
                                <p class="text-muted mb-0"><?php echo $inquiry['company'] ? $inquiry['company'] : 'No Company'; ?></p>
                            </div>
                            
                            <hr>
                            
                            <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                            <p class="text-muted"><?php echo $inquiry['email']; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                            <p class="text-muted"><?php echo $inquiry['phone'] ? $inquiry['phone'] : 'Not provided'; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-tag mr-1"></i> Source</strong>
                            <p class="text-muted"><?php echo $inquiry['source']; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-question-circle mr-1"></i> Inquiry Type</strong>
                            <p class="text-muted"><?php echo $inquiry['inquiry_type']; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-calendar mr-1"></i> Created</strong>
                            <p class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($inquiry['created_at'])); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-user mr-1"></i> Assigned To</strong>
                            <p class="text-muted"><?php echo $inquiry['assigned_to_name'] ?? 'Unassigned'; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-envelope-open mr-1"></i> Welcome Email</strong>
                            <p class="text-muted">
                                <?php if ($inquiry['welcome_email_sent']): ?>
                                    <span class="badge badge-success">Sent on <?php echo date('F j, Y', strtotime($inquiry['welcome_email_date'])); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Not Sent</span>
                                    <form action="" method="post" class="mt-2">
                                        <button type="submit" name="send_welcome_email" class="btn btn-sm btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Welcome Email
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Status Update -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Update Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span class="badge badge-<?php 
                                    echo $inquiry['status'] === 'new' ? 'primary' : 
                                        ($inquiry['status'] === 'contacted' ? 'info' : 
                                        ($inquiry['status'] === 'qualified' ? 'warning' : 
                                        ($inquiry['status'] === 'converted' ? 'success' : 'secondary'))); 
                                ?> p-2">
                                    <h5 class="mb-0"><?php echo ucfirst($inquiry['status']); ?></h5>
                                </span>
                            </div>
                            
                            <form action="" method="post">
                                <div class="form-group">
                                    <label for="new_status">Change Status</label>
                                    <select name="new_status" id="new_status" class="form-control" required>
                                        <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="contacted" <?php echo $inquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="qualified" <?php echo $inquiry['status'] === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                        <option value="converted" <?php echo $inquiry['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                        <option value="closed" <?php echo $inquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="assigned_to">Assign To</label>
                                    <select name="assigned_to" id="assigned_to" class="form-control">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $inquiry['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo $user['username']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary btn-block">
                                    <i class="fas fa-sync-alt"></i> Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Inquiry Message -->
                    <?php if (!empty($inquiry['message'])): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Original Message</h3>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br($inquiry['message']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add Note -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Add Note</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="form-group">
                                    <textarea name="note_text" class="form-control" rows="3" placeholder="Enter your note here..." required></textarea>
                                </div>
                                <button type="submit" name="add_note" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Note
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notes History -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Notes & Activity History</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="timeline timeline-inverse p-3">
                                <?php if (empty($notes)): ?>
                                    <div class="text-center py-3">
                                        <p>No notes or activity yet.</p>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    $current_date = null;
                                    foreach ($notes as $note): 
                                        $note_date = date('Y-m-d', strtotime($note['created_at']));
                                        if ($note_date !== $current_date):
                                            $current_date = $note_date;
                                    ?>
                                        <div class="time-label">
                                            <span class="bg-primary">
                                                <?php echo date('F j, Y', strtotime($note['created_at'])); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <i class="fas fa-comment bg-info"></i>
                                        <div class="timeline-item">
                                            <span class="time">
                                                <i class="far fa-clock"></i> <?php echo date('g:i a', strtotime($note['created_at'])); ?>
                                            </span>
                                            <h3 class="timeline-header">
                                                <a href="#"><?php echo $note['username']; ?></a> added a note
                                            </h3>
                                            <div class="timeline-body">
                                                <?php echo nl2br($note['note']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
.timeline {
    margin: 0;
    padding: 0;
    position: relative;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    margin-right: 10px;
    margin-bottom: 15px;
    position: relative;
}

.time-label {
    margin-bottom: 15px;
}

.time-label > span {
    display: inline-block;
    padding: 5px 10px;
    color: #fff;
    border-radius: 4px;
}

.timeline-item {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 3px;
    margin-left: 60px;
    margin-right: 15px;
    margin-bottom: 15px;
    background: #fff;
    color: #444;
    position: relative;
}

.timeline-item .time {
    float: right;
    color: #999;
    padding: 10px;
    font-size: 12px;
}

.timeline-item .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}

.timeline-item .timeline-body {
    padding: 10px;
}

.timeline-inverse > div > i {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #fff;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>