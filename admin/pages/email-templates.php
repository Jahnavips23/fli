<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'email-templates';
require_once __DIR__ . '/../includes/header.php';

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Initialize variables
$templates = [];

// Get all templates
if ($db_available) {
    try {
        $stmt = $db->prepare("SELECT * FROM email_templates ORDER BY name");
        $stmt->execute();
        $templates = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
} else {
    $db_error = "Database connection is not available. Please check your configuration.";
}

// Handle template deletion
if (isset($_POST['delete_template'])) {
    $template_id = (int)$_POST['template_id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM email_templates WHERE id = :id");
        $stmt->bindParam(':id', $template_id);
        $stmt->execute();
        
        $success_message = "Template deleted successfully.";
        
        // Redirect to refresh the page
        header("Location: email-templates.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error deleting template: " . $e->getMessage();
    }
}

// Handle setting default template
if (isset($_POST['set_default'])) {
    $template_id = (int)$_POST['template_id'];
    
    try {
        // First, unset all defaults
        $stmt = $db->prepare("UPDATE email_templates SET is_default = 0");
        $stmt->execute();
        
        // Then set the new default
        $stmt = $db->prepare("UPDATE email_templates SET is_default = 1 WHERE id = :id");
        $stmt->bindParam(':id', $template_id);
        $stmt->execute();
        
        $success_message = "Default template updated successfully.";
        
        // Redirect to refresh the page
        header("Location: email-templates.php?success=default_set");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error setting default template: " . $e->getMessage();
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $success_message = "Template deleted successfully.";
            break;
        case 'default_set':
            $success_message = "Default template updated successfully.";
            break;
        case 'added':
            $success_message = "Template added successfully.";
            break;
        case 'updated':
            $success_message = "Template updated successfully.";
            break;
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Email Templates</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">Client Inquiries</a></li>
                        <li class="breadcrumb-item active">Email Templates</li>
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
                        <h3 class="card-title">Manage Email Templates</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> Cannot manage email templates until database connection is fixed.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Manage Email Templates</h3>
                            <a href="add-email-template.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Template
                            </a>
                        </div>
                    </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Available Variables</h5>
                        <p>You can use the following variables in your email templates:</p>
                        <ul>
                            <li><code>{client_name}</code> - Client's name</li>
                            <li><code>{client_email}</code> - Client's email address</li>
                            <li><code>{company_name}</code> - Your company name</li>
                            <li><code>{company_phone}</code> - Your company phone</li>
                            <li><code>{company_email}</code> - Your company email</li>
                            <li><code>{company_website}</code> - Your company website</li>
                        </ul>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Subject</th>
                                    <th>Default</th>
                                    <th>Last Updated</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($templates)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No email templates found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($templates as $template): ?>
                                        <tr>
                                            <td><?php echo $template['name']; ?></td>
                                            <td><?php echo $template['subject']; ?></td>
                                            <td>
                                                <?php if ($template['is_default']): ?>
                                                    <span class="badge badge-success">Default</span>
                                                <?php else: ?>
                                                    <form action="" method="post" class="d-inline">
                                                        <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                        <button type="submit" name="set_default" class="btn btn-sm btn-outline-primary">
                                                            Set as Default
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y, g:i a', strtotime($template['updated_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-email-template.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#previewModal<?php echo $template['id']; ?>" title="Preview">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (!$template['is_default']): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $template['id']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Preview Modal -->
                                                <div class="modal fade" id="previewModal<?php echo $template['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel<?php echo $template['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="previewModalLabel<?php echo $template['id']; ?>">
                                                                    Preview: <?php echo $template['name']; ?>
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <strong>Subject:</strong> <?php echo $template['subject']; ?>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <?php echo $template['body']; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <?php if (!$template['is_default']): ?>
                                                    <div class="modal fade" id="deleteModal<?php echo $template['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $template['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $template['id']; ?>">Confirm Deletion</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete the template <strong><?php echo $template['name']; ?></strong>?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <form action="" method="post">
                                                                        <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_template" class="btn btn-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>