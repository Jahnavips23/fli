<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        // Update enquiry status
        $enquiry_id = isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0;
        $status = isset($_POST['status']) ? sanitize_admin_input($_POST['status']) : 'new';
        
        if ($enquiry_id > 0) {
            try {
                $stmt = $db->prepare("UPDATE product_enquiries SET status = :status WHERE id = :id");
                $stmt->execute([
                    'status' => $status,
                    'id' => $enquiry_id
                ]);
                
                set_admin_alert('Enquiry status updated successfully.', 'success');
                header('Location: ' . ADMIN_URL . '/pages/product-enquiries.php');
                exit;
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_enquiry'])) {
        // Delete enquiry
        $enquiry_id = isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0;
        
        if ($enquiry_id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM product_enquiries WHERE id = :id");
                $stmt->execute(['id' => $enquiry_id]);
                
                set_admin_alert('Enquiry deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/product-enquiries.php');
        exit;
    }
}

// Get enquiry data for viewing
$enquiry = null;
if ($action === 'view' && $id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT pe.*, p.name as product_name 
            FROM product_enquiries pe
            LEFT JOIN products p ON pe.product_id = p.id
            WHERE pe.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $enquiry = $stmt->fetch();
        
        if (!$enquiry) {
            set_admin_alert('Enquiry not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/product-enquiries.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/product-enquiries.php');
        exit;
    }
}

// Get all enquiries for listing
$enquiries = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("
            SELECT pe.*, p.name as product_name 
            FROM product_enquiries pe
            LEFT JOIN products p ON pe.product_id = p.id
            ORDER BY pe.created_at DESC
        ");
        $stmt->execute();
        $enquiries = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'view' && $enquiry): ?>
    <!-- View Enquiry Details -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Enquiry Details</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/product-enquiries.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?php echo $enquiry['name']; ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">
                            <a href="mailto:<?php echo $enquiry['email']; ?>"><?php echo $enquiry['email']; ?></a>
                        </dd>
                        
                        <?php if (!empty($enquiry['phone'])): ?>
                            <dt class="col-sm-4">Phone:</dt>
                            <dd class="col-sm-8"><?php echo $enquiry['phone']; ?></dd>
                        <?php endif; ?>
                        
                        <?php if (!empty($enquiry['school_name'])): ?>
                            <dt class="col-sm-4">School:</dt>
                            <dd class="col-sm-8"><?php echo $enquiry['school_name']; ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
                
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Enquiry Details</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Product:</dt>
                        <dd class="col-sm-8">
                            <?php if ($enquiry['product_id']): ?>
                                <a href="<?php echo ADMIN_URL; ?>/pages/products.php?action=edit&id=<?php echo $enquiry['product_id']; ?>">
                                    <?php echo $enquiry['product_name']; ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">General Enquiry</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4">Date:</dt>
                        <dd class="col-sm-8"><?php echo date('F j, Y g:i A', strtotime($enquiry['created_at'])); ?></dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php echo get_status_color($enquiry['status']); ?>">
                                <?php echo ucfirst($enquiry['status']); ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mb-3">Message</h6>
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br($enquiry['message']); ?>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Update Status</h6>
                    <form action="" method="post">
                        <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                        <div class="input-group">
                            <select class="form-select" name="status">
                                <option value="new" <?php echo $enquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="in-progress" <?php echo $enquiry['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="responded" <?php echo $enquiry['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                                <option value="closed" <?php echo $enquiry['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-6 text-end">
                    <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this enquiry? This action cannot be undone.');">
                        <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                        <button type="submit" name="delete_enquiry" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete Enquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Enquiries List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Product Enquiries</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enquiries)): ?>
                <div class="alert alert-info">
                    No enquiries found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>School</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enquiries as $enquiry): ?>
                                <tr>
                                    <td><?php echo $enquiry['id']; ?></td>
                                    <td><?php echo $enquiry['name']; ?></td>
                                    <td><?php echo $enquiry['email']; ?></td>
                                    <td><?php echo $enquiry['school_name'] ?: '-'; ?></td>
                                    <td>
                                        <?php if ($enquiry['product_id']): ?>
                                            <?php echo $enquiry['product_name']; ?>
                                        <?php else: ?>
                                            <span class="text-muted">General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($enquiry['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_status_color($enquiry['status']); ?>">
                                            <?php echo ucfirst($enquiry['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/product-enquiries.php?action=view&id=<?php echo $enquiry['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                                            <button type="submit" name="delete_enquiry" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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

<?php
// Helper function to get status color
function get_status_color($status) {
    switch ($status) {
        case 'new':
            return 'primary';
        case 'in-progress':
            return 'warning';
        case 'responded':
            return 'info';
        case 'closed':
            return 'success';
        default:
            return 'secondary';
    }
}
?>

<?php include '../includes/footer.php'; ?>