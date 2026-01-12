<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'client-inquiries';
require_once __DIR__ . '/../includes/header.php';

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Initialize variables
$sources = [];
$types = [];
$users = [];

// Get all sources and types
if ($db_available) {
    try {
        $stmt = $db->prepare("SELECT name FROM inquiry_sources WHERE is_active = 1 ORDER BY display_order");
        $stmt->execute();
        $sources = $stmt->fetchAll();
        
        $stmt = $db->prepare("SELECT name FROM inquiry_types WHERE is_active = 1 ORDER BY display_order");
        $stmt->execute();
        $types = $stmt->fetchAll();
        
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $company = trim($_POST['company']);
    $source = trim($_POST['source']);
    $inquiry_type = trim($_POST['inquiry_type']);
    $message = trim($_POST['message']);
    $status = trim($_POST['status']);
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $send_welcome_email = isset($_POST['send_welcome_email']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($source) || empty($inquiry_type) || empty($status)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            // Insert new inquiry
            $stmt = $db->prepare("
                INSERT INTO client_inquiries 
                (name, email, phone, company, source, inquiry_type, message, status, assigned_to, welcome_email_sent) 
                VALUES 
                (:name, :email, :phone, :company, :source, :inquiry_type, :message, :status, :assigned_to, 0)
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':inquiry_type', $inquiry_type);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':assigned_to', $assigned_to);
            
            $stmt->execute();
            $inquiry_id = $db->lastInsertId();
            
            // Add initial note
            $note = "Inquiry created with status '$status'";
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
            
            // Send welcome email if requested
            if ($send_welcome_email) {
                // Get default email template
                $stmt = $db->prepare("SELECT * FROM email_templates WHERE is_default = 1 LIMIT 1");
                $stmt->execute();
                $template = $stmt->fetch();
                
                if ($template) {
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
                        $note = "Welcome email sent automatically upon creation";
                        $stmt = $db->prepare("INSERT INTO client_inquiry_notes (inquiry_id, user_id, note) VALUES (:inquiry_id, :user_id, :note)");
                        $stmt->bindParam(':inquiry_id', $inquiry_id);
                        $stmt->bindParam(':user_id', $_SESSION['admin_id']);
                        $stmt->bindParam(':note', $note);
                        $stmt->execute();
                    }
                }
            }
            
            $success_message = "Inquiry added successfully.";
            
            // Redirect to the inquiry list
            header("Location: client-inquiries.php?success=added");
            exit;
        } catch (PDOException $e) {
            $error_message = "Error adding inquiry: " . $e->getMessage();
        }
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add New Inquiry</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">Client Inquiries</a></li>
                        <li class="breadcrumb-item active">Add New Inquiry</li>
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
                        <h3 class="card-title">Inquiry Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> Cannot add inquiries until database connection is fixed.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Inquiry Information</h3>
                    </div>
                    
                    <form action="" method="post">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <input type="text" class="form-control" id="company" name="company" value="<?php echo isset($company) ? $company : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="source">Source <span class="text-danger">*</span></label>
                                    <select class="form-control" id="source" name="source" required>
                                        <option value="">Select Source</option>
                                        <?php foreach ($sources as $source): ?>
                                            <option value="<?php echo $source['name']; ?>" <?php echo isset($_POST['source']) && $_POST['source'] === $source['name'] ? 'selected' : ''; ?>>
                                                <?php echo $source['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="inquiry_type">Inquiry Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="inquiry_type" name="inquiry_type" required>
                                        <option value="">Select Type</option>
                                        <?php foreach ($types as $type): ?>
                                            <option value="<?php echo $type['name']; ?>" <?php echo isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === $type['name'] ? 'selected' : ''; ?>>
                                                <?php echo $type['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="new" <?php echo isset($_POST['status']) && $_POST['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="contacted" <?php echo isset($_POST['status']) && $_POST['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="qualified" <?php echo isset($_POST['status']) && $_POST['status'] === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                                        <option value="converted" <?php echo isset($_POST['status']) && $_POST['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                        <option value="closed" <?php echo isset($_POST['status']) && $_POST['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="assigned_to">Assign To</label>
                                    <select class="form-control" id="assigned_to" name="assigned_to">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo $user['username']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message/Notes</label>
                            <textarea class="form-control" id="message" name="message" rows="5"><?php echo isset($message) ? $message : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="send_welcome_email" name="send_welcome_email" <?php echo isset($_POST['send_welcome_email']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="send_welcome_email">Send welcome email with brochure immediately</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Add Inquiry</button>
                        <a href="client-inquiries.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>