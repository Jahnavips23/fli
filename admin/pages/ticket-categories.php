<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'ticket-categories';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_category'])) {
        // Check if category is in use
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_tickets WHERE category_id = :id");
            $stmt->bindParam(':id', $_POST['delete_category']);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Cannot delete category because it is currently in use by one or more tickets.";
            } else {
                // Delete category
                $stmt = $db->prepare("DELETE FROM ticket_categories WHERE id = :id");
                $stmt->bindParam(':id', $_POST['delete_category']);
                $stmt->execute();
                $success_message = "Category deleted successfully.";
            }
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting category: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_category'])) {
        // Get form data
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $display_order = (int)$_POST['display_order'];
        
        // Validate required fields
        if (empty($name)) {
            $error_message = "Please enter a category name.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing category
                    $stmt = $db->prepare("
                        UPDATE ticket_categories 
                        SET name = :name, 
                            description = :description, 
                            display_order = :display_order
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Insert new category
                    $stmt = $db->prepare("
                        INSERT INTO ticket_categories 
                        (name, description, display_order) 
                        VALUES 
                        (:name, :description, :display_order)
                    ");
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':display_order', $display_order);
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Category updated successfully.";
                } else {
                    $success_message = "Category added successfully.";
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving category: " . $e->getMessage();
            }
        }
    }
}

// Get category data for edit
$category = [];
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM ticket_categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $category = $stmt->fetch();
        
        if (!$category) {
            $error_message = "Category not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving category: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all categories for listing
$categories = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM ticket_categories ORDER BY display_order ASC, name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving categories: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php echo $action === 'list' ? 'Ticket Categories' : ($action === 'add' ? 'Add Category' : 'Edit Category'); ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/ticket-categories.php">Ticket Categories</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo $action === 'list' ? 'Ticket Categories' : ($action === 'add' ? 'Add Category' : 'Edit Category'); ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
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
        
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Ticket Categories</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/ticket-categories.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Category
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Display Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><?php echo $category['name']; ?></td>
                                            <td><?php echo $category['description']; ?></td>
                                            <td><?php echo $category['display_order']; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/ticket-categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $category['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $category['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $category['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $category['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the category: <strong><?php echo $category['name']; ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_category" value="<?php echo $category['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No ticket categories found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add/Edit Category Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $action === 'add' ? 'Add New Category' : 'Edit Category'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($category['name']) ? $category['name'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($category['description']) ? $category['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($category['display_order']) ? $category['display_order'] : '0'; ?>" min="0">
                        </div>
                        
                        <div class="mt-4">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <button type="submit" name="save_category" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Category
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-categories.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>