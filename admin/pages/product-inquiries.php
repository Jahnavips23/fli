<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $inquiry_id = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
        $status = isset($_POST['status']) ? sanitize_admin_input($_POST['status']) : '';
        
        if ($inquiry_id > 0 && !empty($status)) {
            try {
                $stmt = $db->prepare("UPDATE kids_product_inquiries SET status = :status WHERE id = :id");
                $stmt->execute([
                    'status' => $status,
                    'id' => $inquiry_id
                ]);
                
                set_admin_alert('Inquiry status updated successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/product-inquiries.php');
        exit;
    } elseif (isset($_POST['delete_inquiry'])) {
        $inquiry_id = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
        
        if ($inquiry_id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM kids_product_inquiries WHERE id = :id");
                $stmt->execute(['id' => $inquiry_id]);
                
                set_admin_alert('Inquiry deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/product-inquiries.php');
        exit;
    }
}

// Get inquiry data for viewing
$inquiry = null;
if ($action === 'view' && $id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT i.*, p.title as product_title 
            FROM kids_product_inquiries i
            LEFT JOIN kids_products p ON i.product_id = p.id
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $inquiry = $stmt->fetch();
        
        if (!$inquiry) {
            set_admin_alert('Inquiry not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/product-inquiries.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/product-inquiries.php');
        exit;
    }
}

// Get all inquiries for listing
$inquiries = [];
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$valid_statuses = ['new', 'contacted', 'completed', 'cancelled'];

if ($action === 'list') {
    try {
        $sql = "
            SELECT i.*, p.title as product_title 
            FROM kids_product_inquiries i
            LEFT JOIN kids_products p ON i.product_id = p.id
        ";
        
        $params = [];
        
        if (in_array($filter_status, $valid_statuses)) {
            $sql .= " WHERE i.status = :status";
            $params['status'] = $filter_status;
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'view' && $inquiry): ?>
    <!-- View Inquiry Details -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Inquiry Details</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Inquiry Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px;">Inquiry ID</th>
                            <td><?php echo $inquiry['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Date Submitted</th>
                            <td><?php echo date('F j, Y g:i A', strtotime($inquiry['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($inquiry['status'] === 'new'): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php elseif ($inquiry['status'] === 'contacted'): ?>
                                    <span class="badge bg-info">Contacted</span>
                                <?php elseif ($inquiry['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php elseif ($inquiry['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Product</th>
                            <td>
                                <?php if ($inquiry['product_title']): ?>
                                    <?php echo $inquiry['product_title']; ?>
                                <?php else: ?>
                                    <span class="text-muted">Product no longer available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Customer Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px;">Name</th>
                            <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>">
                                    <?php echo htmlspecialchars($inquiry['email']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>
                                <?php if (!empty($inquiry['phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>">
                                        <?php echo htmlspecialchars($inquiry['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Message</h6>
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Update Status</h6>
                    <form action="" method="post">
                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="contacted" <?php echo $inquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                        <option value="completed" <?php echo $inquiry['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $inquiry['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                        Update Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2 mb-3">Actions</h6>
                        <div class="d-flex gap-2">
                            <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>?subject=Re: Inquiry about <?php echo urlencode($inquiry['product_title'] ?? 'our product'); ?>" class="btn btn-info">
                                <i class="fas fa-envelope me-1"></i> Email Customer
                            </a>
                            <?php if (!empty($inquiry['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>" class="btn btn-success">
                                    <i class="fas fa-phone me-1"></i> Call Customer
                                </a>
                            <?php endif; ?>
                            <form action="" method="post" class="ms-auto">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                <button type="submit" name="delete_inquiry" class="btn btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Inquiries List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Kids Product Inquiries</h5>
            <div>
                <div class="btn-group me-2">
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php" class="btn btn-outline-primary <?php echo empty($filter_status) ? 'active' : ''; ?>">All</a>
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php?status=new" class="btn btn-outline-primary <?php echo $filter_status === 'new' ? 'active' : ''; ?>">New</a>
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php?status=contacted" class="btn btn-outline-primary <?php echo $filter_status === 'contacted' ? 'active' : ''; ?>">Contacted</a>
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php?status=completed" class="btn btn-outline-primary <?php echo $filter_status === 'completed' ? 'active' : ''; ?>">Completed</a>
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php?status=cancelled" class="btn btn-outline-primary <?php echo $filter_status === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($inquiries)): ?>
                <div class="alert alert-info">
                    No inquiries found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo $inquiry['id']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($inquiry['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>">
                                            <?php echo htmlspecialchars($inquiry['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($inquiry['product_title']): ?>
                                            <?php echo $inquiry['product_title']; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($inquiry['status'] === 'new'): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php elseif ($inquiry['status'] === 'contacted'): ?>
                                            <span class="badge bg-info">Contacted</span>
                                        <?php elseif ($inquiry['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($inquiry['status'] === 'cancelled'): ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php?action=view&id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" name="delete_inquiry" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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