<?php
// Include the admin config file
require_once __DIR__ . '/../includes/config.php';

// Check if the database connection is working
if (isset($db)) {
    echo "Database connection is working!";
    
    // Try to create a simple test table
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50))");
        echo "\nTest table created successfully!";
        
        // Drop the test table
        $db->exec("DROP TABLE IF EXISTS test_table");
        echo "\nTest table dropped successfully!";
    } catch (PDOException $e) {
        echo "\nError creating test table: " . $e->getMessage();
    }
} else {
    echo "Database connection is not available!";
}
?>