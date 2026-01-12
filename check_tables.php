<?php
require_once 'includes/config.php';

try {
    $stmt = $db->query("SHOW TABLES LIKE 'job_listings'");
    $exists = $stmt->rowCount() > 0;
    echo $exists ? 'job_listings table exists' : 'job_listings table does not exist';
    echo "\n";
    
    if (!$exists) {
        // Create tables from SQL file
        $sql = file_get_contents('database/careers.sql');
        $db->exec($sql);
        echo "Tables created successfully";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>