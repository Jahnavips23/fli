<?php
require_once 'includes/config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/customer_tickets.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "Customer tickets tables created successfully!";
} catch (PDOException $e) {
    echo "Error creating customer tickets tables: " . $e->getMessage();
}
?>