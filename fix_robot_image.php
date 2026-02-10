<?php
require_once 'includes/config.php';

try {
    // 1. Find the program
    $stmt = $db->query("SELECT id, title, image FROM kids_programs WHERE title LIKE '%Robot%' OR image LIKE '%robot%'");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($programs) . " potential programs:\n";
    foreach ($programs as $p) {
        echo "ID: " . $p['id'] . " | Title: " . $p['title'] . " | Image: " . $p['image'] . "\n";
        
        // 2. Prepare update
        // If the image is NOT correctly set, update it.
        // We want 'assets/images/kids/1758117593_robot.png'
        $correct_path = 'assets/images/kids/1758117593_robot.png';
        
        if ($p['image'] !== $correct_path) {
            echo "Updating ID " . $p['id'] . "...\n";
            $update = $db->prepare("UPDATE kids_programs SET image = :img WHERE id = :id");
            $update->execute(['img' => $correct_path, 'id' => $p['id']]);
            echo "Updated.\n";
        } else {
            echo "Path is already correct.\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
