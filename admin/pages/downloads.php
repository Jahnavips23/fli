<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_download']) || isset($_POST['edit_download'])) {
        // Get form data
        $title = isset($_POST['title']) ? sanitize_admin_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_admin_input($_POST['description']) : '';
        $category = isset($_POST['category']) ? sanitize_admin_input($_POST['category']) : '';
        $for_schools = isset($_POST['for_schools']) ? 1 : 0;
        $for_kids = isset($_POST['for_kids']) ? 1 : 0;
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validate form data
        if (empty($title)) {
            set_admin_alert('Title is required.', 'danger');
        } else {
            try {
                // Handle file upload
                $file_path = '';
                $file_size = 0;
                
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = SITE_ROOT . 'uploads/downloads/';
                    $file_name = time() . '_' . basename($_FILES['file']['name']);
                    $upload_file = $upload_dir . $file_name;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
                        $file_path = 'uploads/downloads/' . $file_name;
                        $file_size = $_FILES['file']['size'];
                    } else {
                        set_admin_alert('Failed to upload file.', 'danger');
                    }
                }
                
                if (isset($_POST['add_download'])) {
                    // Add new download
                    if (empty($file_path)) {
                        set_admin_alert('File is required.', 'danger');
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO downloads (title, description, file_path, file_size, category, for_schools, for_kids, active)
                            VALUES (:title, :description, :file_path, :file_size, :category, :for_schools, :for_kids, :active)
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'file_path' => $file_path,
                            'file_size' => $file_size,
                            'category' => $category,
                            'for_schools' => $for_schools,
                            'for_kids' => $for_kids,
                            'active' => $active
                        ]);
                        
                        set_admin_alert('Download added successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/downloads.php');
                        exit;
                    }
                } elseif (isset($_POST['edit_download'])) {
                    // Edit existing download
                    $download_id = isset($_POST['download_id']) ? (int)$_POST['download_id'] : 0;
                    
                    if ($download_id > 0) {
                        // Get current download data
                        $stmt = $db->prepare("SELECT file_path, file_size FROM downloads WHERE id = :id");
                        $stmt->execute(['id' => $download_id]);
                        $current_download = $stmt->fetch();
                        
                        // Use current file if no new file uploaded
                        if (empty($file_path) && $current_download) {
                            $file_path = $current_download['file_path'];
                            $file_size = $current_download['file_size'];
                        }
                        
                        // Update download
                        $stmt = $db->prepare("
                            UPDATE downloads
                            SET title = :title, description = :description, file_path = :file_path,
                                file_size = :file_size, category = :category, for_schools = :for_schools,
                                for_kids = :for_kids, active = :active
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'file_path' => $file_path,
                            'file_size' => $file_size,
                            'category' => $category,
                            'for_schools' => $for_schools,
                            'for_kids' => $for_kids,
                            'active' => $active,
                            'id' => $download_id
                        ]);
                        
                        set_admin_alert('Download updated successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/downloads.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['delete_download'])) {
        // Delete download
        $download_id = isset($_POST['download_id']) ? (int)$_POST['download_id'] : 0;
        
        if ($download_id > 0) {
            try {
                // Get download file path
                $stmt = $db->prepare("SELECT file_path FROM downloads WHERE id = :id");
                $stmt->execute(['id' => $download_id]);
                $download = $stmt->fetch();
                
                // Delete download from database
                $stmt = $db->prepare("DELETE FROM downloads WHERE id = :id");
                $stmt->execute(['id' => $download_id]);
                
                // Delete file
                if ($download && !empty($download['file_path'])) {
                    $file = SITE_ROOT . $download['file_path'];
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                
                set_admin_alert('Download deleted successfully.', 'success');
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/downloads.php');
        exit;
    }
}

// Get download data for editing
$download = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM downloads WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $download = $stmt->fetch();
        
        if (!$download) {
            set_admin_alert('Download not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/downloads.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/downloads.php');
        exit;
    }
}

// Get all downloads for listing
$downloads = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT * FROM downloads ORDER BY created_at DESC");
        $stmt->execute();
        $downloads = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

// Get unique categories for dropdown
$categories = [];
try {
    $stmt = $db->prepare("SELECT DISTINCT category FROM downloads WHERE category != '' ORDER BY category ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Download Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New Download' : 'Edit Download'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $download): ?>
                    <input type="hidden" name="download_id" value="<?php echo $download['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $download ? $download['title'] : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo $download ? $download['description'] : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" list="category-list" value="<?php echo $download ? $download['category'] : ''; ?>">
                            <datalist id="category-list">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="file" class="form-label">File <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?></label>
                            <input type="file" class="form-control" id="file" name="file" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            
                            <?php if ($download && !empty($download['file_path'])): ?>
                                <div class="mt-2">
                                    <p>Current File: <a href="<?php echo SITE_URL . '/' . $download['file_path']; ?>" target="_blank"><?php echo basename($download['file_path']); ?></a></p>
                                    <p>File Size: <?php echo number_format($download['file_size'] / 1024, 2); ?> KB</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="for_schools" name="for_schools" <?php echo ($download && $download['for_schools']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="for_schools">For Schools</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="for_kids" name="for_kids" <?php echo ($download && $download['for_kids']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="for_kids">For Kids</label>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" <?php echo (!$download || $download['active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_download' : 'edit_download'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Download' : 'Update Download'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Downloads List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Downloads</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Download
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($downloads)): ?>
                <div class="alert alert-info">
                    No downloads found. Click the "Add New Download" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>For</th>
                                <th>Downloads</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($downloads as $download): ?>
                                <tr>
                                    <td><?php echo $download['id']; ?></td>
                                    <td><?php echo $download['title']; ?></td>
                                    <td><?php echo $download['category'] ? $download['category'] : 'Uncategorized'; ?></td>
                                    <td>
                                        <?php if ($download['for_schools']): ?>
                                            <span class="badge bg-primary">Schools</span>
                                        <?php endif; ?>
                                        <?php if ($download['for_kids']): ?>
                                            <span class="badge bg-success">Kids</span>
                                        <?php endif; ?>
                                        <?php if (!$download['for_schools'] && !$download['for_kids']): ?>
                                            <span class="badge bg-secondary">All</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $download['download_count']; ?></td>
                                    <td>
                                        <?php if ($download['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($download['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php?action=edit&id=<?php echo $download['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo SITE_URL . '/' . $download['file_path']; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form action="" method="post" class="d-inline">
                                            <input type="hidden" name="download_id" value="<?php echo $download['id']; ?>">
                                            <button type="submit" name="delete_download" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
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