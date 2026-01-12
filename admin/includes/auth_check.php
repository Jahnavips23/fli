<?php
// Include config file which has session start and database connection
require_once __DIR__ . '/config.php';

// Check if admin is logged in using the function from config.php
if (!is_admin_logged_in()) {
    // Redirect to login page
    header("Location: " . ADMIN_URL . "/login.php");
    exit;
}
?>