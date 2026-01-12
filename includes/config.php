<?php
// Start session once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PRODUCTION CONFIG (no .env)
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flionxga_frontend');

// Site configuration
// Force canonical base URL so assets and links never point to localhost
define('SITE_URL', 'https://flionetech.com');
define('SITE_NAME', 'Flione IT');
define('SITE_EMAIL', 'contact@flioneit.com');

// Path configuration
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDE_PATH', ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'recaptcha-config.php';
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Error reporting: production safe
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Database connection (PDO)
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Log details but show generic message
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Service temporarily unavailable.');
}

// Helper functions
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function get_setting($key) {
    global $db;
    try {
        $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>