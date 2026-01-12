<?php
require_once '../includes/config.php';

// Set current page for nav highlighting
$current_page = 'success-stories';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_story'])) {
        // Delete success story
        $id = (int)$_POST['delete_story'];
        try {
            $stmt = $db->prepare("DELETE FROM success_stories WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $success_message = "Success story deleted successfully.";
            $action = 'list';
        } catch (PDOException $e) {
            $error_message = "Error deleting success story: " . $e->getMessage();
        }
    } elseif (isset($_POST['save_story'])) {
        // Get form data
        $title = trim($_POST['title']);
        $organization = trim($_POST['organization']);
        $summary = trim($_POST['summary']);
        $content = trim($_POST['content']);
        $display_order = (int)$_POST['display_order'];
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Process results as JSON
        $results = [];
        if (isset($_POST['result_key']) && is_array($_POST['result_key'])) {
            foreach ($_POST['result_key'] as $index => $key) {
                if (!empty($key) && isset($_POST['result_value'][$index]) && !empty($_POST['result_value'][$index])) {
                    $results[$key] = $_POST['result_value'][$index];
                }
            }
        }
        $results_json = json_encode($results);
        
        // Handle image upload
        $image = '';
        if (isset($_POST['existing_image'])) {
            $image = $_POST['existing_image'];
        }
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/images/';
            $temp_name = $_FILES['image']['tmp_name'];
            $original_name = $_FILES['image']['name'];
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_filename = 'case-study-' . time() . '.' . $extension;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($temp_name, $upload_dir . $new_filename)) {
                $image = 'assets/images/' . $new_filename;
            } else {
                $error_message = "Error uploading image.";
            }
        }
        
        // Validate required fields
        if (empty($title) || empty($organization) || empty($summary)) {
            $error_message = "Please fill in all required fields.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing success story
                    $stmt = $db->prepare("
                        UPDATE success_stories 
                        SET title = :title, 
                            organization = :organization, 
                            summary = :summary, 
                            content = :content, 
                            image = :image, 
                            results = :results, 
                            display_order = :display_order, 
                            active = :active 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                } else {
                    // Insert new success story
                    $stmt = $db->prepare("
                        INSERT INTO success_stories 
                        (title, organization, summary, content, image, results, display_order, active) 
                        VALUES 
                        (:title, :organization, :summary, :content, :image, :results, :display_order, :active)
                    ");
                }
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':organization', $organization);
                $stmt->bindParam(':summary', $summary);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':results', $results_json);
                $stmt->bindParam(':display_order', $display_order);
                $stmt->bindParam(':active', $active);
                
                $stmt->execute();
                
                if ($id > 0) {
                    $success_message = "Success story updated successfully.";
                } else {
                    $success_message = "Success story added successfully.";
                }
                
                $action = 'list';
            } catch (PDOException $e) {
                $error_message = "Error saving success story: " . $e->getMessage();
            }
        }
    }
}

// Get success story data for edit
$story = [];
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM success_stories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $story = $stmt->fetch();
        
        if (!$story) {
            $error_message = "Success story not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving success story: " . $e->getMessage();
        $action = 'list';
    }
}

// Get all success stories for listing
$stories = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM success_stories ORDER BY display_order ASC, created_at DESC");
        $stmt->execute();
        $stories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error retrieving success stories: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?php echo $action === 'list' ? 'Success Stories' : ($action === 'add' ? 'Add Success Story' : 'Edit Success Story'); ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                    <?php if ($action !== 'list'): ?>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php">Success Stories</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo $action === 'list' ? 'Success Stories' : ($action === 'add' ? 'Add Success Story' : 'Edit Success Story'); ?>
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
                    <h3 class="card-title mb-0">Success Stories</h3>
                    <div class="card-tools">
                        <a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php?action=add" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Success Story
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Organization</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stories)): ?>
                                    <?php foreach ($stories as $story): ?>
                                        <tr>
                                            <td><?php echo $story['id']; ?></td>
                                            <td>
                                                <?php if (!empty($story['image'])): ?>
                                                    <img src="<?php echo SITE_URL . '/' . $story['image']; ?>" alt="<?php echo $story['title']; ?>" class="img-thumbnail" style="max-width: 100px;">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $story['title']; ?></td>
                                            <td><?php echo $story['organization']; ?></td>
                                            <td><?php echo $story['display_order']; ?></td>
                                            <td>
                                                <?php if ($story['active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php?action=edit&id=<?php echo $story['id']; ?>" class="btn btn-sm btn-info me-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $story['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $story['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $story['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $story['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the success story: <strong><?php echo $story['title']; ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post">
                                                                    <input type="hidden" name="delete_story" value="<?php echo $story['id']; ?>">
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
                                        <td colspan="7" class="text-center">No success stories found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><?php echo $action === 'add' ? 'Add New Success Story' : 'Edit Success Story'; ?></h3>
                    <a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($story['title']) ? htmlspecialchars($story['title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="organization" class="form-label">Organization <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="organization" name="organization" value="<?php echo isset($story['organization']) ? htmlspecialchars($story['organization']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="summary" class="form-label">Summary <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="summary" name="summary" rows="3" required><?php echo isset($story['summary']) ? htmlspecialchars($story['summary']) : ''; ?></textarea>
                                    <small class="form-text text-muted">A brief summary that will appear on the card (100-150 characters recommended).</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <?php if (isset($story['image']) && !empty($story['image'])): ?>
                                        <div class="mb-3">
                                            <div class="card">
                                                <div class="card-body p-2 text-center">
                                                    <img src="<?php echo SITE_URL . '/' . $story['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                                                    <input type="hidden" name="existing_image" value="<?php echo $story['image']; ?>">
                                                    <p class="text-muted small mt-2 mb-0">Current image</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <label class="input-group-text" for="image"><i class="fas fa-upload"></i></label>
                                    </div>
                                    <small class="form-text text-muted">Recommended size: 800x600 pixels. Leave empty to keep the current image.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="content" class="form-label">Detailed Content</label>
                                    <textarea class="form-control summernote" id="content" name="content" rows="6"><?php echo isset($story['content']) ? htmlspecialchars($story['content']) : ''; ?></textarea>
                                    <small class="form-text text-muted">Detailed description of the success story.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Results & Metrics</label>
                                    <p class="text-muted small mb-2">Add key metrics and results achieved in this success story.</p>
                                    <div id="results-container" class="mb-2">
                                        <?php 
                                        $results = [];
                                        if (isset($story['results']) && !empty($story['results'])) {
                                            $results = json_decode($story['results'], true);
                                        }
                                        
                                        if (!empty($results)) {
                                            foreach ($results as $key => $value) {
                                                echo '<div class="input-group mb-2 result-row">';
                                                echo '<input type="text" class="form-control" name="result_key[]" placeholder="Metric" value="' . htmlspecialchars($key) . '">';
                                                echo '<input type="text" class="form-control" name="result_value[]" placeholder="Value" value="' . htmlspecialchars($value) . '">';
                                                echo '<button type="button" class="btn btn-danger remove-result"><i class="fas fa-times"></i></button>';
                                                echo '</div>';
                                            }
                                        } else {
                                            // Add one empty row by default
                                            echo '<div class="input-group mb-2 result-row">';
                                            echo '<input type="text" class="form-control" name="result_key[]" placeholder="Metric (e.g., Student Engagement)">';
                                            echo '<input type="text" class="form-control" name="result_value[]" placeholder="Value (e.g., Increased by 45%)">';
                                            echo '<button type="button" class="btn btn-danger remove-result"><i class="fas fa-times"></i></button>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success" id="add-result">
                                        <i class="fas fa-plus"></i> Add Result Metric
                                    </button>
                                </div>
                                
                                <div class="card mt-3 mb-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 fs-6">Display Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="display_order" class="form-label">Display Order</label>
                                                    <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo isset($story['display_order']) ? (int)$story['display_order'] : 0; ?>" min="0">
                                                    <small class="form-text text-muted">Lower numbers appear first</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label d-block">Status</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo (!isset($story['active']) || $story['active']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="active">Active</label>
                                                    </div>
                                                    <small class="form-text text-muted">Inactive stories won't be displayed on the website</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <div>
                                <input type="hidden" name="save_story" value="1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Success Story
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add result row
    document.getElementById('add-result').addEventListener('click', function() {
        const container = document.getElementById('results-container');
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 result-row';
        newRow.innerHTML = `
            <input type="text" class="form-control" name="result_key[]" placeholder="Metric (e.g., Student Engagement)">
            <input type="text" class="form-control" name="result_value[]" placeholder="Value (e.g., Increased by 45%)">
            <button type="button" class="btn btn-danger remove-result"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(newRow);
        
        // Add event listener to the new remove button
        newRow.querySelector('.remove-result').addEventListener('click', function() {
            container.removeChild(newRow);
        });
    });
    
    // Remove result row
    document.querySelectorAll('.remove-result').forEach(function(button) {
        button.addEventListener('click', function() {
            const row = this.closest('.result-row');
            row.parentNode.removeChild(row);
        });
    });
    
    // Summernote is initialized automatically by the footer script
});
</script>

<?php include '../includes/footer.php'; ?>