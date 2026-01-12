<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_counter']) || isset($_POST['edit_counter'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0;
        $icon = isset($_POST['icon']) ? sanitize_admin_input($_POST['icon']) : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Title is required.', 'danger');
        } elseif ($value <= 0) {
            set_admin_alert('Value must be greater than zero.', 'danger');
        } elseif (empty($icon)) {
            set_admin_alert('Icon is required.', 'danger');
        } else {
            try {
                if (isset($_POST['add_counter'])) {
                    // Add new counter
                    $stmt = $db->prepare("
                        INSERT INTO counters (title, value, icon, display_order, active)
                        VALUES (:title, :value, :icon, :display_order, :active)
                    ");
                    $stmt->execute([
                        'title' => $title,
                        'value' => $value,
                        'icon' => $icon,
                        'display_order' => $display_order,
                        'active' => $active
                    ]);
                    
                    set_admin_alert('Counter added successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/counters.php');
                    exit;
                } elseif (isset($_POST['edit_counter'])) {
                    // Edit existing counter
                    $counter_id = isset($_POST['counter_id']) ? (int)$_POST['counter_id'] : 0;
                    
                    if ($counter_id > 0) {
                        // Update counter
                        $stmt = $db->prepare("
                            UPDATE counters
                            SET title = :title, value = :value, icon = :icon,
                                display_order = :display_order, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'value' => $value,
                            'icon' => $icon,
                            'display_order' => $display_order,
                            'active' => $active,
                            'id' => $counter_id
                        ]);
                        
                        set_admin_alert('Counter updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/counters.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_counter'])) {
        // Delete counter
        $counter_id = isset($_POST['counter_id']) ? (int)$_POST['counter_id'] : 0;
        
        if ($counter_id > 0) {
            try {
                // Delete counter from database
                $stmt = $db->prepare("DELETE FROM counters WHERE id = :id");
                $stmt->execute(['id' => $counter_id]);
                
                set_admin_alert('Counter deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/counters.php');
        exit;
    }
}

// Get counter data for editing
$counter = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM counters WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $counter = $stmt->fetch();
        
        if (!$counter) {
            set_admin_alert('Counter not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/counters.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/counters.php');
        exit;
    }
}

// Get all counters for listing
$counters = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM counters ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $counters = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Counter Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Counter' : 'Edit Counter'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <?php if ($action === 'edit' && $counter): ?>
                    <input type="hidden" name="counter_id" value="<?php echo $counter['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $counter ? $counter['title'] : ''; ?>" required>
                            <small class="form-text text-muted">Example: "Client Retention", "Students Reached"</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="value" name="value" value="<?php echo $counter ? $counter['value'] : ''; ?>" min="1" required>
                            <small class="form-text text-muted">The number to display in the counter</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon Class <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i id="icon-preview" class="<?php echo $counter ? $counter['icon'] : 'fas fa-chart-line'; ?>"></i></span>
                                <input type="text" class="form-control" id="icon" name="icon" value="<?php echo $counter ? $counter['icon'] : ''; ?>" placeholder="e.g., fas fa-chart-line" required>
                            </div>
                            <small class="form-text text-muted">Enter a Font Awesome icon class. <a href="https://fontawesome.com/icons" target="_blank">Browse icons</a></small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $counter ? $counter['display_order'] : '0'; ?>" min="0">
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$counter || $counter['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_counter' : 'edit_counter'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Counter' : 'Update Counter'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/counters.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Icon Preview Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('icon-preview');
        
        iconInput.addEventListener('input', function() {
            iconPreview.className = this.value || 'fas fa-chart-line';
        });
    });
    </script>
<?php else: ?>
    <!-- Counters List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Counters</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/counters.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Counter
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($counters)): ?>
                <div class="alert alert-info">
                    No counters found. Click the "Add New Counter" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Title</th>
                                <th>Value</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($counters as $counter): ?>
                                <tr>
                                    <td><?php echo $counter['id']; ?></td>
                                    <td><i class="<?php echo $counter['icon']; ?> fa-2x"></i></td>
                                    <td><?php echo $counter['title']; ?></td>
                                    <td><?php echo number_format($counter['value']); ?></td>
                                    <td><?php echo $counter['display_order']; ?></td>
                                    <td>
                                        <?php if ($counter['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/counters.php?action=edit&id=<?php echo $counter['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="counter_id" value="<?php echo $counter['id']; ?>">
                                            <button type="submit" name="delete_counter" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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