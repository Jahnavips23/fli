<?php
require_once '../includes/config.php';

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user']) || isset($_POST['edit_user'])) {
        // Get form data
        $username = isset($_POST['username']) ? sanitize_admin_input($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitize_admin_input($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $role = isset($_POST['role']) ? sanitize_admin_input($_POST['role']) : 'user';
        
        // Validate form data
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        
        if (isset($_POST['add_user']) || !empty($password)) {
            if (isset($_POST['add_user']) && empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'Passwords do not match.';
            }
        }
        
        if (empty($errors)) {
            try {
                if (isset($_POST['add_user'])) {
                    // Check if username or email already exists
                    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
                    $stmt->execute(['username' => $username, 'email' => $email]);
                    if ($stmt->rowCount() > 0) {
                        set_admin_alert('Username or email already exists.', 'danger');
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Add new user
                        $stmt = $db->prepare("
                            INSERT INTO users (username, email, password, role)
                            VALUES (:username, :email, :password, :role)
                        ");
                        $stmt->execute([
                            'username' => $username,
                            'email' => $email,
                            'password' => $hashed_password,
                            'role' => $role
                        ]);
                        
                        set_admin_alert('User added successfully.', 'success');
                        header('Location: ' . ADMIN_URL . '/pages/users.php');
                        exit;
                    }
                } elseif (isset($_POST['edit_user'])) {
                    // Edit existing user
                    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
                    
                    if ($user_id > 0) {
                        // Check if username or email already exists for other users
                        $stmt = $db->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
                        $stmt->execute(['username' => $username, 'email' => $email, 'id' => $user_id]);
                        if ($stmt->rowCount() > 0) {
                            set_admin_alert('Username or email already exists.', 'danger');
                        } else {
                            // Update user
                            if (!empty($password)) {
                                // Update with new password
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $db->prepare("
                                    UPDATE users
                                    SET username = :username, email = :email, password = :password, role = :role
                                    WHERE id = :id
                                ");
                                $stmt->execute([
                                    'username' => $username,
                                    'email' => $email,
                                    'password' => $hashed_password,
                                    'role' => $role,
                                    'id' => $user_id
                                ]);
                            } else {
                                // Update without changing password
                                $stmt = $db->prepare("
                                    UPDATE users
                                    SET username = :username, email = :email, role = :role
                                    WHERE id = :id
                                ");
                                $stmt->execute([
                                    'username' => $username,
                                    'email' => $email,
                                    'role' => $role,
                                    'id' => $user_id
                                ]);
                            }
                            
                            set_admin_alert('User updated successfully.', 'success');
                            header('Location: ' . ADMIN_URL . '/pages/users.php');
                            exit;
                        }
                    }
                }
            } catch (PDOException $e) {
                set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
            }
        } else {
            // Display errors
            set_admin_alert(implode('<br>', $errors), 'danger');
        }
    } elseif (isset($_POST['delete_user'])) {
        // Delete user
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        
        if ($user_id > 0) {
            // Prevent deleting yourself
            if ($user_id === (int)$_SESSION['admin_id']) {
                set_admin_alert('You cannot delete your own account.', 'danger');
            } else {
                try {
                    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
                    $stmt->execute(['id' => $user_id]);
                    
                    set_admin_alert('User deleted successfully.', 'success');
                } catch (PDOException $e) {
                    set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
                }
            }
        }
        
        header('Location: ' . ADMIN_URL . '/pages/users.php');
        exit;
    }
}

// Get user data for editing
$user = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            set_admin_alert('User not found.', 'danger');
            header('Location: ' . ADMIN_URL . '/pages/users.php');
            exit;
        }
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
        header('Location: ' . ADMIN_URL . '/pages/users.php');
        exit;
    }
}

// Get all users for listing
$users = [];
if ($action === 'list') {
    try {
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        set_admin_alert('An error occurred: ' . $e->getMessage(), 'danger');
    }
}

include '../includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit User Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?></h5>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <?php if ($action === 'edit' && $user): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user ? $user['username'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user ? $user['email'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo $action === 'add' ? 'Password <span class="text-danger">*</span>' : 'Password (leave blank to keep current)'; ?></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" <?php echo $action === 'add' ? 'required' : ''; ?>>
                        <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if ($action === 'edit'): ?>
                        <small class="form-text text-muted">Leave blank to keep current password.</small>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" <?php echo $action === 'add' ? 'required' : ''; ?>>
                        <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="user" <?php echo ($user && $user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_user' : 'edit_user'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add User' : 'Update User'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/pages/users.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Users List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Users</h5>
            <a href="<?php echo ADMIN_URL; ?>/pages/users.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New User
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="alert alert-info">
                    No users found. Click the "Add New User" button to create one.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo format_admin_date($user['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== (int)$_SESSION['admin_id']): ?>
                                            <form action="" method="post" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="tooltip" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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