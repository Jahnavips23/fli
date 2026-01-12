<?php
require_once 'includes/config.php';

// Get the ticket number if provided
$ticket_number = isset($_GET['id']) ? trim($_GET['id']) : '';

// Redirect to the unified tracking page
if (!empty($ticket_number)) {
    header("Location: track.php?id=" . urlencode($ticket_number) . "&type=ticket");
    exit;
} else {
    header("Location: track.php");
    exit;
}

// The code below is kept for reference but will not be executed
// Initialize variables
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$error_message = '';
$ticket = null;
$replies = [];

// If ticket number and email are provided, fetch ticket details
if (!empty($ticket_number) && !empty($email)) {
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
            WHERE t.ticket_number = :ticket_number AND t.customer_email = :email
        ");
        $stmt->bindParam(':ticket_number', $ticket_number);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $ticket = $stmt->fetch();
        
        if ($ticket) {
            // Get ticket replies
            $stmt = $db->prepare("
                SELECT r.*, u.username
                FROM ticket_replies r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :ticket_id
                ORDER BY r.created_at ASC
            ");
            $stmt->bindParam(':ticket_id', $ticket['id']);
            $stmt->execute();
            $replies = $stmt->fetchAll();
            
            // Handle new reply submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reply'])) {
                $message = trim($_POST['message']);
                
                if (empty($message)) {
                    $error_message = "Please enter a reply message.";
                } else {
                    // Add customer reply
                    $stmt = $db->prepare("
                        INSERT INTO ticket_replies 
                        (ticket_id, message, is_customer) 
                        VALUES 
                        (:ticket_id, :message, 1)
                    ");
                    $stmt->bindParam(':ticket_id', $ticket['id']);
                    $stmt->bindParam(':message', $message);
                    $stmt->execute();
                    
                    // Update ticket last reply time
                    $stmt = $db->prepare("
                        UPDATE customer_tickets 
                        SET last_reply_at = CURRENT_TIMESTAMP 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $ticket['id']);
                    $stmt->execute();
                    
                    // Refresh the page to show the new reply
                    header("Location: track-ticket.php?id=" . $ticket_number . "&success=1");
                    exit;
                }
            }
        } else {
            $error_message = "No ticket found with the provided ticket number and email address.";
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving ticket information.";
    }
} elseif (!empty($ticket_number) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If only ticket number is provided, show email form
    $email_required = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($email)) {
    $error_message = "Please enter your email address.";
}

// Check for success message
$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;

// Page title
$page_title = "Track Your Ticket";
include 'includes/header.php';
?>

<section class="track-ticket-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold">Track Your Ticket</h1>
                    <p class="lead">Enter your ticket number and email to check the status of your support request</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Your reply has been added successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($ticket)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="" method="<?php echo isset($email_required) ? 'post' : 'get'; ?>" class="row g-3 justify-content-center">
                                <div class="<?php echo isset($email_required) ? 'col-md-12 mb-3' : 'col-md-5'; ?>">
                                    <label for="id" class="form-label">Ticket Number</label>
                                    <input type="text" class="form-control" id="id" name="id" placeholder="Enter your ticket number" value="<?php echo $ticket_number; ?>" required>
                                </div>
                                
                                <?php if (isset($email_required)): ?>
                                    <input type="hidden" name="id" value="<?php echo $ticket_number; ?>">
                                    <div class="col-md-12 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter the email used to create the ticket" required>
                                    </div>
                                <?php else: ?>
                                    <div class="col-md-4">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="<?php echo isset($email_required) ? 'col-md-12' : 'col-md-3'; ?>">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Track</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Ticket Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h4 mb-0">Ticket Details</h2>
                                <a href="track-ticket.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search"></i> Track Another Ticket
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="h5 mb-3"><?php echo $ticket['subject']; ?></h3>
                                    <p class="mb-1"><strong>Ticket #:</strong> <?php echo $ticket['ticket_number']; ?></p>
                                    <p class="mb-1"><strong>Category:</strong> <?php echo $ticket['category_name'] ?? 'General'; ?></p>
                                    <p class="mb-1"><strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                                    <p class="mb-3"><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($ticket['last_reply_at'])); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <div class="status-card p-3 text-center h-100 d-flex flex-column justify-content-center" style="background-color: rgba(<?php echo hexToRgb($ticket['status_color']); ?>, 0.1); border: 1px solid <?php echo $ticket['status_color']; ?>; border-radius: 8px;">
                                        <h4 class="text-uppercase mb-2">Status</h4>
                                        <div class="status-badge mb-2">
                                            <span class="badge fs-6 px-3 py-2" style="background-color: <?php echo $ticket['status_color']; ?>; color: #fff;">
                                                <?php echo $ticket['status_name']; ?>
                                            </span>
                                        </div>
                                        <div class="priority-badge">
                                            <span class="badge fs-6 px-3 py-2" style="background-color: <?php echo $ticket['priority_color']; ?>; color: #fff;">
                                                <?php echo $ticket['priority_name']; ?> Priority
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
                            <?php echo nl2br($ticket['description']); ?>
                        </div>
                    </div>
                    
                    <!-- Ticket Conversation -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="h5 mb-0">Conversation</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php if (!empty($replies)): ?>
                                <div class="ticket-replies">
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="ticket-reply <?php echo $reply['is_customer'] ? 'customer-reply' : 'staff-reply'; ?>">
                                            <div class="reply-header">
                                                <div class="reply-author">
                                                    <?php if ($reply['is_customer']): ?>
                                                        <strong><?php echo $ticket['customer_name']; ?></strong> <span class="badge bg-info">You</span>
                                                    <?php else: ?>
                                                        <strong><?php echo $reply['username'] ?? 'Support Staff'; ?></strong> <span class="badge bg-secondary">Staff</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="reply-date">
                                                    <?php echo date('F j, Y, g:i a', strtotime($reply['created_at'])); ?>
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
                    
                    <!-- Add Reply Form -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h3 class="h5 mb-0">Add Reply</h3>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="message" class="form-label">Your Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <input type="hidden" name="id" value="<?php echo $ticket_number; ?>">
                                <input type="hidden" name="email" value="<?php echo $ticket['customer_email']; ?>">
                                <button type="submit" name="add_reply" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-5">
                    <p>Need to submit a new ticket? <a href="submit-ticket.php">Click here</a>.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Ticket replies styles */
.ticket-replies {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 20px;
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

<?php
// Helper function to convert hex color to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return "$r, $g, $b";
}

include 'includes/footer.php';
?>