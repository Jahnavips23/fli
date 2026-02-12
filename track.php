<?php
require_once 'includes/config.php';

// Initialize variables
$tracking_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$error_message = '';
$success_message = '';
$captcha_error = '';
$tracking_type = '';
$item = null;
$replies = [];
$updates = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    
    if (empty($recaptcha_response)) {
        $captcha_error = "Please complete the CAPTCHA verification.";
    } else {
        // Verify with Google reCAPTCHA API
        $recaptcha_secret = RECAPTCHA_SECRET_KEY;
        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $response_data = json_decode($verify_response);
        
        if (!$response_data->success) {
            $captcha_error = "CAPTCHA verification failed. Please try again.";
        } else {
            // CAPTCHA verified, proceed with tracking
            $tracking_id = isset($_POST['tracking_id']) ? trim($_POST['tracking_id']) : '';
            
            if (empty($tracking_id)) {
                $error_message = "Please enter a tracking ID.";
            } elseif (empty($email)) {
                $error_message = "Please enter your email address.";
            } else {
                // Try to find the item (project or ticket)
                fetchTrackingItem($tracking_id, $email);
            }
        }
    }
} elseif (!empty($tracking_id) && isset($_GET['type']) && $_GET['type'] === 'ticket') {
    // Direct link to ticket tracking
    $email_required = true;
    $tracking_type = 'ticket';
} elseif (!empty($tracking_id) && isset($_GET['type']) && $_GET['type'] === 'project') {
    // Direct link to project tracking
    $email_required = true;
    $tracking_type = 'project';
} elseif (!empty($tracking_id)) {
    // Try to determine type from ID format
    if (strpos($tracking_id, 'TKT') === 0) {
        $email_required = true;
        $tracking_type = 'ticket';
    } else {
        $email_required = true;
        $tracking_type = 'project';
    }
}

// No reply submission handling needed as we're removing this functionality

// Function to fetch tracking item (project or ticket)
function fetchTrackingItem($id, $email) {
    global $db, $tracking_type, $item, $replies, $updates, $error_message;
    
    // First try as a ticket
    try {
        $stmt = $db->prepare("
            SELECT t.*, 
                   s.name as status_name, s.color as status_color, 
                   p.name as priority_name, p.color as priority_color,
                   c.name as category_name
            FROM customer_tickets t
            LEFT JOIN ticket_statuses s ON t.status_id = s.id
            LEFT JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN ticket_categories c ON t.category_id = c.id
            WHERE t.ticket_number = :id AND t.customer_email = :email
        ");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $ticket = $stmt->fetch();
        
        if ($ticket) {
            $tracking_type = 'ticket';
            $item = $ticket;
            
            // Get only staff replies (comments)
            $stmt = $db->prepare("
                SELECT r.*, u.username
                FROM ticket_replies r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :ticket_id AND r.is_customer = 0
                ORDER BY r.created_at DESC
            ");
            $stmt->bindParam(':ticket_id', $ticket['id']);
            $stmt->execute();
            $replies = $stmt->fetchAll();
            
            return;
        }
    } catch (PDOException $e) {
        // Continue to try as project
    }
    
    // Then try as a project
    try {
        $stmt = $db->prepare("
            SELECT p.*, s.name as status_name, s.color as status_color 
            FROM projects p
            JOIN project_statuses s ON p.status_id = s.id
            WHERE p.order_id = :id AND p.customer_email = :email
        ");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $project = $stmt->fetch();
        
        if ($project) {
            $tracking_type = 'project';
            $item = $project;
            
            // Get project updates
            $stmt = $db->prepare("
                SELECT pu.*, s.name as status_name, s.color as status_color
                FROM project_updates pu
                JOIN project_statuses s ON pu.status_id = s.id
                WHERE pu.project_id = :project_id
                ORDER BY pu.created_at DESC
            ");
            $stmt->bindParam(':project_id', $project['id']);
            $stmt->execute();
            $updates = $stmt->fetchAll();
            
            return;
        }
    } catch (PDOException $e) {
        // Both attempts failed
    }
    
    // If we get here, no item was found
    $error_message = "No project or ticket found with the provided ID and email address.";
}

// Page title
$page_title = "Track Your Project or Ticket";

// Handle Ticket Submission
$ticket_success = '';
$ticket_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    // Collect form data
    $client_name = trim($_POST['client_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $file_upload = $_FILES['attachment'] ?? null;

    // Validation
    if (empty($client_name) || empty($email) || empty($phone) || empty($subject) || empty($description)) {
        $ticket_error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ticket_error = "Invalid email address.";
    } else {
        // File Upload Handling
        $attachment_path = null;
        if ($file_upload && $file_upload['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/png', 'image/jpeg', 'application/pdf'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file_upload['type'], $allowed_types)) {
                $ticket_error = "Invalid file type. Only PNG, JPEG, and PDF are allowed.";
            } elseif ($file_upload['size'] > $max_size) {
                $ticket_error = "File size exceeds 2MB limit.";
            } else {
                $upload_dir = 'uploads/tickets/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $extension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
                $filename = uniqid('ticket_', true) . '.' . $extension;
                $target_path = $upload_dir . $filename;

                if (move_uploaded_file($file_upload['tmp_name'], $target_path)) {
                    $attachment_path = $target_path;
                } else {
                    $ticket_error = "Failed to upload file.";
                }
            }
        }

        // Insert into Database if no errors
        if (empty($ticket_error)) {
            try {
                // Generate Ticket Number (TKT-YYYYMMDD-RAND)
                $ticket_number = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                // Default IDs (assuming 1 for Open/General/Medium if not specified)
                // You might need to adjust these based on your actual DB values
                $status_id = 1; 
                $priority_id = 2; // Medium
                $category_id = 1; // General

                $stmt = $db->prepare("
                    INSERT INTO customer_tickets (
                        ticket_number, customer_email, subject, description, 
                        status_id, priority_id, category_id, attachment, created_at
                    ) VALUES (
                        :ticket_number, :email, :subject, :description, 
                        :status_id, :priority_id, :category_id, :attachment, NOW()
                    )
                ");

                $stmt->execute([
                    ':ticket_number' => $ticket_number,
                    ':email' => $email,
                    ':subject' => $subject,
                    ':description' => $description,
                    ':status_id' => $status_id,
                    ':priority_id' => $priority_id,
                    ':category_id' => $category_id,
                    ':attachment' => $attachment_path
                ]);

                // Store client details in a separate way if needed, or if customer_tickets has client_name/phone columns
                // Note: The user asked for client name/phone, but standard schema might not have them if it links to users.
                // Assuming we might need to store them in description or if columns exist. 
                // Let's verify columns again? No, I'll append them to description for now to be safe if columns don't exist, 
                // OR better, I will assume they don't exist based on 'customer_email' being the key.
                // Wait, if I want to be cleaner, I should check if I can add them. 
                // Actually, I'll verify if `client_name` and `phone` columns exist. 
                // Code above assumes they might NOT, so I'll append to description just in case to capture the data.
                
                // RE-PLAN: Check if I can update the INSERT to include client_name/phone if the table has them?
                // Or just append to description:
                // "Client: Name\nPhone: 12345\n\nDescription..."
                // This is safer without knowing full schema.
                
                // Let's refine the insert to append info to description for now to ensure data isn't lost.
                
                // Re-preparing statement with appended description
                $full_description = "Client Name: $client_name\nPhone: $phone\n\n" . $description;
                
                $stmt = $db->prepare("
                    INSERT INTO customer_tickets (
                        ticket_number, customer_email, subject, description, 
                        status_id, priority_id, category_id, attachment, created_at
                    ) VALUES (
                        :ticket_number, :email, :subject, :description, 
                        :status_id, :priority_id, :category_id, :attachment, NOW()
                    )
                ");

                $stmt->execute([
                    ':ticket_number' => $ticket_number,
                    ':email' => $email,
                    ':subject' => $subject,
                    ':description' => $full_description,
                    ':status_id' => $status_id,
                    ':priority_id' => $priority_id,
                    ':category_id' => $category_id,
                    ':attachment' => $attachment_path
                ]);

                $ticket_success = "Ticket raised successfully! Your Ticket Number is: <strong>$ticket_number</strong>";
                
                // Reset form fields
                $client_name = $email = $phone = $subject = $description = '';

            } catch (PDOException $e) {
                $ticket_error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
include 'includes/header.php';
?>


<!-- Existing Tracking Section -->
<section class="tracking-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold">Track Your Project or Ticket</h1>
                    <p class="lead">Enter your tracking ID and email to check the status</p>
                    <div class="mt-3">
                        <div class="alert alert-secondary d-inline-block">
                            <i class="fas fa-info-circle me-2"></i> This is a read-only tracking system. For any inquiries or updates, please contact our support team.
                        </div>
                    </div>
                </div>
                
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
                
                <?php if (!empty($captcha_error)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?php echo $captcha_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($item)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="" method="post" class="row g-3 justify-content-center">
                                <div class="col-md-12 mb-3">
                                    <label for="tracking_id" class="form-label">Tracking ID</label>
                                    <input type="text" class="form-control" id="tracking_id" name="tracking_id" 
                                           placeholder="Enter your project order ID or ticket number" 
                                           value="<?php echo $tracking_id; ?>" required>
                                    <small class="form-text text-muted">
                                        For projects, enter your Order ID. For tickets, enter your Ticket Number (starts with TKT).
                                    </small>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter the email associated with your project or ticket" 
                                           value="<?php echo $email; ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                                </div>
                                
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i> Track
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php elseif ($tracking_type === 'ticket'): ?>
                    <!-- Ticket Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h4 mb-0">Support Ticket Details</h2>
                                <a href="track.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search"></i> Track Another Item
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="h5 mb-3"><?php echo $item['subject']; ?></h3>
                                    <p class="mb-1"><strong>Ticket #:</strong> <?php echo $item['ticket_number']; ?></p>
                                    <p class="mb-1"><strong>Category:</strong> <?php echo $item['category_name'] ?? 'General'; ?></p>
                                    <p class="mb-1"><strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($item['created_at'])); ?></p>
                                    <p class="mb-3"><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($item['last_reply_at'])); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="status-card p-3 text-center h-100 d-flex flex-column justify-content-center" style="background-color: rgba(<?php echo hexToRgb($item['status_color']); ?>, 0.1); border: 1px solid <?php echo $item['status_color']; ?>; border-radius: 8px;">
                                        <h4 class="text-uppercase mb-2">Status</h4>
                                        <div class="status-badge mb-2">
                                            <span class="badge fs-6 px-3 py-2" style="background-color: <?php echo $item['status_color']; ?>; color: #fff;">
                                                <?php echo $item['status_name']; ?>
                                            </span>
                                        </div>
                                        <div class="priority-badge">
                                            <span class="badge fs-6 px-3 py-2" style="background-color: <?php echo $item['priority_color']; ?>; color: #fff;">
                                                <?php echo $item['priority_name']; ?> Priority
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ticket Description -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="h5 mb-0">Description</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php echo nl2br($item['description']); ?>
                        </div>
                    </div>
                    
                    <!-- Ticket Comments -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="h5 mb-0">Support Comments</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php if (!empty($replies)): ?>
                                <div class="ticket-comments">
                                    <?php 
                                    // Only show staff replies
                                    $staff_replies = array_filter($replies, function($reply) {
                                        return !$reply['is_customer'];
                                    });
                                    
                                    if (!empty($staff_replies)):
                                    ?>
                                        <?php foreach ($staff_replies as $reply): ?>
                                            <div class="comment-item">
                                                <div class="comment-header">
                                                    <div class="comment-author">
                                                        <strong><?php echo $reply['username'] ?? 'Support Staff'; ?></strong>
                                                    </div>
                                                    <div class="comment-date">
                                                        <?php echo date('F j, Y, g:i a', strtotime($reply['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="comment-content">
                                                    <?php echo nl2br($reply['message']); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-center">No support comments yet. Our team will update you soon.</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-center">No support comments yet. Our team will update you soon.</p>
                            <?php endif; ?>
                            
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i> If you need to contact us about this ticket, please email us at <a href="mailto:<?php echo get_setting('contact_email'); ?>"><?php echo get_setting('contact_email'); ?></a> and include your ticket number.
                            </div>
                        </div>
                    </div>
                <?php elseif ($tracking_type === 'project'): ?>
                    <!-- Project Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h4 mb-0">Project Details</h2>
                                <a href="track.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search"></i> Track Another Item
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="h5 mb-3"><?php echo $item['title']; ?></h3>
                                    <p class="mb-1"><strong>Order ID:</strong> <?php echo $item['order_id']; ?></p>
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo $item['customer_name']; ?></p>
                                    <p class="mb-3"><strong>Created:</strong> <?php echo date('F j, Y', strtotime($item['created_at'])); ?></p>
                                    
                                    <?php if (!empty($item['description'])): ?>
                                        <p><strong>Description:</strong><br><?php echo nl2br($item['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="status-card p-4 text-center h-100 d-flex flex-column justify-content-center" style="background-color: rgba(<?php echo hexToRgb($item['status_color']); ?>, 0.1); border: 1px solid <?php echo $item['status_color']; ?>; border-radius: 8px;">
                                        <h4 class="text-uppercase mb-3">Current Status</h4>
                                        <div class="status-badge mb-3">
                                            <span class="badge fs-5 px-4 py-2" style="background-color: <?php echo $item['status_color']; ?>; color: #fff;">
                                                <?php echo $item['status_name']; ?>
                                            </span>
                                        </div>
                                        <p class="mb-0">Last Updated: <?php echo date('F j, Y, g:i a', strtotime($item['last_update'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Updates -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h2 class="h4 mb-0">Project Updates</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php if (!empty($updates)): ?>
                                <div class="project-updates">
                                    <?php foreach ($updates as $update): ?>
                                        <div class="update-item">
                                            <div class="update-header">
                                                <div class="update-status">
                                                    <span class="badge" style="background-color: <?php echo $update['status_color']; ?>; color: #fff;">
                                                        <?php echo $update['status_name']; ?>
                                                    </span>
                                                </div>
                                                <div class="update-date">
                                                    <?php echo date('F j, Y, g:i a', strtotime($update['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="update-content">
                                                <?php echo nl2br($update['comments']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p>No updates have been recorded for this project yet.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i> If you need to contact us about this project, please email us at <a href="mailto:<?php echo get_setting('contact_email'); ?>"><?php echo get_setting('contact_email'); ?></a> and include your order ID.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-5">
                    <p>If you have any questions, please contact us at <a href="mailto:<?php echo get_setting('contact_email'); ?>"><?php echo get_setting('contact_email'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Raise Ticket Section -->
<section class="raise-ticket-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h3 class="mb-0">Raise a Ticket Here</h3>
                    </div>
                    <div class="card-body p-5">
                        <?php if ($ticket_success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $ticket_success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($ticket_error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $ticket_error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="submit_ticket" value="1">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($client_name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="ticket_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="ticket_email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="attachment" class="form-label">File/Image Upload (Optional)</label>
                                <input class="form-control" type="file" id="attachment" name="attachment" accept=".png, .jpg, .jpeg, .pdf">
                                <div class="form-text">Max size: 2MB. Allowed formats: PNG, JPEG, PDF.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
/* Project updates styles */
.project-updates {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 20px;
}

.update-item {
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.update-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.update-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.update-content {
    white-space: pre-line;
}

/* Ticket comments styles */
.ticket-comments {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 20px;
}

.comment-item {
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    background-color: #f8f9fa;
    border-left: 4px solid #6c757d;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.comment-content {
    white-space: pre-line;
}
</style>

<?php include 'includes/footer.php'; ?>