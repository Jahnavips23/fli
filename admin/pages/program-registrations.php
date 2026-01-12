<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        // Update registration status
        $registration_id = isset($_POST['registration_id']) ? (int)$_POST['registration_id'] : 0;
        $status = isset($_POST['status']) ? sanitize_admin_input($_POST['status']) : 'new';
        $payment_status = isset($_POST['payment_status']) ? sanitize_admin_input($_POST['payment_status']) : 'pending';
        
        if ($registration_id > 0) {
            try {
                $stmt = $db->prepare("UPDATE program_registrations SET status = :status, payment_status = :payment_status WHERE id = :id");
                $stmt->execute([
                    'status' => $status,
                    'payment_status' => $payment_status,
                    'id' => $registration_id
                ]);
                
                set_admin_alert('Registration status updated successfully.', 'success');
                
                // Redirect back to the view page or list
                if ($action === 'view') {
                    header('Location: ' . ADMIN_URL . '/pages/program-registrations.php?action=view&id=' . $registration_id);
                } else {
                    header('Location: ' . ADMIN_URL . '/pages/program-registrations.php' . ($program_id ? '?program_id=' . $program_id : ''));
                }
                exit;
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_registration'])) {
        // Delete registration
        $registration_id = isset($_POST['registration_id']) ? (int)$_POST['registration_id'] : 0;
        
        if ($registration_id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM program_registrations WHERE id = :id");
                $stmt->execute(['id' => $registration_id]);
                
                set_admin_alert('Registration deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/program-registrations.php' . ($program_id ? '?program_id=' . $program_id : ''));
        exit;
    }
}

// Get registration data for viewing
$registration = null;
if ($action === 'view' && $id > 0) {
    try {
        $stmt = $db->prepare("
            SELECT r.*, p.title as program_title 
            FROM program_registrations r
            JOIN kids_programs p ON r.program_id = p.id
            WHERE r.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            set_admin_alert('Registration not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/program-registrations.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/program-registrations.php');
        exit;
    }
}

// Get all registrations for listing
$registrations = [];
if ($action === 'list') {
    try {
        $query = "
            SELECT r.*, p.title as program_title 
            FROM program_registrations r
            JOIN kids_programs p ON r.program_id = p.id
        ";
        
        $params = [];
        
        // Filter by program if specified
        if ($program_id > 0) {
            $query .= " WHERE r.program_id = :program_id";
            $params['program_id'] = $program_id;
        }
        
        $query .= " ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $registrations = $stmt->fetchAll();
        
        // Get program details if filtering by program
        $program = null;
        if ($program_id > 0) {
            $stmt = $db->prepare("SELECT * FROM kids_programs WHERE id = :id");
            $stmt->execute(['id' => $program_id]);
            $program = $stmt->fetch();
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

// Get all programs for filtering
$programs = [];
try {
    $stmt = $db->prepare("SELECT id, title FROM kids_programs ORDER BY title");
    $stmt->execute();
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error
}

include '../includes/header.php';
?>

<?php if ($action === 'view' && $registration): ?>
    <!-- View Registration Details -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Registration Details</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/program-registrations.php<?php echo $program_id ? '?program_id=' . $program_id : ''; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Child Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?php echo $registration['child_name']; ?></dd>
                        
                        <dt class="col-sm-4">Age:</dt>
                        <dd class="col-sm-8"><?php echo $registration['child_age']; ?> years</dd>
                    </dl>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Parent/Guardian Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?php echo $registration['parent_name']; ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">
                            <a href="mailto:<?php echo $registration['email']; ?>"><?php echo $registration['email']; ?></a>
                        </dd>
                        
                        <dt class="col-sm-4">Phone:</dt>
                        <dd class="col-sm-8"><?php echo $registration['phone']; ?></dd>
                        
                        <?php if (!empty($registration['address'])): ?>
                            <dt class="col-sm-4">Address:</dt>
                            <dd class="col-sm-8"><?php echo nl2br($registration['address']); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
                
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Program Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Program:</dt>
                        <dd class="col-sm-8">
                            <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php?action=edit&id=<?php echo $registration['program_id']; ?>">
                                <?php echo $registration['program_title']; ?>
                            </a>
                        </dd>
                        
                        <dt class="col-sm-4">Registration Date:</dt>
                        <dd class="col-sm-8"><?php echo date('F j, Y g:i A', strtotime($registration['created_at'])); ?></dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php echo get_status_color($registration['status']); ?>">
                                <?php echo ucfirst($registration['status']); ?>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Payment Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php echo get_payment_status_color($registration['payment_status']); ?>">
                                <?php echo ucfirst($registration['payment_status']); ?>
                            </span>
                        </dd>
                    </dl>
                    
                    <?php if (!empty($registration['special_requirements'])): ?>
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Special Requirements</h6>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br($registration['special_requirements']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Update Status</h6>
                    <form action="" method="post">
                        <input type="hidden" name="registration_id" value="<?php echo $registration['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Registration Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="new" <?php echo $registration['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="confirmed" <?php echo $registration['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="waitlisted" <?php echo $registration['status'] === 'waitlisted' ? 'selected' : ''; ?>>Waitlisted</option>
                                    <option value="cancelled" <?php echo $registration['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $registration['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_status" class="form-label">Payment Status</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="pending" <?php echo $registration['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $registration['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="refunded" <?php echo $registration['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    <option value="waived" <?php echo $registration['payment_status'] === 'waived' ? 'selected' : ''; ?>>Waived</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
                
                <div class="col-md-6 text-end">
                    <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this registration? This action cannot be undone.');">
                        <input type="hidden" name="registration_id" value="<?php echo $registration['id']; ?>">
                        <button type="submit" name="delete_registration" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete Registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Registrations List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">
                <?php if (isset($program)): ?>
                    Registrations for: <?php echo $program['title']; ?>
                <?php else: ?>
                    Program Registrations
                <?php endif; ?>
            </h5>
            
            <div>
                <?php if ($program_id): ?>
                    <a href="<?php echo ADMIN_URL; ?>/pages/program-registrations.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-1"></i> All Registrations
                    </a>
                    <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Programs
                    </a>
                <?php else: ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> Filter by Program
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <?php foreach ($programs as $p): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/pages/program-registrations.php?program_id=<?php echo $p['id']; ?>">
                                        <?php echo $p['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($registrations)): ?>
                <div class="alert alert-info">
                    No registrations found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Child Name</th>
                                <th>Age</th>
                                <th>Parent</th>
                                <th>Program</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo $reg['id']; ?></td>
                                    <td><?php echo $reg['child_name']; ?></td>
                                    <td><?php echo $reg['child_age']; ?></td>
                                    <td><?php echo $reg['parent_name']; ?></td>
                                    <td><?php echo $reg['program_title']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($reg['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_status_color($reg['status']); ?>">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo get_payment_status_color($reg['payment_status']); ?>">
                                            <?php echo ucfirst($reg['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/program-registrations.php?action=view&id=<?php echo $reg['id']; ?><?php echo $program_id ? '&program_id=' . $program_id : ''; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                            <button type="submit" name="delete_registration" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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
        case 'confirmed':
            return 'success';
        case 'waitlisted':
            return 'warning';
        case 'cancelled':
            return 'danger';
        case 'completed':
            return 'info';
        default:
            return 'secondary';
    }
}

// Helper function to get payment status color
function get_payment_status_color($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'paid':
            return 'success';
        case 'refunded':
            return 'info';
        case 'waived':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>

<?php include '../includes/footer.php'; ?>