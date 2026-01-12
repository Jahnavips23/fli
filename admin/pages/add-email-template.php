<?php
require_once __DIR__ . '/../includes/auth_check.php';
$current_page = 'email-templates';
require_once __DIR__ . '/../includes/header.php';

// Check if database connection is available
$db_available = isset($db) && $db instanceof PDO;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $subject = trim($_POST['subject']);
    $body = $_POST['body'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($subject) || empty($body)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            // If setting as default, unset all other defaults first
            if ($is_default) {
                $stmt = $db->prepare("UPDATE email_templates SET is_default = 0");
                $stmt->execute();
            }
            
            // Insert new template
            $stmt = $db->prepare("
                INSERT INTO email_templates 
                (name, subject, body, is_default) 
                VALUES 
                (:name, :subject, :body, :is_default)
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':is_default', $is_default);
            
            $stmt->execute();
            
            $success_message = "Email template added successfully.";
            
            // Redirect to email templates page
            header("Location: email-templates.php?success=added");
            exit;
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Email Template</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">Client Inquiries</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/pages/email-templates.php">Email Templates</a></li>
                        <li class="breadcrumb-item active">Add Template</li>
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
                        <h3 class="card-title">Add Email Template</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i> Cannot add email templates until database connection is fixed.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Template Information</h3>
                    </div>
                    
                    <form action="" method="post">
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
                            
                            <div class="form-group">
                                <label for="name">Template Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Email Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="body">Email Body <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="body" name="body" rows="10" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_default" name="is_default">
                                    <label class="custom-control-label" for="is_default">Set as default template</label>
                                </div>
                                <small class="form-text text-muted">If checked, this template will be used as the default for welcome emails.</small>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Add Template</button>
                            <a href="email-templates.php" class="btn btn-secondary ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Initialize CKEditor for the body field
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('body', {
            height: 300,
            removeButtons: 'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Strike,Subscript,Superscript,CopyFormatting,RemoveFormat,NumberedList,BulletedList,Outdent,Indent,Blockquote,CreateDiv,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,BidiLtr,BidiRtl,Language,Anchor,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,FontSize,TextColor,BGColor,Maximize,ShowBlocks,About'
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>