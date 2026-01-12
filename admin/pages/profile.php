<?php
require_once '../includes/config.php';

// Get current admin user
$current_admin = get_current_admin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $username = isset($_POST['username']) ? sanitize_admin_input($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitize_admin_input($_POST['email']) : '';
        
        // Validate form data
        if (empty($username)) {
            set_admin_alert('Username is required.', 'danger');
        } elseif (empty($email)) {
            set_admin_alert('Email is required.', 'danger');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_admin_alert('Invalid email format.', 'danger');
        } else {
            try {
                // Check if username or email already exists for other users
                $stmt = $db->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
                $stmt->execute(['username' => $username, 'email' => $email, 'id' => $_SESSION['admin_id']]);
                if ($stmt->rowCount() > 0) {
                    set_admin_alert('Username or email already exists.', 'danger');
                } else {
                    // Update profile
                    $stmt = $db->prepare("
                        UPDATE users
                        SET username = :username, email = :email
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'username' => $username,
                        'email' => $email,
                        'id' => $_SESSION['admin_id']
                    ]);
                    
                    // Update session
                    $_SESSION['admin_username'] = $username;
                    
                    set_admin_alert('Profile updated successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/profile.php');
                    exit;
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Get form data
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate form data
        if (empty($current_password)) {
            set_admin_alert('Current password is required.', 'danger');
        } elseif (empty($new_password)) {
            set_admin_alert('New password is required.', 'danger');
        } elseif (strlen($new_password) < 6) {
            set_admin_alert('New password must be at least 6 characters.', 'danger');
        } elseif ($new_password !== $confirm_password) {
            set_admin_alert('New passwords do not match.', 'danger');
        } else {
            try {
                // Verify current password
                $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->execute(['id' => $_SESSION['admin_id']]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($current_password, $user['password'])) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        UPDATE users
                        SET password = :password
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'password' => $hashed_password,
                        'id' => $_SESSION['admin_id']
                    ]);
                    
                    set_admin_alert('Password changed successfully.', 'success');
                    header('Location: ' . ADMIN_URL . '/pages/profile.php');
                    exit;
                } else {
                    set_admin_alert('Current password is incorrect.', 'danger');
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <!-- Profile Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $current_admin['username']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $current_admin['email']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst($current_admin['role']); ?>" readonly>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Password must be at least 6 characters.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Account Activity -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Account Activity</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Date</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Last Login</td>
                        <td><?php echo date('M d, Y h:i A'); ?></td>
                        <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                    </tr>
                    <tr>
                        <td>Account Created</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>