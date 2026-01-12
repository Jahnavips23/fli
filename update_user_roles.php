<?php
require_once 'includes/config.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/update_user_roles.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "User roles updated successfully!";
} catch (PDOException $e) {
    echo "Error updating user roles: " . $e->getMessage();
}
?>