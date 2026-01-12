<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flioneit');

// Try to connect to the database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to the database.<br>";
    
    // Check if testimonials table exists
    $stmt = $db->query("SHOW TABLES LIKE 'testimonials'");
    if ($stmt->rowCount() > 0) {
        echo "Testimonials table exists.<br>";
        
        // Count testimonials
        $stmt = $db->query("SELECT COUNT(*) FROM testimonials");
        $count = $stmt->fetchColumn();
        echo "Number of testimonials: " . $count . "<br>";
        
        // Get testimonials
        if ($count > 0) {
            $stmt = $db->query("SELECT * FROM testimonials");
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Testimonials:</h3>";
            echo "<pre>";
            print_r($testimonials);
            echo "</pre>";
        }
    } else {
        echo "Testimonials table does not exist.<br>";
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>