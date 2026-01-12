<?php
require_once 'includes/config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/project_tracking.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "Project tracking tables created successfully!";
} catch (PDOException $e) {
    echo "Error creating project tracking tables: " . $e->getMessage();
}
?>