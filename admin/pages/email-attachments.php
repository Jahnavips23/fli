<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'email-attachments';
require_once __DIR__ . '/../includes/header.php';

// Create uploads directory if it doesn't exist
$upload_dir = ROOT_PATH . 'uploads/attachments/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Initialize variables
$attachments = [];

// Get all attachments
if ($db_available) {
    try {
        $stmt = $db->prepare("SELECT * FROM email_attachments ORDER BY name");
        $stmt->execute();
        $attachments = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
} else {
    $db_error = "Database connection is not available. Please check your configuration.";
}

// Handle attachment deletion
if (isset($_POST['delete_attachment'])) {
    $attachment_id = (int)$_POST['attachment_id'];
    
    try {
        // Get attachment details
        $stmt = $db->prepare("SELECT * FROM email_attachments WHERE id = :id");
        $stmt->bindParam(':id', $attachment_id);
        $stmt->execute();
        $attachment = $stmt->fetch();
        
        if ($attachment) {
            // Delete file from server
            $file_path = ROOT_PATH . $attachment['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $db->prepare("DELETE FROM email_attachments WHERE id = :id");
            $stmt->bindParam(':id', $attachment_id);
            $stmt->execute();
            
            $success_message = "Attachment deleted successfully.";
            
            // Redirect to refresh the page
            header("Location: email-attachments.php?success=deleted");
            exit;
        } else {
            $error_message = "Attachment not found.";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting attachment: " . $e->getMessage();
    }
}

// Handle setting default attachment
if (isset($_POST['toggle_default'])) {
    $attachment_id = (int)$_POST['attachment_id'];
    $is_default = (int)$_POST['is_default'];
    $new_default = $is_default ? 0 : 1;
    
    try {
        $stmt = $db->prepare("UPDATE email_attachments SET is_default = :is_default WHERE id = :id");
        $stmt->bindParam(':is_default', $new_default);
        $stmt->bindParam(':id', $attachment_id);
        $stmt->execute();
        
        $success_message = $new_default ? "Attachment set as default." : "Attachment removed from defaults.";
        
        // Redirect to refresh the page
        header("Location: email-attachments.php?success=" . ($new_default ? "default_set" : "default_removed"));
        exit;
    } catch (PDOException $e) {
        $error_message = "Error updating attachment: " . $e->getMessage();
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_attachment'])) {
    $name = trim($_POST['name']);
    
    if (empty($name)) {
        $error_message = "Please enter a name for the attachment.";
    } elseif (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Please select a file to upload.";
    } else {
        $file = $_FILES['attachment'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        
        // Generate unique filename
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = uniqid() . '.' . $file_ext;
        $upload_path = 'uploads/attachments/' . $unique_name;
        $full_path = ROOT_PATH . $upload_path;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $full_path)) {
            try {
                // Insert into database
                $stmt = $db->prepare("
                    INSERT INTO email_attachments 
                    (name, file_path, file_type, file_size, is_default) 
                    VALUES 
                    (:name, :file_path, :file_type, :file_size, 0)
                ");
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':file_path', $upload_path);
                $stmt->bindParam(':file_type', $file_type);
                $stmt->bindParam(':file_size', $file_size);
                $stmt->execute();
                
                $success_message = "Attachment uploaded successfully.";
                
                // Redirect to refresh the page
                header("Location: email-attachments.php?success=uploaded");
                exit;
            } catch (PDOException $e) {
                // Delete the uploaded file if database insert fails
                unlink($full_path);
                $error_message = "Error saving attachment: " . $e->getMessage();
            }
        } else {
            $error_message = "Error uploading file. Please try again.";
        }
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $success_message = "Attachment deleted successfully.";
            break;
        case 'default_set':
            $success_message = "Attachment set as default.";
            break;
        case 'default_removed':
            $success_message = "Attachment removed from defaults.";
            break;
        case 'uploaded':
            $success_message = "Attachment uploaded successfully.";
            break;
    }
}

// Function to format file size
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Email Attachments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">Client Inquiries</a></li>
                        <li class="breadcrumb-item active">Email Attachments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($db_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-ban"></i> Database Connection Error</h5>
                    <p><?php echo $db_error; ?></p>
                    <p>Please check your database configuration in <code>includes/config.php</code>.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php else: ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (isset($db_error)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Email Attachments</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> Cannot manage email attachments until database connection is fixed.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Upload New Attachment</h3>
                            </div>
                        <div class="card-body">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Attachment Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <small class="form-text text-muted">Enter a descriptive name for the attachment (e.g., "Company Brochure")</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="attachment">File <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="attachment" name="attachment" required>
                                        <label class="custom-file-label" for="attachment">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">Recommended file types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX</small>
                                </div>
                                
                                <button type="submit" name="upload_attachment" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Attachment
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">About Default Attachments</h3>
                        </div>
                        <div class="card-body">
                            <p>Default attachments are automatically included when sending welcome emails to clients.</p>
                            <p>You can mark multiple attachments as default. All default attachments will be included in welcome emails.</p>
                            <p>To toggle whether an attachment is included by default, use the "Default" toggle in the attachments list.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Manage Attachments</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>File Type</th>
                                            <th>Size</th>
                                            <th>Default</th>
                                            <th>Date Added</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($attachments)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No attachments found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($attachments as $attachment): ?>
                                                <tr>
                                                    <td><?php echo $attachment['name']; ?></td>
                                                    <td>
                                                        <?php 
                                                        $icon = 'file';
                                                        if (strpos($attachment['file_type'], 'pdf') !== false) {
                                                            $icon = 'file-pdf';
                                                        } elseif (strpos($attachment['file_type'], 'word') !== false || strpos($attachment['file_type'], 'doc') !== false) {
                                                            $icon = 'file-word';
                                                        } elseif (strpos($attachment['file_type'], 'excel') !== false || strpos($attachment['file_type'], 'sheet') !== false || strpos($attachment['file_type'], 'xls') !== false) {
                                                            $icon = 'file-excel';
                                                        } elseif (strpos($attachment['file_type'], 'powerpoint') !== false || strpos($attachment['file_type'], 'presentation') !== false || strpos($attachment['file_type'], 'ppt') !== false) {
                                                            $icon = 'file-powerpoint';
                                                        } elseif (strpos($attachment['file_type'], 'image') !== false) {
                                                            $icon = 'file-image';
                                                        } elseif (strpos($attachment['file_type'], 'zip') !== false || strpos($attachment['file_type'], 'archive') !== false) {
                                                            $icon = 'file-archive';
                                                        }
                                                        ?>
                                                        <i class="fas fa-<?php echo $icon; ?>"></i>
                                                        <?php echo strtoupper(pathinfo($attachment['file_path'], PATHINFO_EXTENSION)); ?>
                                                    </td>
                                                    <td><?php echo format_file_size($attachment['file_size']); ?></td>
                                                    <td>
                                                        <form action="" method="post">
                                                            <input type="hidden" name="attachment_id" value="<?php echo $attachment['id']; ?>">
                                                            <input type="hidden" name="is_default" value="<?php echo $attachment['is_default']; ?>">
                                                            <button type="submit" name="toggle_default" class="btn btn-sm <?php echo $attachment['is_default'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                                <?php echo $attachment['is_default'] ? '<i class="fas fa-check"></i> Yes' : 'No'; ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($attachment['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?php echo SITE_URL . '/' . $attachment['file_path']; ?>" class="btn btn-sm btn-info" title="Download" target="_blank">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $attachment['id']; ?>" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <!-- Delete Modal -->
                                                        <div class="modal fade" id="deleteModal<?php echo $attachment['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $attachment['id']; ?>" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $attachment['id']; ?>">Confirm Deletion</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Are you sure you want to delete the attachment <strong><?php echo $attachment['name']; ?></strong>?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <form action="" method="post">
                                                                            <input type="hidden" name="attachment_id" value="<?php echo $attachment['id']; ?>">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                            <button type="submit" name="delete_attachment" class="btn btn-danger">Delete</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if (!isset($db_error)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update file input label with selected filename
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>