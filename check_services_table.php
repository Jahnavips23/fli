<?php
require_once 'includes/config.php';

try {
    $stmt = $db->query("DESCRIBE services");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in 'services' table:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>