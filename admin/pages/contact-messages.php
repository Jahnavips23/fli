<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_message'])) {
        // Delete message
        $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        
        if ($message_id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
                $stmt->execute(['id' => $message_id]);
                
                set_admin_alert('Message deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/contact-messages.php');
        exit;
    } elseif (isset($_POST['mark_read'])) {
        // Mark message as read
        $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        
        if ($message_id > 0) {
            try {
                $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
                $stmt->execute(['id' => $message_id]);
                
                set_admin_alert('Message marked as read.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/contact-messages.php');
        exit;
    } elseif (isset($_POST['mark_unread'])) {
        // Mark message as unread
        $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        
        if ($message_id > 0) {
            try {
                $stmt = $db->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = :id");
                $stmt->execute(['id' => $message_id]);
                
                set_admin_alert('Message marked as unread.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/contact-messages.php');
        exit;
    }
}

// Get message data for viewing
$message = null;
if ($action === 'view' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = $stmt->fetch();
        
        if (!$message) {
            set_admin_alert('Message not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/contact-messages.php');
            exit;
        }
        
        // Mark message as read when viewed
        if (!$message['is_read']) {
            $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/contact-messages.php');
        exit;
    }
}

// Get all messages for listing
$messages = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $stmt->execute();
        $messages = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'view' && $message): ?>
    <!-- View Message -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">View Message</h5>
            <div>
                <a href="<?php echo ADMIN_URL; ?>/pages/contact-messages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Messages
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="message-header mb-4 pb-3 border-bottom">
                <div class="row">
                    <div class="col-md-6">
                        <h4><?php echo $message['subject']; ?></h4>
                        <div class="text-muted">
                            From: <strong><?php echo $message['name']; ?></strong> &lt;<?php echo $message['email']; ?>&gt;
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">
                            <i class="far fa-calendar-alt me-1"></i> <?php echo format_admin_date($message['created_at']); ?>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-<?php echo $message['is_read'] ? 'success' : 'warning'; ?>">
                                <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                            </span>
                            <span class="badge bg-info ms-1">
                                <?php echo ucfirst($message['contact_type']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="message-content mb-4">
                <h5>Message:</h5>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br($message['message']); ?>
                </div>
            </div>
            
            <div class="message-actions d-flex justify-content-between">
                <div>
                    <a href="mailto:<?php echo $message['email']; ?>" class="btn btn-primary">
                        <i class="fas fa-reply me-1"></i> Reply via Email
                    </a>
                    <form action="" method="post" class="d-inline ms-2">
                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                        <?php if ($message['is_read']): ?>
                            <button type="submit" name="mark_unread" class="btn btn-warning">
                                <i class="fas fa-envelope me-1"></i> Mark as Unread
                            </button>
                        <?php else: ?>
                            <button type="submit" name="mark_read" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Mark as Read
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                <form action="" method="post" class="d-inline">
                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                    <button type="submit" name="delete_message" class="btn btn-danger confirm-delete">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Messages List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Contact Messages</h5>
        </div>
        <div class="card-body">
            <?php if (empty($messages)): ?>
                <div class="alert alert-info">
                    No messages found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr class="<?php echo !$msg['is_read'] ? 'table-warning' : ''; ?>">
                                    <td><?php echo $msg['id']; ?></td>
                                    <td><?php echo $msg['name']; ?></td>
                                    <td><?php echo $msg['email']; ?></td>
                                    <td><?php echo $msg['subject']; ?></td>
                                    <td>
                                        <?php 
                                        switch ($msg['contact_type']) {
                                            case 'school_admin':
                                                echo '<span class="badge bg-primary">School Admin</span>';
                                                break;
                                            case 'teacher':
                                                echo '<span class="badge bg-success">Teacher</span>';
                                                break;
                                            case 'parent':
                                                echo '<span class="badge bg-info">Parent</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">Other</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($msg['is_read']): ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($msg['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/contact-messages.php?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="mailto:<?php echo $msg['email']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" name="delete_message" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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