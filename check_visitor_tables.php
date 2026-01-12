<?php
require_once 'includes/config.php';

try {
    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    
    // Check for visitor-related tables
    $visitor_tables = array_filter($tables, function($table) {
        return strpos($table, 'visitor') !== false || 
               strpos($table, 'analytics') !== false || 
               strpos($table, 'stats') !== false;
    });
    
    if (!empty($visitor_tables)) {
        echo "<br>Found visitor-related tables:<br>";
        foreach ($visitor_tables as $table) {
            echo "- $table<br>";
            
            // Show table structure
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "  Columns: " . implode(", ", $columns) . "<br>";
            
            // Show sample data
            $stmt = $db->query("SELECT * FROM $table LIMIT 3");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                echo "  Sample data:<br>";
                foreach ($rows as $row) {
                    echo "  - " . json_encode($row) . "<br>";
                }
            } else {
                echo "  No data in table<br>";
            }
        }
    } else {
        echo "<br>No visitor-related tables found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>