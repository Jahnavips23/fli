<?php
require_once 'includes/config.php';

// Initialize variables
$success_message = '';
$error_message = '';
$ticket_number = '';

// Generate a unique ticket number
function generate_ticket_number() {
    $prefix = 'TKT';
    $timestamp = substr(time(), -6);
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    return $prefix . $timestamp . $random;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    // Validate required fields
    if (empty($subject) || empty($description) || empty($name) || empty($email)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            // Generate ticket number
            $ticket_number = generate_ticket_number();
            
            // Get default status and priority
            $stmt = $db->prepare("SELECT id FROM ticket_statuses WHERE display_order = (SELECT MIN(display_order) FROM ticket_statuses)");
            $stmt->execute();
            $status = $stmt->fetch();
            $status_id = $status['id'];
            
            $stmt = $db->prepare("SELECT id FROM ticket_priorities WHERE name = 'Medium'");
            $stmt->execute();
            $priority = $stmt->fetch();
            $priority_id = $priority ? $priority['id'] : 2; // Default to Medium or ID 2
            
            // Insert new ticket
            $stmt = $db->prepare("
                INSERT INTO customer_tickets 
                (ticket_number, subject, description, customer_name, customer_email, customer_phone, 
                 status_id, priority_id, category_id) 
                VALUES 
                (:ticket_number, :subject, :description, :name, :email, :phone, 
                 :status_id, :priority_id, :category_id)
            ");
            
            $stmt->bindParam(':ticket_number', $ticket_number);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':status_id', $status_id);
            $stmt->bindParam(':priority_id', $priority_id);
            $stmt->bindParam(':category_id', $category_id);
            
            $stmt->execute();
            
            $success_message = "Your ticket has been submitted successfully. Your ticket number is: <strong>" . $ticket_number . "</strong>";
            
            // Clear form data after successful submission
            $subject = $description = $name = $email = $phone = '';
            $category_id = null;
        } catch (PDOException $e) {
            $error_message = "Error submitting ticket: " . $e->getMessage();
        }
    }
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

// Page title
$page_title = "Submit a Support Ticket";
include 'includes/header.php';
?>

<section class="submit-ticket-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold">Submit a Support Ticket</h1>
                    <p class="lead">Need help? Submit a ticket and our support team will assist you.</p>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                        <p class="mt-3">Please save your ticket number for future reference. You can use it to check the status of your ticket.</p>
                        <div class="mt-3">
                            <a href="track.php?id=<?php echo $ticket_number; ?>&type=ticket" class="btn btn-primary">Track Your Ticket</a>
                            <a href="submit-ticket.php" class="btn btn-outline-secondary ms-2">Submit Another Ticket</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select class="form-select" id="category_id" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                                        <?php echo $category['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="6" required><?php echo isset($description) ? $description : ''; ?></textarea>
                                    <small class="form-text text-muted">Please provide as much detail as possible about your issue.</small>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="text-center mt-5">
                        <p>Already have a ticket? <a href="track.php">Track your ticket here</a>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>