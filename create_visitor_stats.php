<?php
require_once 'includes/config.php';

try {
    // Check if visitor_stats table exists
    $stmt = $db->query("SHOW TABLES LIKE 'visitor_stats'");
    $exists = $stmt->rowCount() > 0;
    
    if (!$exists) {
        echo "Visitor stats table does not exist. Creating it now...<br>";
        
        // Read SQL file
        $sql = file_get_contents('database/visitor_stats.sql');
        
        // Execute SQL
        $db->exec($sql);
        
        echo "Visitor stats table created successfully with sample data!<br>";
    } else {
        echo "Visitor stats table already exists. Updating with sample data...<br>";
        
        // Clear existing data
        $db->exec("TRUNCATE TABLE visitor_stats");
        
        // Read SQL file (just the INSERT part)
        $sql = file_get_contents('database/visitor_stats.sql');
        $insert_pos = strpos($sql, 'INSERT INTO');
        if ($insert_pos !== false) {
            $insert_sql = substr($sql, $insert_pos);
            $db->exec($insert_sql);
        }
        
        echo "Visitor stats data updated successfully!<br>";
    }
    
    // Show some sample data
    $stmt = $db->query("SELECT * FROM visitor_stats ORDER BY visit_date DESC LIMIT 5");
    $data = $stmt->fetchAll();
    
    echo "<br>Sample data:<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Date</th><th>Page Views</th><th>Unique Visitors</th></tr>";
    
    foreach ($data as $row) {
        echo "<tr>";
        echo "<td>" . $row['visit_date'] . "</td>";
        echo "<td>" . $row['page_views'] . "</td>";
        echo "<td>" . $row['unique_visitors'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<br>All done!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>