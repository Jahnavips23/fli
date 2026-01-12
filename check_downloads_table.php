<?php
require_once 'includes/config.php';

try {
    // Check if downloads table exists
    $stmt = $db->query("SHOW TABLES LIKE 'downloads'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "Downloads table exists.<br>";
        
        // Get table structure
        $stmt = $db->query("DESCRIBE downloads");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Table columns:<br>";
        foreach ($columns as $column) {
            echo "- $column<br>";
        }
        
        // Get sample data
        $stmt = $db->query("SELECT * FROM downloads LIMIT 3");
        $downloads = $stmt->fetchAll();
        
        echo "<br>Sample data:<br>";
        foreach ($downloads as $download) {
            echo "ID: {$download['id']}, Title: {$download['title']}, Category: {$download['category']}<br>";
        }
    } else {
        echo "Downloads table does not exist.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>