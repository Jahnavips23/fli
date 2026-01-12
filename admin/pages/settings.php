<?php
require_once '../includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle general settings update
    if (isset($_POST['update_settings'])) {
        try {
            // Determine which form was submitted based on available fields
            $settings_to_update = [];
            
            // General settings
            if (isset($_POST['site_title'])) {
                $settings_to_update = [
                    'site_title' => isset($_POST['site_title']) ? sanitize_admin_input($_POST['site_title']) : '',
                    'site_description' => isset($_POST['site_description']) ? sanitize_admin_input($_POST['site_description']) : '',
                    'contact_email' => isset($_POST['contact_email']) ? sanitize_admin_input($_POST['contact_email']) : '',
                    'contact_phone' => isset($_POST['contact_phone']) ? sanitize_admin_input($_POST['contact_phone']) : '',
                    'footer_text' => isset($_POST['footer_text']) ? sanitize_admin_input($_POST['footer_text']) : ''
                ];
            }
            
            // Social media settings
            if (isset($_POST['facebook_url'])) {
                $settings_to_update = [
                    'facebook_url' => isset($_POST['facebook_url']) ? sanitize_admin_input($_POST['facebook_url']) : '',
                    'twitter_url' => isset($_POST['twitter_url']) ? sanitize_admin_input($_POST['twitter_url']) : '',
                    'linkedin_url' => isset($_POST['linkedin_url']) ? sanitize_admin_input($_POST['linkedin_url']) : '',
                    'instagram_url' => isset($_POST['instagram_url']) ? sanitize_admin_input($_POST['instagram_url']) : ''
                ];
            }
            
            // SEO settings
            if (isset($_POST['meta_keywords'])) {
                $settings_to_update = [
                    'meta_keywords' => isset($_POST['meta_keywords']) ? sanitize_admin_input($_POST['meta_keywords']) : '',
                    'google_analytics' => isset($_POST['google_analytics']) ? sanitize_admin_input($_POST['google_analytics']) : ''
                ];
            }
            
            // Update each setting using a different approach
            foreach ($settings_to_update as $key => $value) {
                // First check if the setting exists
                $check_stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $check_stmt->execute([$key]);
                $exists = $check_stmt->fetchColumn() > 0;
                
                if ($exists) {
                    // Update existing setting
                    $update_stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    $update_stmt->execute([$value, $key]);
                } else {
                    // Insert new setting
                    $insert_stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    $insert_stmt->execute([$key, $value]);
                }
            }
            
            set_admin_alert('Settings updated successfully.', 'success');
        } catch (PDOException $e) {
            set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        }
    }
    
    // Handle SMTP settings update
    if (isset($_POST['update_smtp_settings'])) {
        try {
            // Get SMTP settings from the form
            $smtp_settings = [
                'smtp_host' => isset($_POST['smtp_host']) ? sanitize_admin_input($_POST['smtp_host']) : '',
                'smtp_port' => isset($_POST['smtp_port']) ? sanitize_admin_input($_POST['smtp_port']) : '',
                'smtp_username' => isset($_POST['smtp_username']) ? sanitize_admin_input($_POST['smtp_username']) : '',
                'smtp_from_email' => isset($_POST['smtp_from_email']) ? sanitize_admin_input($_POST['smtp_from_email']) : '',
                'smtp_from_name' => isset($_POST['smtp_from_name']) ? sanitize_admin_input($_POST['smtp_from_name']) : ''
            ];
            
            // Only update password if provided
            if (!empty($_POST['smtp_password'])) {
                $smtp_settings['smtp_password'] = sanitize_admin_input($_POST['smtp_password']);
            }
            
            // Update each setting using a different approach
            foreach ($smtp_settings as $key => $value) {
                // First check if the setting exists
                $check_stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $check_stmt->execute([$key]);
                $exists = $check_stmt->fetchColumn() > 0;
                
                if ($exists) {
                    // Update existing setting
                    $update_stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    $update_stmt->execute([$value, $key]);
                } else {
                    // Insert new setting
                    $insert_stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    $insert_stmt->execute([$key, $value]);
                }
            }
            
            set_admin_alert('SMTP settings updated successfully.', 'success');
        } catch (PDOException $e) {
            set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        }
    }
}

// Get current settings
$settings = [];
try {
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings");
    $stmt->execute();
    $result = $stmt->fetchAll();
    
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
}

include '../includes/header.php';
?>

<!-- Site Settings Form -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Site Settings</h5>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="site_title" class="form-label">Site Title</label>
                        <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo isset($settings['site_title']) ? $settings['site_title'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo isset($settings['site_description']) ? $settings['site_description'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo isset($settings['contact_email']) ? $settings['contact_email'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo isset($settings['contact_phone']) ? $settings['contact_phone'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="footer_text" class="form-label">Footer Text</label>
                        <input type="text" class="form-control" id="footer_text" name="footer_text" value="<?php echo isset($settings['footer_text']) ? $settings['footer_text'] : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Social Media Settings -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Social Media Settings</h5>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="facebook_url" class="form-label">Facebook URL</label>
                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?php echo isset($settings['facebook_url']) ? $settings['facebook_url'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="twitter_url" class="form-label">Twitter URL</label>
                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="<?php echo isset($settings['twitter_url']) ? $settings['twitter_url'] : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="<?php echo isset($settings['linkedin_url']) ? $settings['linkedin_url'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="instagram_url" class="form-label">Instagram URL</label>
                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?php echo isset($settings['instagram_url']) ? $settings['instagram_url'] : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Social Media Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SEO Settings -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">SEO Settings</h5>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="<?php echo isset($settings['meta_keywords']) ? $settings['meta_keywords'] : ''; ?>">
                <small class="form-text text-muted">Separate keywords with commas.</small>
            </div>
            
            <div class="mb-3">
                <label for="google_analytics" class="form-label">Google Analytics Tracking ID</label>
                <input type="text" class="form-control" id="google_analytics" name="google_analytics" value="<?php echo isset($settings['google_analytics']) ? $settings['google_analytics'] : ''; ?>">
                <small class="form-text text-muted">Example: UA-XXXXX-Y</small>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save SEO Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SMTP Settings -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Email (SMTP) Settings</h5>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="smtp_host" class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo isset($settings['smtp_host']) ? $settings['smtp_host'] : ''; ?>">
                        <small class="form-text text-muted">e.g., smtp.gmail.com</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_port" class="form-label">SMTP Port</label>
                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo isset($settings['smtp_port']) ? $settings['smtp_port'] : '587'; ?>">
                        <small class="form-text text-muted">Common ports: 25, 465, 587</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_username" class="form-label">SMTP Username</label>
                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo isset($settings['smtp_username']) ? $settings['smtp_username'] : ''; ?>">
                        <small class="form-text text-muted">Usually your email address</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">SMTP Password</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" placeholder="<?php echo isset($settings['smtp_password']) && !empty($settings['smtp_password']) ? '••••••••' : 'Enter password'; ?>">
                        <small class="form-text text-muted">Leave blank to keep existing password</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_from_email" class="form-label">From Email Address</label>
                        <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?php echo isset($settings['smtp_from_email']) ? $settings['smtp_from_email'] : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_from_name" class="form-label">From Name</label>
                        <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?php echo isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="update_smtp_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save SMTP Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>