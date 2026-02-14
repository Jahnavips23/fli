<?php
require_once 'includes/config.php';

try {
    $stmt = $db->query("SELECT id, title, image FROM services ORDER BY id");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Current Services:\n";
    foreach ($services as $service) {
        echo "ID: " . $service['id'] . ", Title: " . $service['title'] . ", Image: [" . $service['image'] . "]\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>