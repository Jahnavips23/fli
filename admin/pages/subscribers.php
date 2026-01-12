<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subscriber']) || isset($_POST['edit_subscriber'])) {
        // Get form data
        $email = isset($_POST['email']) ? sanitize_admin_input($_POST['email']) : '';
        $name = isset($_POST['name']) ? sanitize_admin_input($_POST['name']) : '';
        $type = isset($_POST['type']) ? sanitize_admin_input($_POST['type']) : '';
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($email)) {
            set_admin_alert('Email is required.', 'danger');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_admin_alert('Invalid email format.', 'danger');
        } elseif (empty($type)) {
            set_admin_alert('Subscriber type is required.', 'danger');
        } else {
            try {
                if (isset($_POST['add_subscriber'])) {
                    // Check if email already exists
                    $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email");
                    $stmt->execute(['email' => $email]);
                    if ($stmt->rowCount() > 0) {
                        set_admin_alert('This email is already subscribed.', 'danger');
                    } else {
                        // Add new subscriber
                        $stmt = $db->prepare("
                            INSERT INTO newsletter_subscribers (email, name, subscriber_type, active)
                            VALUES (:email, :name, :type, :active)
                        ");
                        $stmt->execute([
                            'email' => $email,
                            'name' => $name,
                            'type' => $type,
                            'active' => $active
                        ]);
                        
                        set_admin_alert('Subscriber added successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/subscribers.php');
                        exit;
                    }
                } elseif (isset($_POST['edit_subscriber'])) {
                    // Edit existing subscriber
                    $subscriber_id = isset($_POST['subscriber_id']) ? (int)$_POST['subscriber_id'] : 0;
                    
                    if ($subscriber_id > 0) {
                        // Check if email already exists for other subscribers
                        $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email AND id != :id");
                        $stmt->execute(['email' => $email, 'id' => $subscriber_id]);
                        if ($stmt->rowCount() > 0) {
                            set_admin_alert('This email is already subscribed.', 'danger');
                        } else {
                            // Update subscriber
                            $stmt = $db->prepare("
                                UPDATE newsletter_subscribers
                                SET email = :email, name = :name, subscriber_type = :type, active = :active
                                WHERE id = :id
                            ");
                            $stmt->execute([
                                'email' => $email,
                                'name' => $name,
                                'type' => $type,
                                'active' => $active,
                                'id' => $subscriber_id
                            ]);
                            
                            set_admin_alert('Subscriber updated successfully.', 'success');
                            header('Location: ' . ADMIN_URL . '/pages/subscribers.php');
                            exit;
                        }
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_subscriber'])) {
        // Delete subscriber
        $subscriber_id = isset($_POST['subscriber_id']) ? (int)$_POST['subscriber_id'] : 0;
        
        if ($subscriber_id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = :id");
                $stmt->execute(['id' => $subscriber_id]);
                
                set_admin_alert('Subscriber deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/subscribers.php');
        exit;
    } elseif (isset($_POST['export_subscribers'])) {
        // Export subscribers to CSV
        try {
            $stmt = $db->prepare("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
            $stmt->execute();
            $subscribers = $stmt->fetchAll();
            
            if (!empty($subscribers)) {
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
                
                // Open output stream
                $output = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($output, ['ID', 'Email', 'Name', 'Type', 'Status', 'Date']);
                
                // Add subscriber data
                foreach ($subscribers as $subscriber) {
                    fputcsv($output, [
                        $subscriber['id'],
                        $subscriber['email'],
                        $subscriber['name'],
                        $subscriber['subscriber_type'],
                        $subscriber['active'] ? 'Active' : 'Inactive',
                        $subscriber['created_at']
                    ]);
                }
                
                // Close output stream
                fclose($output);
                exit;
            } else {
                set_admin_alert('No subscribers to export.', 'info');
            }
        } catch (PDOException $e) {
            set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        }
    }
}

// Get subscriber data for editing
$subscriber = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM newsletter_subscribers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $subscriber = $stmt->fetch();
        
        if (!$subscriber) {
            set_admin_alert('Subscriber not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/subscribers.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/subscribers.php');
        exit;
    }
}

// Get all subscribers for listing
$subscribers = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
        $stmt->execute();
        $subscribers = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Subscriber Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Subscriber' : 'Edit Subscriber'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <?php if ($action === 'edit' && $subscriber): ?>
                    <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $subscriber ? $subscriber['email'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $subscriber ? $subscriber['name'] : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="type" class="form-label">Subscriber Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="parent" <?php echo ($subscriber && $subscriber['subscriber_type'] === 'parent') ? 'selected' : ''; ?>>Parent</option>
                        <option value="school_staff" <?php echo ($subscriber && $subscriber['subscriber_type'] === 'school_staff') ? 'selected' : ''; ?>>School Staff</option>
                        <option value="other" <?php echo ($subscriber && $subscriber['subscriber_type'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$subscriber || $subscriber['active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_subscriber' : 'edit_subscriber'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Subscriber' : 'Update Subscriber'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Subscribers List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Newsletter Subscribers</h5>
            <div>
                <form action="" method="post" class="d-inline">
                    <button type="submit" name="export_subscribers" class="btn btn-success me-2">
                        <i class="fas fa-file-export me-1"></i> Export CSV
                    </button>
                </form>
                <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Add New Subscriber
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($subscribers)): ?>
                <div class="alert alert-info">
                    No subscribers found. Click the "Add New Subscriber" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?php echo $subscriber['id']; ?></td>
                                    <td><?php echo $subscriber['email']; ?></td>
                                    <td><?php echo $subscriber['name'] ? $subscriber['name'] : '-'; ?></td>
                                    <td>
                                        <?php 
                                        switch ($subscriber['subscriber_type']) {
                                            case 'parent':
                                                echo '<span class="badge bg-primary">Parent</span>';
                                                break;
                                            case 'school_staff':
                                                echo '<span class="badge bg-success">School Staff</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">Other</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($subscriber['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php?action=edit&id=<?php echo $subscriber['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                            <button type="submit" name="delete_subscriber" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>