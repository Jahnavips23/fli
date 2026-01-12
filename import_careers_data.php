<?php
require_once 'includes/config.php';

// Read the SQL file
$sql = file_get_contents('database/careers.sql');

try {
    // Execute the SQL
    $db->exec($sql);
    echo 'Careers database tables created successfully.';
} catch (PDOException $e) {
    echo 'Error creating careers database tables: ' . $e->getMessage();
}
?>