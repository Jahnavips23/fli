<?php
require_once 'includes/config.php';

// Read the SQL file
$sql = file_get_contents('database/sample_blog_data.sql');

try {
    // Execute the SQL
    $db->exec($sql);
    echo 'Sample blog data imported successfully.';
} catch (PDOException $e) {
    echo 'Error importing sample blog data: ' . $e->getMessage();
}
?>