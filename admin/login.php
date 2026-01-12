<?php
require_once 'includes/config.php';
require_once '../includes/recaptcha-config.php';

// Redirect if already logged in
if (is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (empty($recaptchaResponse)) {
        $error = 'Please complete the reCAPTCHA verification.';
    } elseif (!verifyRecaptcha($recaptchaResponse)) {
        $error = 'reCAPTCHA verification failed. Please try again.';
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND role = 'admin'");
            $stmt->execute([
                'username' => $username,
                'email' => $username
            ]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (86400 * 30); // 30 days
                    
                    // Store token in database
                    $stmt = $db->prepare("UPDATE users SET remember_token = :token, token_expires = :expires WHERE id = :id");
                    $stmt->execute([
                        'token' => $token,
                        'expires' => date('Y-m-d H:i:s', $expires),
                        'id' => $user['id']
                    ]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                // Redirect to dashboard
                header('Location: ' . ADMIN_URL . '/index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" type="image/png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo ADMIN_ASSETS; ?>/css/admin-style.css">
    
    <style>
        .login-page {
            background: linear-gradient(135deg, #0056b3 0%, #004494 100%);
        }
        .login-card {
            background-color: #fff;
        }
        .login-header {
            background-color: #fff;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        .btn-login {
            background-color: #0056b3;
            border-color: #0056b3;
            color: #fff;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #004494;
            border-color: #004494;
            color: #fff;
        }
        .login-footer {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="login-card">
                        <div class="login-header py-4">
                            <div class="text-center">
                                <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="<?php echo SITE_NAME; ?>" height="60" class="mb-3">
                                <h4>Admin Login</h4>
                                <p class="text-muted mb-0">Sign in to your account to continue</p>
                            </div>
                        </div>
                        <div class="login-body p-4">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username or Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username or email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" toggle="#password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                    <a href="forgot-password.php" class="text-primary">Forgot password?</a>
                                </div>
                                <div class="mb-3">
                                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-login">Sign In <i class="fas fa-sign-in-alt ms-1"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="login-footer py-3">
                            <div class="text-center">
                                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo SITE_URL; ?>" class="text-white"><i class="fas fa-arrow-left me-1"></i> Back to Website</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Toggle password visibility
        $('.toggle-password').on('click', function() {
            var input = $($(this).attr('toggle'));
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).html('<i class="fas fa-eye-slash"></i>');
            } else {
                input.attr('type', 'password');
                $(this).html('<i class="fas fa-eye"></i>');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>