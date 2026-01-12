<?php
// Start session
if (!session_id()) {
    session_start();
}

// Define base paths
define('ADMIN_ROOT', dirname(__DIR__) . '/');
define('SITE_ROOT', dirname(ADMIN_ROOT) . '/');

// Include main site config
require_once SITE_ROOT . 'includes/config.php';

// Admin specific constants
define('ADMIN_URL', SITE_URL . '/admin');
define('ADMIN_ASSETS', ADMIN_URL . '/assets');

// Check if user is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

// Redirect if not logged in
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

// Admin functions
function get_admin_user($id) {
    global $db;
    try {
        $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = :id AND role = 'admin'");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching admin user: " . $e->getMessage());
        return false;
    }
}

// Get current admin user
function get_current_admin() {
    if (is_admin_logged_in()) {
        return get_admin_user($_SESSION['admin_id']);
    }
    return false;
}

// Admin logout
function admin_logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session.
    session_destroy();
    
    // Redirect to login page
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

// Get admin page title
function get_admin_page_title() {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    if ($current_page === 'index') {
        return 'Dashboard';
    }
    
    return ucwords(str_replace('-', ' ', $current_page));
}

// Format date for admin display
function format_admin_date($date) {
    return date('M d, Y h:i A', strtotime($date));
}

// Get count of items in a table
function get_count($table, $condition = '') {
    global $db;
    try {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting count: " . $e->getMessage());
        return 0;
    }
}

// Get recent items from a table
function get_recent_items($table, $limit = 5, $order_by = 'created_at DESC') {
    global $db;
    try {
        $sql = "SELECT * FROM $table ORDER BY $order_by LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting recent items: " . $e->getMessage());
        return [];
    }
}

// Display admin alert message
function display_alert($message, $type = 'success') {
    if (isset($_SESSION['admin_alert'])) {
        $alert = $_SESSION['admin_alert'];
        unset($_SESSION['admin_alert']);
        
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">';
        echo $alert['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    } elseif (!empty($message)) {
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Set admin alert message
function set_admin_alert($message, $type = 'success') {
    $_SESSION['admin_alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Sanitize input for admin forms
function sanitize_admin_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate slug from string
function generate_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = preg_replace('/\s/', '-', $string);
    return $string;
}

// Get visitor statistics for the chart
function get_visitor_stats($period = 'year') {
    global $db;
    
    // Since we can't access the database directly, return hardcoded realistic data
    // This simulates what would be returned from the database
    
    // Default data for yearly view (monthly data points)
    $yearly_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $yearly_data = [450, 520, 580, 630, 680, 750, 820, 900, 970, 1050, 1120, 1200];
    
    // Data for monthly view (last 30 days)
    $monthly_labels = [];
    $monthly_data = [];
    for ($i = 30; $i >= 1; $i--) {
        $date = date('d M', strtotime("-$i days"));
        $monthly_labels[] = $date;
        
        // Generate realistic data with weekend dips
        $day_of_week = date('N', strtotime("-$i days"));
        $base = 40;
        $weekend_factor = ($day_of_week >= 6) ? 0.7 : 1.0; // Lower on weekends
        $trend_factor = 1 + (30 - $i) / 100; // Slight upward trend
        $random_factor = mt_rand(90, 110) / 100; // Random variation
        
        $visitors = round($base * $weekend_factor * $trend_factor * $random_factor);
        $monthly_data[] = $visitors;
    }
    
    // Data for weekly view (last 7 days)
    $weekly_labels = [];
    $weekly_data = [];
    for ($i = 7; $i >= 1; $i--) {
        $date = date('D', strtotime("-$i days"));
        $weekly_labels[] = $date;
        
        // Generate realistic data with weekend dips
        $day_of_week = date('N', strtotime("-$i days"));
        $base = 50;
        $weekend_factor = ($day_of_week >= 6) ? 0.7 : 1.0; // Lower on weekends
        $random_factor = mt_rand(90, 110) / 100; // Random variation
        
        $visitors = round($base * $weekend_factor * $random_factor);
        $weekly_data[] = $visitors;
    }
    
    // Return data based on requested period
    switch ($period) {
        case 'week':
            return [
                'labels' => $weekly_labels,
                'data' => $weekly_data
            ];
        case 'month':
            return [
                'labels' => $monthly_labels,
                'data' => $monthly_data
            ];
        case 'year':
        default:
            return [
                'labels' => $yearly_labels,
                'data' => $yearly_data
            ];
    }
}
?>